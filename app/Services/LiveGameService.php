<?php

namespace App\Services;

use App\Models\GamePlay;
use App\Models\LiveAnswer;
use App\Models\LivePlayer;
use App\Models\LiveSession;
use App\Models\QuizClient;
use App\Support\QuestionBank;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LiveGameService
{
    public function __construct(private GeoLookup $geo) {}

    public function createSession(
        string $nivel,
        ?string $categoria = null,
        ?Request $request = null,
        QuizClient|int|null $client = null,
    ): LiveSession {
        $clientModel = $this->resolveClient($client);
        $clientId = $clientModel?->id;

        if ($clientModel) {
            $nivel = QuizClient::CUSTOM_NIVEL;
        } else {
            abort_unless(array_key_exists($nivel, QuestionBank::levels()), 404);
        }

        if ($categoria === null || $categoria === '' || strtolower($categoria) === 'todas') {
            $categoria = null;
        }

        if ($categoria !== null) {
            $available = $clientModel
                ? array_column(QuestionBank::categoriesForClient($clientModel), 'nome')
                : array_column(QuestionBank::categoriesFor($nivel), 'nome');
            if (! in_array($categoria, $available, true)) {
                throw ValidationException::withMessages([
                    'categoria' => 'Categoria inválida para este nível.',
                ]);
            }
        }

        $questions = $clientModel
            ? QuestionBank::drawForClient($clientModel, 10, $categoria)
            : QuestionBank::draw($nivel, 10, $categoria);

        if (count($questions) === 0) {
            throw ValidationException::withMessages([
                'categoria' => $categoria
                    ? 'Não há perguntas suficientes nesta categoria.'
                    : 'Não há perguntas para este nível.',
            ]);
        }

        $ip = $request?->ip();
        $location = $this->geo->lookup($ip);

        return LiveSession::query()->create([
            'client_id' => $clientId,
            'pin' => $this->uniquePin(),
            'nivel' => $nivel,
            'host_token' => Str::random(40),
            'status' => LiveSession::STATUS_LOBBY,
            'current_index' => 0,
            'questions' => $questions,
            'ip_address' => $ip,
            'country' => $location['country'],
            'city' => $location['city'],
        ]);
    }

    public function join(string $pin, string $name, ?int $clientId = null): LivePlayer
    {
        $session = LiveSession::query()->where('pin', $pin)->first();

        if (! $session) {
            throw ValidationException::withMessages([
                'pin' => 'PIN inválido.',
            ]);
        }

        if ($clientId !== null && (int) $session->client_id !== $clientId) {
            throw ValidationException::withMessages([
                'pin' => 'PIN inválido para este quiz.',
            ]);
        }

        if ($clientId === null && $session->client_id !== null) {
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

        $now = now();

        $session->update([
            'status' => LiveSession::STATUS_QUESTION,
            'current_index' => 0,
            'question_started_at' => $now,
            'started_at' => $session->started_at ?? $now,
        ]);

        $session = $session->fresh(['players']);
        $this->recordLivePlay($session);

        return $session;
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

        if ($session->status === LiveSession::STATUS_RANKING) {
            if (! $session->question_started_at) {
                $session->update(['question_started_at' => now()]);

                return $session->fresh();
            }

            $elapsed = (int) $session->question_started_at->diffInMilliseconds(now());
            if ($elapsed < LiveSession::RANKING_SECONDS * 1000) {
                return $session;
            }

            return $this->startNextQuestion($session);
        }

        return $session;
    }

    public function advance(LiveSession $session): LiveSession
    {
        $before = $session->status;
        $session = $this->maybeExpireQuestion($session);

        // Se o timer já avançou o estado, não aplica outra transição no mesmo clique.
        if ($session->status !== $before) {
            return $session;
        }

        if ($session->status === LiveSession::STATUS_QUESTION) {
            return $this->enterReveal($session);
        }

        if ($session->status === LiveSession::STATUS_REVEAL) {
            return $this->goToRankingOrFinished($session);
        }

        if ($session->status === LiveSession::STATUS_RANKING) {
            return $this->startNextQuestion($session);
        }

        return $session;
    }

    private function startNextQuestion(LiveSession $session): LiveSession
    {
        $session->update([
            'status' => LiveSession::STATUS_QUESTION,
            'current_index' => $session->current_index + 1,
            'question_started_at' => now(),
        ]);

        return $session->fresh();
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
                'finished_at' => now(),
            ]);

            return $session->fresh();
        }

        $session->update([
            'status' => LiveSession::STATUS_RANKING,
            'question_started_at' => now(),
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
            'level' => $this->levelMetaForSession($session),
            'status' => $session->status,
            'current_index' => $session->current_index,
            'total' => $session->totalQuestions(),
            'remaining_ms' => $session->remainingMs(),
            'reveal_remaining_ms' => $this->revealRemainingMs($session),
            'ranking_remaining_ms' => $this->rankingRemainingMs($session),
            'question_seconds' => LiveSession::QUESTION_SECONDS,
            'reveal_seconds' => LiveSession::REVEAL_SECONDS,
            'ranking_seconds' => LiveSession::RANKING_SECONDS,
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

    /**
     * @return array{title: string, subtitle: string, description: string, accent: string, age?: string}|null
     */
    private function levelMetaForSession(LiveSession $session): ?array
    {
        if ($session->nivel === QuizClient::CUSTOM_NIVEL) {
            $client = $session->relationLoaded('client')
                ? $session->client
                : $session->client()->first();

            return $client?->levelMeta() ?? [
                'title' => 'Quiz',
                'subtitle' => 'Personalizado',
                'description' => '',
                'accent' => 'ocean',
            ];
        }

        return QuestionBank::levels()[$session->nivel] ?? null;
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

    private function rankingRemainingMs(LiveSession $session): int
    {
        if ($session->status !== LiveSession::STATUS_RANKING || ! $session->question_started_at) {
            return 0;
        }

        $elapsed = (int) $session->question_started_at->diffInMilliseconds(now());
        $limit = LiveSession::RANKING_SECONDS * 1000;

        return max(0, $limit - $elapsed);
    }

    private function recordLivePlay(LiveSession $session): void
    {
        $already = GamePlay::query()
            ->where('live_session_id', $session->id)
            ->where('type', GamePlay::TYPE_LIVE)
            ->exists();

        if ($already) {
            return;
        }

        $names = $session->players
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        GamePlay::query()->create([
            'type' => GamePlay::TYPE_LIVE,
            'nivel' => $session->nivel,
            'categoria' => $session->categoriaLabel() === 'Todas' ? null : $session->categoriaLabel(),
            'live_session_id' => $session->id,
            'player_names' => $names,
            'ip_address' => $session->ip_address,
            'user_agent' => null,
            'country' => $session->country,
            'city' => $session->city,
            'started_at' => $session->started_at ?? now(),
        ]);
    }

    private function uniquePin(): string
    {
        do {
            $pin = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (LiveSession::query()->where('pin', $pin)->exists());

        return $pin;
    }

    private function resolveClient(QuizClient|int|null $client): ?QuizClient
    {
        if ($client instanceof QuizClient) {
            return $client;
        }

        if ($client === null) {
            return null;
        }

        return QuizClient::query()->find($client);
    }
}
