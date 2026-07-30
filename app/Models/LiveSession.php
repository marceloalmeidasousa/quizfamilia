<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveSession extends Model
{
    public const STATUS_LOBBY = 'lobby';

    public const STATUS_QUESTION = 'question';

    public const STATUS_REVEAL = 'reveal';

    public const STATUS_RANKING = 'ranking';

    public const STATUS_FINISHED = 'finished';

    public const QUESTION_SECONDS = 20;

    /** Tempo mostrando a resposta correta antes de ir ao ranking. */
    public const REVEAL_SECONDS = 4;

    /** Tempo do ranking parcial antes da próxima pergunta. */
    public const RANKING_SECONDS = 7;

    protected $fillable = [
        'pin',
        'nivel',
        'host_token',
        'status',
        'current_index',
        'question_started_at',
        'started_at',
        'finished_at',
        'ip_address',
        'country',
        'city',
        'questions',
    ];

    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'question_started_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'current_index' => 'integer',
        ];
    }

    public function players(): HasMany
    {
        return $this->hasMany(LivePlayer::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(LiveAnswer::class);
    }

    public function currentQuestion(): ?array
    {
        $questions = $this->questions ?? [];

        return $questions[$this->current_index] ?? null;
    }

    public function totalQuestions(): int
    {
        return count($this->questions ?? []);
    }

    /**
     * Rótulo da categoria sorteada (ou "Todas" se misto).
     */
    public function categoriaLabel(): string
    {
        $cats = collect($this->questions ?? [])
            ->pluck('categoria')
            ->filter()
            ->unique()
            ->values();

        if ($cats->count() === 1) {
            return (string) $cats->first();
        }

        return 'Todas';
    }

    public function remainingMs(): int
    {
        if ($this->status !== self::STATUS_QUESTION || ! $this->question_started_at) {
            return 0;
        }

        $elapsed = (int) $this->question_started_at->diffInMilliseconds(now());
        $limit = self::QUESTION_SECONDS * 1000;

        return max(0, $limit - $elapsed);
    }
}
