<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents an exam in the application.
 *
 * An exam is a collection of steps (tests) that are assigned to participants
 * and administered by a teacher.
 */
class Exam extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'city_id',
        'teacher_id',
        'status',
        'current_exam_step_id',
        'started_at',
        'completed_at',
        'contract_view_enabled',
    ];

    /**
     * Get the city where the exam is taking place.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the teacher administering the exam.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the participants of the exam.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function participants(): HasMany
    {
        return $this->hasMany(ExamParticipant::class);
    }

    /**
     * Get the steps of the exam, ordered by their sequence.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function steps(): HasMany
    {
        return $this->hasMany(ExamStep::class)->orderBy('step_order');
    }

    /**
     * Get the current step of the exam.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(ExamStep::class, 'current_exam_step_id');
    }
}
