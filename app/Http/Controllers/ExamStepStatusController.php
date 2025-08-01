<?php

namespace App\Http\Controllers;

use App\Models\ExamStepStatus;
use Illuminate\Http\Request;

class ExamStepStatusController extends Controller
{
    public function addTime(Request $request, ExamStepStatus $status)
    {
        $data = $request->validate([
            'minutes' => 'required|integer|min:1',
        ]);

        $status->increment('grace_period_seconds', $data['minutes'] * 60);

        return back(303)->with('success', 'Extra time added.');
    }
}
