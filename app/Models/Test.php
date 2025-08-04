<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Test extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'duration',
    ];

    // Assignments for this test
    public function assignments(): HasMany
    {
        return $this->hasMany(TestAssignment::class);
    }
}