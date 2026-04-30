<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollaborationSuggestion extends Model
{
    protected $fillable = ['title', 'content', 'created_by', 'status', 'is_hidden'];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(CollaborationSuggestionVote::class, 'suggestion_id');
    }
}

