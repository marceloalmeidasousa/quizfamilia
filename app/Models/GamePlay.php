<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamePlay extends Model
{
    public const TYPE_SOLO = 'solo';

    public const TYPE_LIVE = 'live';

    public const TYPE_X1 = 'x1';

    protected $fillable = [
        'type',
        'nivel',
        'categoria',
        'live_session_id',
        'player_names',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'started_at',
    ];

    protected function casts(): array
    {
        return [
            'player_names' => 'array',
            'started_at' => 'datetime',
        ];
    }

    public function liveSession(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class);
    }

    public function locationLabel(): string
    {
        if ($this->city && $this->country) {
            return $this->city.', '.$this->country;
        }

        if ($this->city) {
            return $this->city;
        }

        if ($this->country) {
            return $this->country;
        }

        return $this->ip_address ?: '—';
    }
}
