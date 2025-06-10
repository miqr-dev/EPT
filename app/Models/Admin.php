<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function testBatteries()
    {
        return $this->hasMany(TestBattery::class);
    }

    public function testSessions()
    {
        return $this->hasMany(TestSession::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
