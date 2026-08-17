<?php

namespace App\Services;

use App\Models\GamePlay;
use App\Models\QuizClient;
use App\Models\X1Challenge;
use App\Support\QuestionBank;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class X1ChallengeService
{
    public function __construct(private GeoLookup $geo) {}

    public function create(
        string $name,
        string $nivel,
        ?string $categoria,
        Request $request,
        ?int $clientId = null,
    ): X1Challenge {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 40) {
            throw ValidationException::withMessages([
                'name' => 'Informe um nome válido (até 40 caracteres).',
            ]);
        }

        if ($clientId) {
            $nivel = QuizClient::CUSTOM_NIVEL;
        } elseif (! in_array($nivel, X1Challenge::LEVELS, true)) {
            throw ValidationException::withMessages([
                'nivel' => 'Nível inválido para o X1.',
            ]);
        }

        if ($categoria === null || $categoria === '' || strtolower($categoria) === 'todas') {
            $categoria = null;
        }

        if ($categoria !== null) {
            $available = array_column(QuestionBank::categoriesFor($nivel, $clientId), 'nome');
            if (! in_array($categoria, $available, true)) {
                throw ValidationException::withMessages([
                    'categoria' => 'Categoria inválida para este nível.',
                ]);
            }
        }

        $questions = QuestionBank::draw($nivel, X1Challenge::RODADAS, $categoria, $clientId);

        if (count($questions) < 1) {
            throw ValidationException::withMessages([
                'categoria' => 'Não há perguntas suficientes para este desafio.',
            ]);
        }

        $ip = $request->ip();
        $geo = $this->geo->lookup($ip);

        $challenge = X1Challenge::query()->create([
            'client_id' => $clientId,
            'token' => (string) Str::uuid(),
            'nivel' => $nivel,
            'categoria' => $categoria,
            'questions' => $questions,
            'status' => X1Challenge::STATUS_PLAYING_CREATOR,
            'creator_name' => $name,
            'ip_address' => $ip,
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'country' => $geo['country'],
            'city' => $geo['city'],
            'expires_at' => now()->addDays(X1Challenge::EXPIRES_DAYS),
        ]);

        GamePlay::query()->create([
            'type' => GamePlay::TYPE_X1,
            'nivel' => $nivel,
            'categoria' => $categoria,
            'player_names' => [$name],
            'ip_address' => $ip,
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'country' => $geo['country'],
            'city' => $geo['city'],
            'started_at' => now(),
        ]);

        return $challenge;
    }

    public function findByToken(string $token): X1Challenge
    {
        $challenge = X1Challenge::query()->where('token', $token)->firstOrFail();

        if ($challenge->isExpired() && $challenge->status !== X1Challenge::STATUS_FINISHED) {
            if ($challenge->status !== X1Challenge::STATUS_EXPIRED) {
                $challenge->update(['status' => X1Challenge::STATUS_EXPIRED]);
                $challenge->refresh();
            }
        }

        return $challenge;
    }

    public function joinOpponent(X1Challenge $challenge, string $name): X1Challenge
    {
        $this->assertPlayable($challenge);

        if ($challenge->status !== X1Challenge::STATUS_AWAITING_OPPONENT) {
            throw ValidationException::withMessages([
                'name' => 'Este desafio não está mais aguardando adversário.',
            ]);
        }

        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 40) {
            throw ValidationException::withMessages([
                'name' => 'Informe um nome válido (até 40 caracteres).',
            ]);
        }

        $challenge->update([
            'opponent_name' => $name,
            'status' => X1Challenge::STATUS_PLAYING_OPPONENT,
        ]);

        return $challenge->fresh();
    }

    public function finishCreator(X1Challenge $challenge, int $score): X1Challenge
    {
        $this->assertPlayable($challenge);

        if ($challenge->status !== X1Challenge::STATUS_PLAYING_CREATOR) {
            throw ValidationException::withMessages([
                'score' => 'Pontuação do desafiante já registrada.',
            ]);
        }

        $max = $challenge->totalQuestions();
        $score = max(0, min($score, $max));

        $challenge->update([
            'creator_score' => $score,
            'creator_finished_at' => now(),
            'status' => X1Challenge::STATUS_AWAITING_OPPONENT,
        ]);

        return $challenge->fresh();
    }

    public function finishOpponent(X1Challenge $challenge, int $score): X1Challenge
    {
        $this->assertPlayable($challenge);

        if ($challenge->status !== X1Challenge::STATUS_PLAYING_OPPONENT) {
            throw ValidationException::withMessages([
                'score' => 'Este desafio já foi finalizado ou ainda não começou para o adversário.',
            ]);
        }

        $max = $challenge->totalQuestions();
        $score = max(0, min($score, $max));

        $challenge->update([
            'opponent_score' => $score,
            'opponent_finished_at' => now(),
            'status' => X1Challenge::STATUS_FINISHED,
        ]);

        return $challenge->fresh();
    }

    public function whatsappUrl(X1Challenge $challenge, string $brandName, ?string $url = null): string
    {
        $tema = $challenge->categoriaLabel();
        $url ??= route('x1.show', $challenge->token);
        $text = "Vamos no X1? Eu te desafio a jogar comigo no tema *{$tema}* no {$brandName}! {$url}";

        return 'https://wa.me/?text='.rawurlencode($text);
    }

    private function assertPlayable(X1Challenge $challenge): void
    {
        if ($challenge->isExpired() && $challenge->status !== X1Challenge::STATUS_FINISHED) {
            throw ValidationException::withMessages([
                'token' => 'Este desafio expirou.',
            ]);
        }
    }
}
