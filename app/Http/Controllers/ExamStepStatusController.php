<?php

namespace App\Http\Controllers;

use App\Models\ExamStepStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles actions related to the status of a specific exam step for a participant.
 */
class ExamStepStatusController extends Controller
{
    /**
     * Add extra time to a participant's exam step.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ExamStepStatus  $status
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addTime(Request $request, ExamStepStatus $status): RedirectResponse
    {
        $data = $request->validate([
            'minutes' => 'required|integer|min:1|max:30',
        ], [
            'minutes.max' => 'Es dürfen maximal 30 Minuten hinzugefügt werden.',
        ]);

        if (in_array($status->status, ['completed', 'paused'], true)) {
            return back(303)->with('error', 'Für pausierte oder beendete Tests kann keine zusätzliche Zeit vergeben werden.');
        }

        $additionalMinutes = (int)$data['minutes'];
        $additionalSeconds = $additionalMinutes * 60;

        $status->increment('extra_time', $additionalMinutes);

        if (!is_null($status->time_remaining_seconds)) {
            $status->increment('time_remaining_seconds', $additionalSeconds);
        }

        return back(303)->with('success', 'Zusätzliche Zeit wurde vergeben.');
    }
}
