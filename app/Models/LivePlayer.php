<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LivePlayer extends Model
{
    protected $fillable = [
        'live_session_id',
        'name',
        'token',
        'score',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'joined_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(LiveAnswer::class);
    }
}
