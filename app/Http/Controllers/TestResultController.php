<?php

namespace App\Http\Controllers;

use App\Models\TestResult;
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

    public function view(TestResult $testResult)
    {
        $testResult->load(['assignment.participant', 'assignment.test']);

        return view('pdfs.result', [
            'testResult' => $testResult,
        ]);
    }
}
