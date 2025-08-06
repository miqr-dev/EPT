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
        'component_name',
    ];

    protected $appends = ['component_name'];

    public function getComponentNameAttribute(): string
    {
        return explode('-', $this->code)[0];
    }

    // Assignments for this test
    public function assignments(): HasMany
    {
        return $this->hasMany(TestAssignment::class);
    }
}