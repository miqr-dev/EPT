<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'klasse',
        'age_year',
        'age_month',
        'german_level',
        'english_level',
        'math_level',
        'math_note',
        'schulzweig',
        'schule',
        'schulart',
    ];

    public function testSessions()
    {
        return $this->hasMany(TestSession::class);
    }
}
