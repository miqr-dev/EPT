<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'test_battery_id',
        'admin_id',
        'start_time',
        'end_time',
        'status',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function testBattery()
    {
        return $this->belongsTo(TestBattery::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function testSessionTests()
    {
        return $this->hasMany(TestSessionTest::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
