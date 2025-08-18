<?php

namespace App\Services;

use App\Models\TestResult;
use Illuminate\Support\Facades\Storage;

class BrtAPdfService
{
    public static function generate(TestResult $result): ?string
    {
        // Only attempt generation if DomPDF is installed
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return null;
        }

        $data = [
            'test_name' => $result->assignment->test->name ?? 'BRT-A',
            'date' => now()->format('d.m.Y'),
            'rohwert' => $result->result_json['rohwert'] ?? null,
            'prozentrang' => $result->result_json['prozentrang'] ?? null,
            'twert' => $result->result_json['twert'] ?? null,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.brt-a-result', $data)->setPaper('a4');
        $path = 'test_results/brt-a-' . $result->id . '.pdf';
        Storage::put($path, $pdf->output());
        return $path;
    }
}
