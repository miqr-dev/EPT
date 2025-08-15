<?php

namespace App\Http\Controllers;

use App\Models\TestAssignment;
use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestResultController extends Controller
{
  public function store(Request $request)
  {
    $data = $request->validate([
      'assignment_id' => 'required|exists:test_assignments,id',
      'result_json' => 'required|json',
    ]);

    $assignment = TestAssignment::findOrFail($data['assignment_id']);

    if (Auth::id() !== $assignment->participant_id) {
      abort(403);
    }

    TestResult::create([
      'assignment_id' => $assignment->id,
      'result_json' => $data['result_json'],
    ]);

    $assignment->update([
      'status' => 'completed',
      'started_at' => $assignment->started_at ?? now(),
      'completed_at' => now(),
    ]);

    return response()->json(['message' => 'Result stored']);
  }
}

