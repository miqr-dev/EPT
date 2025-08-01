<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
  protected $fillable = ['name', 'city_id', 'teacher_id', 'status', 'current_exam_step_id', 'started_at', 'completed_at'];

  public function city()
  {
    return $this->belongsTo(City::class);
  }
  public function teacher()
  {
    return $this->belongsTo(User::class, 'teacher_id');
  }
  public function participants()
  {
    return $this->hasMany(ExamParticipant::class);
  }
  public function steps()
  {
    return $this->hasMany(ExamStep::class)->orderBy('step_order');
  }
  public function currentStep()
  {
    return $this->belongsTo(ExamStep::class, 'current_exam_step_id');
  }
}
