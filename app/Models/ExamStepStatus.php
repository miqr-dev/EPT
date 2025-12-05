<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents the status of a participant for a specific exam step.
 *
 * This model tracks the progress of a participant on a single test within an exam,
 * including their status, timing information, and completion status.
 */
class ExamStepStatus extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'exam_id',
        'participant_id',
        'exam_step_id',
        'status',
        'paused_from_status',
        'force_finish_requested_at',
        'force_finish_deadline',
        'duration',
        'extra_time',
        'time_remaining_seconds',
        'started_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'force_finish_requested_at' => 'datetime',
        'force_finish_deadline' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the exam that this status belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the participant associated with this status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_id');
    }

    /**
     * Get the exam step that this status refers to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(ExamStep::class, 'exam_step_id');
    }
}
