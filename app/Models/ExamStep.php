<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamStep extends Model
{
  protected $fillable = ['exam_id', 'test_id', 'step_order', 'duration'];

  public function exam()
  {
    return $this->belongsTo(Exam::class);
  }
  public function test()
  {
    return $this->belongsTo(Test::class);
  }
  public function statuses()
  {
    return $this->hasMany(ExamStepStatus::class);
  }
}
