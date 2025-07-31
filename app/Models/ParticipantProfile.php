<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantProfile extends Model
{
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

  // Belongs to user
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function city()
  {
    return $this->belongsTo(City::class);
  }
  // Belongs to profession group
  public function professionGroup(): BelongsTo
  {
    return $this->belongsTo(ProfessionGroup::class);
  }

  // Belongs to employed status
  public function employed(): BelongsTo
  {
    return $this->belongsTo(Employed::class);
  }
}
