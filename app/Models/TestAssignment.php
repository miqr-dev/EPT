<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TestAssignment extends Model
{
  protected $fillable = [
    'participant_id',
    'test_id',
    'status',
    'started_at',
    'completed_at',
  ];

  // The assigned participant (User)
  public function participant(): BelongsTo
  {
    return $this->belongsTo(User::class, 'participant_user_id');
  }

  // The test
  public function test(): BelongsTo
  {
    return $this->belongsTo(Test::class);
  }

  // The result (if completed)
  public function result(): HasOne
  {
    return $this->hasOne(TestResult::class, 'assignment_id');
  }
}
