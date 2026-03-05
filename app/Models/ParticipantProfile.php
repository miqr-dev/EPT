<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantProfile extends Model
{

  protected $casts = [
    'birthday' => 'date',
    'measure_start' => 'date',
    'measure_end' => 'date',
    'contract_imported_at' => 'datetime',
  ];
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
    'course',
    'location',
    'measure_start',
    'measure_end',
    'measure_time',
    'street',
    'zip',
    'city_name',
    'telephone',
    'cost_bearer',
    'contract_imported_at',
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
