<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a profession group in the application.
 *
 * This model is used to categorize participants into different professional groups.
 */
class ProfessionGroup extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the participant profiles associated with this profession group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function participantProfiles(): HasMany
    {
        return $this->hasMany(ParticipantProfile::class);
    }
}
