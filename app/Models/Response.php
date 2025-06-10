<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_session_test_id',
        'question_id',
        'answer',
        'is_correct',
        'points_awarded',
    ];

    public function testSessionTest()
    {
        return $this->belongsTo(TestSessionTest::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
