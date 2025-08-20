<?php

namespace App\Services;

use App\Models\TestResult;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;

class MrtAPdfService
{
    /**
     * Generate a PDF for the given MRT-A test result.
     */
    public static function generate(TestResult $result): ?string
    {
        if (!class_exists(Browsershot::class)) {
            return null;
        }

        $participantName = $result->assignment->participant->name ?? 'participant';
        $testName = $result->assignment->test->name ?? 'test';
        $durationSeconds = $result->result_json['total_time_seconds'] ?? 0;
        $durationFormatted = gmdate('i:s', $durationSeconds);

        $data = [
            'test_name' => $testName,
            'date' => now()->format('d.m.Y'),
            'participant_name' => $participantName,
            'duration' => $durationFormatted,
            'results' => $result->result_json,
        ];

        $html = view('pdfs.mrt-a-result', $data)->render();

        $pdf = Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->waitUntilNetworkIdle()
            ->setDelay(1000)
            ->showBackground()
            ->pdf();

        $fileName = Str::slug($participantName, '_') . '_' . Str::slug($testName, '_') . '.pdf';
        $path = 'test_results/' . $fileName;
        Storage::put($path, $pdf);

        return $path;
    }
}

