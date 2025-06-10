<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'active',
        'scoring_table_id',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function normtable()
    {
        return $this->belongsTo(Normtable::class, 'scoring_table_id');
    }

    public function testBatteries()
    {
        return $this->belongsToMany(TestBattery::class, 'test_battery_tests')->withPivot('order');
    }
}
