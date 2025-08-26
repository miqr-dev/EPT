<?php

namespace App\Http\Controllers;

use App\Models\TestResult;
use App\Services\BrtPdfService;
use App\Services\FpiRPdfService;
use App\Services\LmtPdfService;
use Illuminate\Http\Request;
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
             $newAnswersWithTimes = array_map(function($newAnswer, $oldAnswer) {
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

    public function download(TestResult $testResult)
    {
        $path = $testResult->pdf_file_path;

        if (!$path || !Storage::exists($path)) {
            $testName = $testResult->assignment->test->name;
            $service = null;

            if ($testName === 'BRT-A' || $testName === 'BRT-B') {
                $service = BrtPdfService::class;
            } elseif ($testName === 'LMT') {
                $service = LmtPdfService::class;
            } elseif ($testName === 'FPI-R') {
                $service = FpiRPdfService::class;
            }

            if ($service) {
                $path = $service::generate($testResult);
                $testResult->update(['pdf_file_path' => $path]);
            }
        }

        if (!$path || !Storage::exists($path)) {
            abort(404, 'PDF could not be generated or found.');
        }

        return Storage::download($path);
    }
}
