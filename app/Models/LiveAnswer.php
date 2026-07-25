<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveAnswer extends Model
{
    protected $fillable = [
        'live_session_id',
        'live_player_id',
        'question_index',
        'choice',
        'correct',
        'points',
        'answered_at_ms',
    ];

    protected function casts(): array
    {
        return [
            'question_index' => 'integer',
            'choice' => 'integer',
            'correct' => 'boolean',
            'points' => 'integer',
            'answered_at_ms' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(LivePlayer::class, 'live_player_id');
    }
}
