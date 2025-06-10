<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestBattery extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'title',
        'description',
        'pause_time_between_tests',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function tests()
    {
        return $this->belongsToMany(Test::class, 'test_battery_tests')->withPivot('order');
    }

    public function testSessions()
    {
        return $this->hasMany(TestSession::class);
    }
}
