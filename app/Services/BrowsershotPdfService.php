<?php

namespace App\Services;

use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\TestResult;

class BrowsershotPdfService
{
    public static function generate(TestResult $result): string
    {
        $participant = $result->assignment->participant->name ?? 'participant';
        $test = $result->assignment->test->name ?? 'test';
        $fileName = Str::slug($participant, '_') . '_' . Str::slug($test, '_') . '.pdf';
        $path = "test_results/{$fileName}";

        Storage::makeDirectory('test_results');

        $url = route('participants.result', [$result->id]);
        Browsershot::url($url)->format('A4')->save(storage_path("app/{$path}"));

        return $path;
    }
}
