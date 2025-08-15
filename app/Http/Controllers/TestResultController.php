<?php

namespace App\Http\Controllers;

use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class TestResultController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'assignment_id' => ['required', 'exists:test_assignments,id'],
            'result_json' => ['required', 'array'],
        ]);

        $result = TestResult::create([
            'assignment_id' => $data['assignment_id'],
            'result_json' => $data['result_json'],
        ]);

        $pdf = Pdf::loadView('pdf.test-result', ['result' => $data['result_json']]);
        $path = 'results/' . $result->id . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());
        $result->update(['pdf_file_path' => $path]);

        return response()->json($result, 201);
    }

    public function download(TestResult $testResult)
    {
        if (!$testResult->pdf_file_path || !Storage::disk('public')->exists($testResult->pdf_file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($testResult->pdf_file_path);
    }
}
