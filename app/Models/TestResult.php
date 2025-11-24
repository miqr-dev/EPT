<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestResult extends Model
{
  protected $fillable = [
    'assignment_id',
    'result_json',
    'pdf_file_path',
    'teacher_id',
  ];

  protected $casts = [
    'result_json' => 'array',
  ];

  // Assignment for which this result belongs
  public function assignment(): BelongsTo
  {
    return $this->belongsTo(TestAssignment::class);
  }

  // Teacher who conducted the exam for this result
  public function teacher(): BelongsTo
  {
    return $this->belongsTo(User::class, 'teacher_id');
  }
}
