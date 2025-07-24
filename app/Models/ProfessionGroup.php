<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfessionGroup extends Model
{
    protected $fillable = [
        'name',
    ];

    // Participants in this profession group
    public function participantProfiles(): HasMany
    {
        return $this->hasMany(ParticipantProfile::class);
    }
}
