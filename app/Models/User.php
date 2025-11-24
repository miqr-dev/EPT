<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements LdapAuthenticatable
{
  use HasFactory, Notifiable, AuthenticatesWithLdap;

  protected $fillable = [
    'name',
    'email',
    'password',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected function casts(): array
  {
    return [
      'password' => 'hashed',
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

  public function isEnrolledInExam($examId)
  {
      return $this->participantProfile && $this->participantProfile->exam_id === (int) $examId;
  }
}
