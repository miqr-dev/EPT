<?php

namespace App\Http\Controllers;

use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
        Log::info('savePdf endpoint hit.');

        $validator = Validator::make($request->all(), [
            'pdf' => 'required|file|mimes:pdf',
            'filename' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::error('PDF upload validation failed.', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $file = $request->file('pdf');
            $filename = $request->input('filename');

            Log::info('Validation passed. Attempting to save PDF.', [
                'original_filename' => $file->getClientOriginalName(),
                'target_filename' => $filename,
                'size' => $file->getSize(),
            ]);

            $path = $file->storeAs('pdf', $filename, 'public');

            Log::info('PDF stored successfully.', ['path' => $path]);

            // Also, let's log the absolute path to be super clear
            $absolutePath = Storage::disk('public')->path($path);
            Log::info('PDF absolute path.', ['absolute_path' => $absolutePath]);


            return response()->json([
                'message' => 'PDF saved successfully',
                'path' => $path,
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving PDF.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Server error while saving PDF.'], 500);
        }
    }
}
