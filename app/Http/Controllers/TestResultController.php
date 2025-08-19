<?php

namespace App\Http\Controllers;

use App\Models\TestResult;
use App\Services\MrtAChartService;
use Illuminate\Http\Request;

class TestResultController extends Controller
{
    public function update(Request $request, TestResult $testResult)
    {
        $validatedData = $request->validate([
            'result_json' => 'required|json',
        ]);

        $testResult->update([
            'result_json' => json_decode($validatedData['result_json'], true),
        ]);

        return redirect()->back()->with('success', 'Test result updated successfully.');
    }

    public function mrtAChart(TestResult $testResult)
    {
        $answers = collect($testResult->result_json['answers'] ?? [])
            ->map(fn ($a) => $a['user_answer'] ?? null)
            ->toArray();

        $age = optional($testResult->assignment->participant->participant_profile)->age;

        $data = MrtAChartService::generate($answers, is_numeric($age) ? (int) $age : null);

        return response()->json($data);
    }
}
