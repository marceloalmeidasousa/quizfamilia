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

    protected $fillable = [
        'pin',
        'nivel',
        'host_token',
        'status',
        'current_index',
        'question_started_at',
        'questions',
    ];

    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'question_started_at' => 'datetime',
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
