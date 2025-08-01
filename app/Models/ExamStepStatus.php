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
    'duration',
    'extra_time',
    'started_at',
    'completed_at'
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
