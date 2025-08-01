<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamParticipant extends Model
{
  protected $fillable = ['exam_id', 'participant_id', 'status'];

  public function exam()
  {
    return $this->belongsTo(Exam::class);
  }
  public function user()
  {
    return $this->belongsTo(User::class, 'participant_id');
  }
  public function stepStatuses()
  {
    return $this->hasMany(ExamStepStatus::class, 'participant_id', 'participant_id');
  }
}
