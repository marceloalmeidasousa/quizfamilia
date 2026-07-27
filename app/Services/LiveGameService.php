<?php

namespace App\Services;

use App\Models\LiveAnswer;
use App\Models\LivePlayer;
use App\Models\LiveSession;
use App\Support\QuestionBank;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LiveGameService
{
    public function createSession(string $nivel, ?string $categoria = null): LiveSession
    {
        abort_unless(array_key_exists($nivel, QuestionBank::levels()), 404);

        if ($categoria === null || $categoria === '' || strtolower($categoria) === 'todas') {
            $categoria = null;
        }

        if ($categoria !== null) {
            $available = array_column(QuestionBank::categoriesFor($nivel), 'nome');
            if (! in_array($categoria, $available, true)) {
                throw ValidationException::withMessages([
                    'categoria' => 'Categoria inválida para este nível.',
                ]);
            }
        }

        $questions = QuestionBank::draw($nivel, 10, $categoria);

        if (count($questions) === 0) {
            throw ValidationException::withMessages([
                'categoria' => $categoria
                    ? 'Não há perguntas suficientes nesta categoria.'
                    : 'Não há perguntas para este nível.',
            ]);
        }

        return LiveSession::query()->create([
            'pin' => $this->uniquePin(),
            'nivel' => $nivel,
            'host_token' => Str::random(40),
            'status' => LiveSession::STATUS_LOBBY,
            'current_index' => 0,
            'questions' => $questions,
        ]);
    }

    public function join(string $pin, string $name): LivePlayer
    {
        $session = LiveSession::query()->where('pin', $pin)->first();

        if (! $session) {
            throw ValidationException::withMessages([
                'pin' => 'PIN inválido.',
            ]);
        }

        if ($session->status !== LiveSession::STATUS_LOBBY) {
            throw ValidationException::withMessages([
                'pin' => 'Esta partida já começou.',
            ]);
        }

        $name = trim($name);

        if ($name === '' || mb_strlen($name) > 40) {
            throw ValidationException::withMessages([
                'name' => 'Informe um nome válido (até 40 caracteres).',
            ]);
        }

        return $session->players()->create([
            'name' => $name,
            'token' => (string) Str::uuid(),
            'score' => 0,
            'joined_at' => now(),
        ]);
    }

    public function assertHost(LiveSession $session, ?string $hostToken): void
    {
        abort_unless($hostToken && hash_equals($session->host_token, $hostToken), 403);
    }

    public function start(LiveSession $session): LiveSession
    {
        if ($session->status !== LiveSession::STATUS_LOBBY) {
            return $session;
        }

        if ($session->players()->count() === 0) {
            throw ValidationException::withMessages([
                'players' => 'Espere pelo menos 1 jogador entrar.',
            ]);
        }

        $session->update([
            'status' => LiveSession::STATUS_QUESTION,
            'current_index' => 0,
            'question_started_at' => now(),
        ]);

        return $session->fresh();
    }

    public function maybeExpireQuestion(LiveSession $session): LiveSession
    {
        if ($session->status === LiveSession::STATUS_QUESTION) {
            $playersCount = $session->players()->count();
            $answersCount = $session->answers()
                ->where('question_index', $session->current_index)
                ->count();
            $everyoneAnswered = $playersCount > 0 && $answersCount >= $playersCount;

            if (! $everyoneAnswered && $session->remainingMs() > 0) {
                return $session;
            }

            return $this->enterReveal($session);
        }

        if ($session->status === LiveSession::STATUS_REVEAL) {
            if (! $session->question_started_at) {
                $session->update(['question_started_at' => now()]);

                return $session->fresh();
            }

            $elapsed = (int) $session->question_started_at->diffInMilliseconds(now());
            if ($elapsed < LiveSession::REVEAL_SECONDS * 1000) {
                return $session;
            }

            return $this->goToRankingOrFinished($session);
        }

        return $session;
    }

    public function advance(LiveSession $session): LiveSession
    {
        $session = $this->maybeExpireQuestion($session);

        if ($session->status === LiveSession::STATUS_QUESTION) {
            return $this->enterReveal($session);
        }

        if ($session->status === LiveSession::STATUS_REVEAL) {
            return $this->goToRankingOrFinished($session);
        }

        if ($session->status === LiveSession::STATUS_RANKING) {
            $session->update([
                'status' => LiveSession::STATUS_QUESTION,
                'current_index' => $session->current_index + 1,
                'question_started_at' => now(),
            ]);

            return $session->fresh();
        }

        return $session;
    }

    private function enterReveal(LiveSession $session): LiveSession
    {
        $session->update([
            'status' => LiveSession::STATUS_REVEAL,
            'question_started_at' => now(),
        ]);

        return $session->fresh();
    }

    private function goToRankingOrFinished(LiveSession $session): LiveSession
    {
        if ($session->current_index + 1 >= $session->totalQuestions()) {
            $session->update([
                'status' => LiveSession::STATUS_FINISHED,
                'question_started_at' => null,
            ]);

            return $session->fresh();
        }

        $session->update([
            'status' => LiveSession::STATUS_RANKING,
            'question_started_at' => null,
        ]);

        return $session->fresh();
    }

    public function answer(LivePlayer $player, int $choice): LiveAnswer
    {
        $session = $this->maybeExpireQuestion($player->session()->first());

        if ($session->status !== LiveSession::STATUS_QUESTION) {
            throw ValidationException::withMessages([
                'choice' => 'Não é possível responder agora.',
            ]);
        }

        $index = $session->current_index;
        $question = $session->currentQuestion();

        if (! $question) {
            throw ValidationException::withMessages([
                'choice' => 'Pergunta inválida.',
            ]);
        }

        $options = $question['opcoes'] ?? [];

        if ($choice < 0 || $choice >= count($options)) {
            throw ValidationException::withMessages([
                'choice' => 'Opção inválida.',
            ]);
        }

        $existing = LiveAnswer::query()
            ->where('live_player_id', $player->id)
            ->where('question_index', $index)
            ->first();

        if ($existing) {
            return $existing;
        }

        $elapsed = $session->question_started_at
            ? (int) $session->question_started_at->diffInMilliseconds(now())
            : LiveSession::QUESTION_SECONDS * 1000;

        $elapsed = max(0, min($elapsed, LiveSession::QUESTION_SECONDS * 1000));
        $correct = (int) ($question['correta'] ?? -1) === $choice;
        $points = $correct ? $this->pointsFor($elapsed) : 0;

        $answer = LiveAnswer::query()->create([
            'live_session_id' => $session->id,
            'live_player_id' => $player->id,
            'question_index' => $index,
            'choice' => $choice,
            'correct' => $correct,
            'points' => $points,
            'answered_at_ms' => $elapsed,
        ]);

        if ($points > 0) {
            $player->increment('score', $points);
        }

        return $answer;
    }

    public function pointsFor(int $elapsedMs): int
    {
        $limit = LiveSession::QUESTION_SECONDS * 1000;
        $ratio = 1 - ($elapsedMs / $limit);

        return 500 + (int) floor(500 * max(0, min(1, $ratio)));
    }

    /**
     * @return array<int, array{name: string, score: int, rank: int}>
     */
    public function ranking(LiveSession $session): array
    {
        $players = $session->players()->with('answers')->orderBy('joined_at')->get();

        $ranked = $players->map(function (LivePlayer $player) {
            return [
                'name' => $player->name,
                'score' => (int) $player->score,
                'total_time' => (int) $player->answers->sum('answered_at_ms'),
                'joined_at' => $player->joined_at?->timestamp ?? 0,
            ];
        })->sort(function (array $a, array $b) {
            if ($a['score'] !== $b['score']) {
                return $b['score'] <=> $a['score'];
            }
            if ($a['total_time'] !== $b['total_time']) {
                return $a['total_time'] <=> $b['total_time'];
            }

            return $a['joined_at'] <=> $b['joined_at'];
        })->values();

        return $ranked->map(function (array $row, int $i) {
            return [
                'name' => $row['name'],
                'score' => $row['score'],
                'rank' => $i + 1,
            ];
        })->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function hostState(LiveSession $session): array
    {
        $session = $this->maybeExpireQuestion($session->fresh(['players']));

        return $this->buildState($session, revealCorrect: true, forHost: true);
    }

    /**
     * @return array<string, mixed>
     */
    public function playerState(LivePlayer $player): array
    {
        $session = $this->maybeExpireQuestion($player->session()->first());
        $answered = LiveAnswer::query()
            ->where('live_player_id', $player->id)
            ->where('question_index', $session->current_index)
            ->first();

        $state = $this->buildState(
            $session,
            revealCorrect: $session->status !== LiveSession::STATUS_QUESTION,
            forHost: false,
        );

        $state['me'] = [
            'name' => $player->name,
            'score' => (int) $player->score,
            'answered' => (bool) $answered,
            'my_choice' => $answered?->choice,
            'my_correct' => $answered?->correct,
            'my_points' => $answered?->points,
        ];

        return $state;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildState(LiveSession $session, bool $revealCorrect, bool $forHost): array
    {
        $question = $session->currentQuestion();
        $payload = null;

        if ($question && in_array($session->status, [LiveSession::STATUS_QUESTION, LiveSession::STATUS_REVEAL], true)) {
            $payload = [
                'index' => $session->current_index,
                'total' => $session->totalQuestions(),
                'categoria' => $question['categoria'] ?? null,
                'emoji' => $question['emoji'] ?? null,
                'pergunta' => $question['pergunta'] ?? '',
                'opcoes' => $question['opcoes'] ?? [],
                'opcoesEmoji' => $question['opcoesEmoji'] ?? null,
            ];

            if ($revealCorrect) {
                $payload['correta'] = $question['correta'] ?? null;
            }
        }

        return [
            'pin' => $session->pin,
            'nivel' => $session->nivel,
            'level' => QuestionBank::levels()[$session->nivel] ?? null,
            'status' => $session->status,
            'current_index' => $session->current_index,
            'total' => $session->totalQuestions(),
            'remaining_ms' => $session->remainingMs(),
            'reveal_remaining_ms' => $this->revealRemainingMs($session),
            'question_seconds' => LiveSession::QUESTION_SECONDS,
            'reveal_seconds' => LiveSession::REVEAL_SECONDS,
            'players' => $session->players()->orderBy('joined_at')->get(['name', 'score'])->map(fn ($p) => [
                'name' => $p->name,
                'score' => (int) $p->score,
            ])->all(),
            'players_count' => $session->players()->count(),
            'answers_count' => $session->answers()->where('question_index', $session->current_index)->count(),
            'question' => $payload,
            'ranking' => in_array($session->status, [LiveSession::STATUS_REVEAL, LiveSession::STATUS_RANKING, LiveSession::STATUS_FINISHED], true)
                ? $this->ranking($session)
                : [],
            'for_host' => $forHost,
        ];
    }

    private function revealRemainingMs(LiveSession $session): int
    {
        if ($session->status !== LiveSession::STATUS_REVEAL || ! $session->question_started_at) {
            return 0;
        }

        $elapsed = (int) $session->question_started_at->diffInMilliseconds(now());
        $limit = LiveSession::REVEAL_SECONDS * 1000;

        return max(0, $limit - $elapsed);
    }

    private function uniquePin(): string
    {
        do {
            $pin = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (LiveSession::query()->where('pin', $pin)->exists());

        return $pin;
    }
}
