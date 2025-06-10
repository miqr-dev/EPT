<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_session_id',
        'admin_id',
        'note',
    ];

    public function testSession()
    {
        return $this->belongsTo(TestSession::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
