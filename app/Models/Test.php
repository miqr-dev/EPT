<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a test in the application.
 *
 * This model stores information about the available tests that can be assigned to participants.
 */
class Test extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'duration',
        'component_name',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['component_name'];

    /**
     * Get the component name for the test.
     *
     * This accessor dynamically determines the Vue component name based on the test code.
     *
     * @return string
     */
    public function getComponentNameAttribute(): string
    {
        return explode('-', $this->code)[0];
    }

    /**
     * Get the assignments for this test.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TestAssignment::class);
    }
}