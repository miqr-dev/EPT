<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PausedTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_step_status_id',
        'state_json',
    ];

    protected $casts = [
        'state_json' => 'array',
    ];

    public function examStepStatus(): BelongsTo
    {
        return $this->belongsTo(ExamStepStatus::class);
    }
}
