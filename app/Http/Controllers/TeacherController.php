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

    // New: Fetch users created in the last 6 hours in the same city
    $recentUsers = User::where('role', 'participant')
      ->where('city_id', $cityId)
      ->where('created_at', '>=', Carbon::now()->subHours(6))
      ->orderBy('created_at', 'desc')
      ->get();

    // New: Fetch all exams
    $exams = Exam::with(['city', 'teacher', 'participants.user', 'steps.test'])->get();

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
