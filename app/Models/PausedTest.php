<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PausedTest extends Model
{
  protected $fillable = [
    'exam_step_status_id',
    'assignment_id',
    'progress_json',
  ];

  protected $casts = [
    'progress_json' => 'array',
  ];

  public function stepStatus(): BelongsTo
  {
    return $this->belongsTo(ExamStepStatus::class, 'exam_step_status_id');
  }

  public function assignment(): BelongsTo
  {
    return $this->belongsTo(TestAssignment::class, 'assignment_id');
  }
}
