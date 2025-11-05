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
    'completed_at',
    'progress_json',
  ];

  protected $casts = [
    'force_finish_requested_at' => 'datetime',
    'force_finish_deadline' => 'datetime',
    'started_at' => 'datetime',
    'completed_at' => 'datetime',
    'progress_json' => 'array',
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
}
