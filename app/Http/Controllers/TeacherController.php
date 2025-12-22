<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Test;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\TestAssignment;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
  public function dashboard()
  {
    $teacher = Auth::user();
    $cityId = $teacher->city_id;

    // Existing participants query
    $participants = User::with(['city', 'testAssignments.test'])
      ->where('role', 'participant')
      ->where('city_id', $cityId)
      ->orderBy('created_at', 'desc')
      ->get();

    // New: Fetch users active in the last 6 hours in the same city
    $recentCutoff = Carbon::now()->subHours(6);

    $recentUsers = User::where('role', 'participant')
      ->where('city_id', $cityId)
      ->where(function ($query) use ($recentCutoff) {
        $query->where('created_at', '>=', $recentCutoff)
          ->orWhereHas('examParticipants', function ($subQuery) use ($recentCutoff) {
            $subQuery->where('created_at', '>=', $recentCutoff)
              ->orWhere('updated_at', '>=', $recentCutoff);
          });
      })
      ->orderBy('updated_at', 'desc')
      ->get();

    // New: Fetch exams from the teacher's city created within the last 30 days
    $exams = Exam::with(['city', 'teacher', 'participants.user', 'steps.test', 'participants.stepStatuses', 'currentStep.test'])
      ->where('created_at', '>=', Carbon::now()->subDays(30))
      ->where('city_id', $cityId)
      ->get();

    foreach ($exams as $exam) {
      if (!in_array($exam->status, ['in_progress', 'paused'])) {
        continue;
      }

      if ($exam->currentStep) {
        $duration = $exam->currentStep->duration; // duration in minutes
        foreach ($exam->participants as $participant) {
          $status = $participant->stepStatuses->where('exam_step_id', $exam->current_exam_step_id)->first();

          if (!$status) {
            continue;
          }

          $totalDurationSeconds = max(0, ($duration ?? 0) * 60
            + (int) $status->extra_time * 60
            + (int) $status->grace_period_seconds);

          if ($status->status === 'paused') {
            $status->time_remaining = $status->time_remaining_seconds ?? $totalDurationSeconds;
          } elseif ($status->status === 'not_started') {
            $status->time_remaining = $totalDurationSeconds;
          } elseif ($status->started_at) {
            $startTime = Carbon::parse($status->started_at);
            $endTime = $startTime->copy()->addSeconds($totalDurationSeconds);
            $status->time_remaining = now()->diffInSeconds($endTime, false);
          }

          if (isset($status->time_remaining)) {
            $status->time_remaining = max(0, (int) $status->time_remaining);
          }
        }
      }
    }

    $tests = Test::all();

    return inertia('Dashboard', [
      'participants' => $participants,
      'recentUsers' => $recentUsers,
      'exams' => $exams,
      'tests' => $tests,
    ]);
  }

  // 2. Assign Tests
  public function assignTests(Request $request)
  {
    $data = $request->validate([
      'participant_ids' => 'required|array',
      'test_ids' => 'required|array',
    ]);

    foreach ($data['participant_ids'] as $participantId) {
      foreach ($data['test_ids'] as $testId) {
        TestAssignment::firstOrCreate([
          'participant_id' => $participantId,
          'test_id' => $testId,
        ], [
          'assigned_by' => Auth::id(),
          'assigned_at' => now(),
          'status' => 'assigned',
        ]);
      }
    }

    return back()->with('success', 'Tests assigned successfully!');
  }

  // 3. Remove Tests
  public function removeTests(Request $request)
  {
    $data = $request->validate([
      'participant_ids' => 'required|array',
      'test_ids' => 'required|array',
    ]);

    TestAssignment::whereIn('participant_id', $data['participant_ids'])
      ->whereIn('test_id', $data['test_ids'])
      ->delete();

    return back()->with('success', 'Tests removed successfully!');
  }

}
