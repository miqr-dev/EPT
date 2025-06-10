<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TestBatteryTest extends Pivot
{
    use HasFactory;

    public $timestamps = false; // Disable timestamps for this pivot model
    protected $table = 'test_battery_tests';

    protected $fillable = [
        'test_battery_id',
        'test_id',
        'order',
    ];

    public function testBattery()
    {
        return $this->belongsTo(TestBattery::class);
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
