<?php

namespace App\Services;

use App\Models\TestResult;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class GenericPdfService
{
    public static function generate(TestResult $result): ?string
    {
        $participantName = $result->assignment->participant->name ?? 'participant';
        $testName = $result->assignment->test->name ?? 'test';

        $url = URL::temporarySignedRoute(
            'test-results.pdf-view',
            now()->addMinutes(10),
            ['testResult' => $result->id]
        );

        $fileName = Str::slug($participantName, '_') . '_' . Str::slug($testName, '_') . '.pdf';
        $path = 'public/test_results/' . $fileName;

        // Ensure the directory exists
        Storage::makeDirectory('public/test_results');

        try {
            Browsershot::url($url)
                ->save(Storage::path($path));
        } catch (\Exception $e) {
            // Log the error
            return null;
        }

        return $path;
    }
}
