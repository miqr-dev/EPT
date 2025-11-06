<?php

namespace App\Http\Controllers;

use App\Models\TestResult;
use Illuminate\Http\Request;

class TestResultController extends Controller
{
    public function remove(Request $request)
    {
        $testResult = TestResult::findOrFail($request->input('test_result_id'));
        $testResult->delete();

        return back(303);
    }
}
