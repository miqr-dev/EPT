<?php

namespace App\Http\Controllers;

use App\Models\TestResult;
use App\Services\MrtAScorer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles actions related to test results, such as updating them.
 */
class TestResultController extends Controller
{
    /**
     * Update the specified test result in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TestResult  $testResult
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, TestResult $testResult): RedirectResponse
    {
        // Eager load relationships for efficiency
        $testResult->load('assignment.test', 'assignment.participant.participantProfile');
        $testName = $testResult->assignment->test->name;

        $resultData = [];

        if ($testName === 'MRT-A') {
            $validatedData = $request->validate([
                'answers' => 'required|array',
                'answers.*.user_answer' => 'nullable|string|max:1',
            ]);

            $userAnswers = array_column($validatedData['answers'], 'user_answer');
            $originalAnswers = $testResult->result_json['answers'] ?? [];
            $questionTimes = array_column($originalAnswers, 'time_seconds');

            $participant = $testResult->assignment->participant;
            $age = $participant->participantProfile->age ?? null;

            $resultData = MrtAScorer::score($userAnswers, $age, $questionTimes);

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

}
