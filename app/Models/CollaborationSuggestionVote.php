<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollaborationSuggestionVote extends Model
{
    protected $fillable = ['suggestion_id', 'user_id', 'vote', 'comment'];

    public function suggestion(): BelongsTo
    {
        return $this->belongsTo(CollaborationSuggestion::class, 'suggestion_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

