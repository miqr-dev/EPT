<?php

namespace App\Http\Controllers;

use App\Models\ExamStepStatus;
use Illuminate\Http\Request;

class ExamStepStatusController extends Controller
{
    public function addTime(Request $request, ExamStepStatus $status)
    {
        $data = $request->validate([
            'minutes' => 'required|integer|min:1|max:30',
        ], [
            'minutes.max' => 'Es dürfen maximal 30 Minuten hinzugefügt werden.',
        ]);

        if (in_array($status->status, ['completed', 'paused'], true)) {
            return back(303)->with('error', 'Für pausierte oder beendete Tests kann keine zusätzliche Zeit vergeben werden.');
        }

        $additionalMinutes = (int) $data['minutes'];
        $additionalSeconds = $additionalMinutes * 60;

        $status->increment('extra_time', $additionalMinutes);

        if (!is_null($status->time_remaining_seconds)) {
            $status->increment('time_remaining_seconds', $additionalSeconds);
        }

        return back(303)->with('success', 'Zusätzliche Zeit wurde vergeben.');
    }
}
