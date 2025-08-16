<?php

namespace App\Http\Controllers;

use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TestResultController extends Controller
{
    public function storeBrtA(Request $request)
    {
        Log::info('BRT-A result submission', $request->all());

        $validator = Validator::make($request->all(), [
            'assignment_id' => 'required|integer',
            'answers' => 'required|array',
            'timings' => 'required|array',
            'raw_score' => 'required|integer',
            'pr' => 'required|numeric',
            't_score' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Log::warning('BRT-A validation failed', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $validator->validated();

            $result = [
                'answers' => $data['answers'],
                'timings' => $data['timings'],
                'raw_score' => $data['raw_score'],
                'pr' => $data['pr'],
                't_score' => $data['t_score'],
            ];

            $testResult = TestResult::create([
                'assignment_id' => $data['assignment_id'],
                'result_json' => json_encode($result),
            ]);

            Log::info('BRT-A result stored', ['id' => $testResult->id]);
            return response()->noContent();
        } catch (\Throwable $e) {
            Log::error('BRT-A result store exception: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Failed to store results'], 500);
        }
    }
}
