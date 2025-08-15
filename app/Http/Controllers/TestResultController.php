<?php

namespace App\Http\Controllers;

use App\Models\TestResult;
use Illuminate\Http\Request;

class TestResultController extends Controller
{
    public function storeBrtA(Request $request)
    {
        $data = $request->validate([
            'assignment_id' => 'required|exists:test_assignments,id',
            'answers' => 'required|array',
            'timings' => 'required|array',
            'raw_score' => 'required|integer',
            'pr' => 'required|numeric',
            't_score' => 'required|numeric',
        ]);

        $result = [
            'answers' => $data['answers'],
            'timings' => $data['timings'],
            'raw_score' => $data['raw_score'],
            'pr' => $data['pr'],
            't_score' => $data['t_score'],
        ];

        TestResult::create([
            'assignment_id' => $data['assignment_id'],
            'result_json' => $result,
        ]);

        return response()->json(['status' => 'ok']);
    }
}
