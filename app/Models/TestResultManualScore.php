<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestResultManualScore extends Model
{
  protected $fillable = [
    'test_result_id',
    'key',
    'value',
  ];

  protected $casts = [
    'value' => 'float',
  ];

  public function testResult(): BelongsTo
  {
    return $this->belongsTo(TestResult::class);
  }
}
