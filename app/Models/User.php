<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use Illuminate\Notifications\Notifiable;
use App\Models\ExamParticipant;

class User extends Authenticatable implements LdapAuthenticatable
{
  use HasFactory, Notifiable, AuthenticatesWithLdap;

  protected $fillable = [
    'name',
    'firstname',
    'username',
    'email',
    'password',
    'can_login',
    'contract_view_enabled',
    'can_change',
    'role',
    'city_id',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
      'can_login' => 'boolean',
      'contract_view_enabled' => 'boolean',
      'can_change' => 'boolean',
    ];
  }
  public function participantProfile()
  {
    return $this->hasOne(ParticipantProfile::class);
  }
  public function city()
  {
    return $this->belongsTo(City::class);
  }
  public function testAssignments()
  {
    return $this->hasMany(\App\Models\TestAssignment::class, 'participant_id');
  }

  public function tests()
  {
    return $this->belongsToMany(
      \App\Models\Test::class,
      'test_assignments',
      'participant_id',
      'test_id'
    );
  }

  public function examParticipants()
  {
    return $this->hasMany(ExamParticipant::class, 'participant_id');
  }
}
