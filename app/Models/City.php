<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'name',
    ];

    // Users in this city (teachers & participants)
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
