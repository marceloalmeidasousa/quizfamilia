<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class X1Challenge extends Model
{
    public const STATUS_PLAYING_CREATOR = 'playing_creator';

    public const STATUS_AWAITING_OPPONENT = 'awaiting_opponent';

    public const STATUS_PLAYING_OPPONENT = 'playing_opponent';

    public const STATUS_FINISHED = 'finished';

    public const STATUS_EXPIRED = 'expired';

    public const LEVELS = ['adolescente', 'adulto'];

    public const RODADAS = 10;

    public const EXPIRES_DAYS = 7;

    protected $table = 'x1_challenges';

    protected $fillable = [
        'token',
        'nivel',
        'categoria',
        'questions',
        'status',
        'creator_name',
        'creator_score',
        'creator_finished_at',
        'opponent_name',
        'opponent_score',
        'opponent_finished_at',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'creator_score' => 'integer',
            'opponent_score' => 'integer',
            'creator_finished_at' => 'datetime',
            'opponent_finished_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }

        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function categoriaLabel(): string
    {
        return $this->categoria ?: 'Todas';
    }

    public function totalQuestions(): int
    {
        return count($this->questions ?? []);
    }

    public function winnerLabel(): ?string
    {
        if ($this->status !== self::STATUS_FINISHED) {
            return null;
        }

        if ($this->creator_score === $this->opponent_score) {
            return 'empate';
        }

        return $this->creator_score > $this->opponent_score ? 'creator' : 'opponent';
    }
}
