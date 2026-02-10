<?php

namespace App\Http\Controllers;

use App\Models\TestResult;
use App\Models\TestResultManualScore;
use Illuminate\Http\Request;
use App\Services\BrtAScorer;
use App\Services\BrtBScorer;
use App\Services\MrtAScorer;
use Illuminate\Support\Facades\Storage;

class TestResultController extends Controller
{
    public function update(Request $request, TestResult $testResult)
    {
        // Eager load relationships for efficiency
        $testResult->load('assignment.test', 'assignment.participant.participantProfile');
        $testName = $testResult->assignment->test->name;

        $resultData = [];

        if (in_array($testName, ['MRT-A', 'BRT-A', 'BRT-B'], true)) {
            $validatedData = $request->validate([
                'answers' => 'required|array',
                'answers.*.user_answer' => 'nullable|string',
            ]);

            $userAnswers = array_column($validatedData['answers'], 'user_answer');
            $originalAnswers = $testResult->result_json['answers'] ?? [];
            $questionTimes = array_column($originalAnswers, 'time_seconds');

            if ($testName === 'MRT-A') {
                $participant = $testResult->assignment->participant;
                $age = $participant->participantProfile->age ?? null;
                $resultData = MrtAScorer::score($userAnswers, $age, $questionTimes);
            } elseif ($testName === 'BRT-A') {
                $resultData = BrtAScorer::score($userAnswers, $questionTimes);
            } else {
                $resultData = BrtBScorer::score($userAnswers, $questionTimes);
            }

            // The scorer returns a result object that includes an 'answers' key.
            // We need to merge the existing time_seconds back into this new structure.
            $newAnswersWithTimes = array_map(function ($newAnswer, $oldAnswer) {
                $newAnswer['time_seconds'] = $oldAnswer['time_seconds'] ?? 0;
                return $newAnswer;
            }, $resultData['answers'], $originalAnswers);

            $resultData['answers'] = $newAnswersWithTimes;
            $resultData['total_time_seconds'] = $testResult->result_json['total_time_seconds'] ?? array_sum($questionTimes);
        } else {
            // For other tests, keep the old behavior for now.
            $validatedData = $request->validate([
                'result_json' => 'required|json',
            ]);
            $resultData = json_decode($validatedData['result_json'], true);
        }

        $testResult->update([
            'result_json' => $resultData,
        ]);

        return redirect()->back()->with('success', 'Test result updated successfully.');
    }

    public function updateManualScore(Request $request, TestResult $testResult)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:64',
            'value' => 'nullable|numeric',
        ]);

        TestResultManualScore::updateOrCreate(
            [
                'test_result_id' => $testResult->id,
                'key' => $validated['key'],
            ],
            [
                'value' => $validated['value'],
            ]
        );

        return response()->json(['status' => 'ok']);
    }

}
