<?php

namespace App\Http\Controllers;

use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TestResultController extends Controller
{
    public function storeBrtA(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'assignment_id' => 'required|integer',
            'answers' => 'required|array',
            'timings' => 'required|array',
            'raw_score' => 'required|integer',
            'pr' => 'required|numeric',
            't_score' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

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
