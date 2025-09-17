<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamPauseResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'exam_step_id',
        'participant_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
