<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamStepStatus extends Model
{
  protected $fillable = [
    'exam_id',
    'participant_id',
    'exam_step_id',
    'status',
    'paused_from_status',
    'force_finish_requested_at',
    'force_finish_deadline',
    'duration',
    'extra_time',
    'time_remaining_seconds',
    'started_at',
    'completed_at'
  ];

  protected $casts = [
    'force_finish_requested_at' => 'datetime',
    'force_finish_deadline' => 'datetime',
    'started_at' => 'datetime',
    'completed_at' => 'datetime',
  ];

  public function exam()
  {
    return $this->belongsTo(Exam::class);
  }
  public function participant()
  {
    return $this->belongsTo(User::class, 'participant_id');
  }
  public function step()
  {
    return $this->belongsTo(ExamStep::class, 'exam_step_id');
  }

  public function getTimeRemainingAttribute()
  {
    $durationMinutes = $this->step->duration ?? $this->step->test->duration_minutes ?? 0;
    $totalDurationSeconds = max(
      0,
      (int) $durationMinutes * 60 + (int) $this->extra_time * 60 + (int) $this->grace_period_seconds,
    );

    if ($this->status === 'paused') {
      return max(0, (int) ($this->time_remaining_seconds ?? $totalDurationSeconds));
    }

    if ($this->status === 'not_started') {
      return $totalDurationSeconds;
    }

    if ($this->started_at) {
      $endTime = $this->started_at->copy()->addSeconds($totalDurationSeconds);

      return max(0, now()->diffInSeconds($endTime, false));
    }

    return 0;
  }
}
