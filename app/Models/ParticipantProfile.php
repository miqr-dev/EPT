<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents the profile of a participant user.
 *
 * This model stores detailed demographic and professional information about a participant.
 */
class ParticipantProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'firstname',
        'name',
        'birthday',
        'age',
        'sex',
        'education',
        'profession',
        'marital_status',
        'household',
        'employed_id',
        'profession_group_id',
    ];

    /**
     * Get the user that this profile belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the city of the participant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the profession group of the participant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function professionGroup(): BelongsTo
    {
        return $this->belongsTo(ProfessionGroup::class);
    }

    /**
     * Get the employment status of the participant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employed(): BelongsTo
    {
        return $this->belongsTo(Employed::class);
    }
}
