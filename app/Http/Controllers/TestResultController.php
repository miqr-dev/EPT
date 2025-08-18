<?php

namespace App\Http\Controllers;

use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function savePdf(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf',
            'filename' => 'required|string',
        ]);

        $path = $request->file('pdf')->storeAs(
            'pdf',
            $request->input('filename'),
            'public'
        );

        return response()->json([
            'message' => 'PDF saved successfully',
            'path' => $path,
        ]);
    }
}
