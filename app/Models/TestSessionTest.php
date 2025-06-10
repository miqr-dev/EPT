<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestSessionTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_session_id',
        'test_id',
        'start_time',
        'end_time',
        'timer_override',
        'status',
        'score',
    ];

    public function testSession()
    {
        return $this->belongsTo(TestSession::class);
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}
