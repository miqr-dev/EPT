<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Normtable extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'table_data',
    ];

    protected $casts = [
        'table_data' => 'array',
    ];
}
