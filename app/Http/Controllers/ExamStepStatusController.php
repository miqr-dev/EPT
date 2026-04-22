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

        $status->loadMissing('step');

        $additionalMinutes = (int) $data['minutes'];
        $additionalSeconds = $additionalMinutes * 60;

        $stepDurationMinutes = $status->step->duration ?? 0;

        $baseDurationSeconds = max(0, (int) $stepDurationMinutes * 60
            + (int) $status->extra_time * 60
            + (int) $status->grace_period_seconds);

        $currentRemaining = $baseDurationSeconds;

        if ($status->status === 'in_progress' && $status->started_at) {
            $startTime = $status->started_at;
            $endTime = $startTime->copy()->addSeconds($baseDurationSeconds);
            $currentRemaining = now()->diffInSeconds($endTime, false);
        }

        $currentRemaining = max(0, (int) $currentRemaining);

        $status->increment('extra_time', $additionalMinutes);

        $status->update([
            'time_remaining_seconds' => $currentRemaining + $additionalSeconds,
        ]);

        return back(303)->with('success', 'Zusätzliche Zeit wurde vergeben.');
    }
}
