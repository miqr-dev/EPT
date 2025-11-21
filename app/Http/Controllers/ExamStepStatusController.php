<?php

namespace App\Http\Controllers;

use App\Models\ExamStepStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ExamStepStatusController extends Controller
{
  public function addTime(Request $request, ExamStepStatus $status)
  {
    $data = $request->validate([
      'minutes' => 'required|integer|min:1',
    ]);

    if (in_array($status->status, ['completed', 'broken'], true)) {
      return back(303)->with('error', 'Für abgeschlossene Tests kann keine Zeit hinzugefügt werden.');
    }

    $addedSeconds = $data['minutes'] * 60;

    $status->increment('extra_time', $data['minutes']);

    if ($status->status === 'paused') {
      $status->update([
        'time_remaining_seconds' => ($status->time_remaining_seconds ?? 0) + $addedSeconds,
      ]);
    }

    $status->loadMissing('step');
    $stepDurationMinutes = $status->step?->duration ?? 0;
    $totalDurationSeconds = max(0, (int) $stepDurationMinutes * 60
      + (int) $status->extra_time * 60
      + (int) $status->grace_period_seconds);

    if ($status->status === 'paused') {
      $timeRemaining = $status->time_remaining_seconds ?? $totalDurationSeconds;
    } elseif ($status->status === 'not_started') {
      $timeRemaining = $totalDurationSeconds;
    } elseif ($status->started_at) {
      $startTime = Carbon::parse($status->started_at);
      $endTime = $startTime->copy()->addSeconds($totalDurationSeconds);
      $timeRemaining = now()->diffInSeconds($endTime, false);
    } else {
      $timeRemaining = $totalDurationSeconds;
    }

    $timeRemaining = max(0, (int) $timeRemaining);

    if ($request->wantsJson()) {
      return response()->json([
        'message' => 'Extra time added.',
        'time_remaining' => $timeRemaining,
        'extra_time' => $status->extra_time,
      ]);
    }

    return back(303)->with('success', 'Extra time added.');
  }
}
