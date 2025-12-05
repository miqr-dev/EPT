<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents the participation of a user in an exam.
 *
 * This pivot model links a user (participant) to an exam and stores their
 * status within that exam.
 */
class ExamParticipant extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['exam_id', 'participant_id', 'status'];

    /**
     * Get the exam that the participant is taking.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the user who is the participant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_id');
    }

    /**
     * Get the statuses of the exam steps for this participant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stepStatuses(): HasMany
    {
        return $this->hasMany(ExamStepStatus::class, 'participant_id', 'participant_id');
    }
}
