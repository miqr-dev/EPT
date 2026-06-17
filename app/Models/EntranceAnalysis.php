<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntranceAnalysis extends Model
{
    protected $fillable = [
        'participant_id',
        'teacher_id',
        'instruction_understanding',
        'work_method',
        'work_speed',
        'group_behavior',
        'remarks',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
