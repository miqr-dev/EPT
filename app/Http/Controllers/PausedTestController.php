<?php

namespace App\Http\Controllers;

use App\Models\PausedTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PausedTestController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'exam_step_status_id' => 'required|exists:exam_step_statuses,id',
            'state_json' => 'required|json',
        ]);

        $examStepStatus = \App\Models\ExamStepStatus::find($data['exam_step_status_id']);

        // Authorization check
        if ($examStepStatus->participant_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        PausedTest::updateOrCreate(
            ['exam_step_status_id' => $data['exam_step_status_id']],
            ['state_json' => json_decode($data['state_json'], true)]
        );

        return response()->json(['message' => 'Test state saved successfully.']);
    }
}
