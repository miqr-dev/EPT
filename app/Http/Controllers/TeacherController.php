<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Test;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\TestAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

    // New: Fetch participants from the same city who logged in within the last 6 hours
    $recentCutoffTimestamp = Carbon::now()->subHours(6)->getTimestamp();

    $recentUserIds = DB::table('sessions')
      ->whereNotNull('user_id')
      ->where('last_activity', '>=', $recentCutoffTimestamp)
      ->pluck('user_id')
      ->unique();

    $recentUsers = User::where('role', 'participant')
      ->where('city_id', $cityId)
      ->whereIn('id', $recentUserIds)
      ->addSelect(['last_seen_at' => DB::table('sessions')
        ->selectRaw('MAX(last_activity)')
        ->whereColumn('user_id', 'users.id')])
      ->orderByDesc('last_seen_at')
      ->get();

    $teacherRecentCutoffTimestamp = Carbon::now()->subHours(2)->getTimestamp();

    $recentTeacherUserIds = DB::table('sessions')
      ->whereNotNull('user_id')
      ->where('last_activity', '>=', $teacherRecentCutoffTimestamp)
      ->pluck('user_id')
      ->unique();

    $changeableTeachers = collect();
    $canManageRoles = $teacher->role === 'admin' || ($teacher->role === 'teacher' && $teacher->can_change);
    if ($canManageRoles) {
      $changeableTeachers = User::where('city_id', $cityId)
        ->where('role', 'teacher')
        ->where('can_change', false)
        ->whereIn('id', $recentTeacherUserIds)
        ->addSelect(['last_seen_at' => DB::table('sessions')
          ->selectRaw('MAX(last_activity)')
          ->whereColumn('user_id', 'users.id')])
        ->orderByDesc('last_seen_at')
        ->orderBy('name')
        ->orderBy('firstname')
        ->get();
    }

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
      'changeableTeachers' => $changeableTeachers,
      'exams' => $exams,
      'tests' => $tests,
    ]);
  }


  public function downgradeTeacherToParticipant(Request $request, User $teacher)
  {
    $user = Auth::user();

    $canManageRoles = $user->role === 'admin' || ($user->role === 'teacher' && $user->can_change);

    if (!$canManageRoles) {
      abort(403, 'Keine Berechtigung für diese Aktion.');
    }

    if ($user->city_id !== $teacher->city_id) {
      abort(403, 'Nur Lehrkräfte aus derselben Stadt können geändert werden.');
    }

    if ($teacher->can_change) {
      return back()->withErrors([
        'teacher' => 'Diese Lehrkraft besitzt die can_change-Berechtigung und kann nicht geändert werden.',
      ]);
    }

    $validated = $request->validate([
      'role' => ['required', Rule::in(['participant'])],
    ]);

    if ($teacher->role !== 'teacher') {
      return back()->withErrors([
        'teacher' => 'Nur Lehrkräfte mit der Rolle teacher können zu Teilnehmern geändert werden.',
      ]);
    }

    $teacher->role = $validated['role'];
    $teacher->save();

    return back()->with('success', 'Die Rolle wurde auf participant gesetzt.');
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
