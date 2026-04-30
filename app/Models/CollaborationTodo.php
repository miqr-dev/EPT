<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollaborationTodo extends Model
{
    protected $fillable = ['task', 'is_completed', 'created_by', 'suggestion_id'];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function suggestion(): BelongsTo
    {
        return $this->belongsTo(CollaborationSuggestion::class, 'suggestion_id');
    }
}

