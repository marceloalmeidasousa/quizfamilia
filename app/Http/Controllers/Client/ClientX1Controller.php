<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\QuizClient;
use App\Models\X1Challenge;
use App\Services\X1ChallengeService;
use App\Support\QuestionBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientX1Controller extends Controller
{
    public function __construct(private X1ChallengeService $x1) {}

    public function hub(QuizClient $client): View
    {
        $this->assertActive($client);

        return view('client.x1-hub', [
            'client' => $client,
            'categorias' => QuestionBank::categoriesFor(QuizClient::CUSTOM_NIVEL, $client->id),
            'level' => $client->levelMeta(),
        ]);
    }

    public function store(Request $request, QuizClient $client): RedirectResponse
    {
        $this->assertActive($client);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:40'],
            'categoria' => ['nullable', 'string', 'max:80'],
        ]);

        $challenge = $this->x1->create(
            $data['name'],
            QuizClient::CUSTOM_NIVEL,
            $data['categoria'] ?? null,
            $request,
            $client->id,
        );

        return redirect()
            ->route('client.x1.show', [$client, $challenge->token])
            ->cookie($this->roleCookieName($challenge->token), 'creator', 60 * 24 * 8);
    }

    public function show(Request $request, QuizClient $client, string $token): View|RedirectResponse
    {
        $this->assertActive($client);
        $challenge = $this->challengeForClient($client, $token);
        $role = $this->roleFromRequest($request, $token);
        $level = $client->levelMeta();

        if ($challenge->status === X1Challenge::STATUS_FINISHED) {
            return redirect()->route('client.x1.scoreboard', [$client, $token]);
        }

        if ($challenge->status === X1Challenge::STATUS_EXPIRED || $challenge->isExpired()) {
            return view('x1.expired', [
                'challenge' => $challenge,
                'level' => $level,
                'x1HubUrl' => route('client.x1.hub', $client),
                'client' => $client,
            ]);
        }

        if ($role === 'creator') {
            if ($challenge->status === X1Challenge::STATUS_PLAYING_CREATOR) {
                return view('x1.play', [
                    'challenge' => $challenge,
                    'role' => 'creator',
                    'playerName' => $challenge->creator_name,
                    'level' => $level,
                    'finishUrl' => route('client.x1.finish', [$client, $token]),
                    'client' => $client,
                ]);
            }

            if ($challenge->status === X1Challenge::STATUS_AWAITING_OPPONENT) {
                return view('x1.result', [
                    'challenge' => $challenge,
                    'role' => 'creator',
                    'whatsappUrl' => $this->x1->whatsappUrl(
                        $challenge,
                        $client->name,
                        route('client.x1.show', [$client, $token]),
                    ),
                    'level' => $level,
                    'homeUrl' => route('client.hub', $client),
                    'showUrl' => route('client.x1.show', [$client, $token]),
                    'client' => $client,
                ]);
            }

            if (in_array($challenge->status, [
                X1Challenge::STATUS_PLAYING_OPPONENT,
                X1Challenge::STATUS_FINISHED,
            ], true)) {
                return redirect()->route('client.x1.scoreboard', [$client, $token]);
            }
        }

        if ($role === 'opponent') {
            if ($challenge->status === X1Challenge::STATUS_PLAYING_OPPONENT) {
                return view('x1.play', [
                    'challenge' => $challenge,
                    'role' => 'opponent',
                    'playerName' => $challenge->opponent_name,
                    'level' => $level,
                    'finishUrl' => route('client.x1.finish', [$client, $token]),
                    'client' => $client,
                ]);
            }

            return redirect()->route('client.x1.scoreboard', [$client, $token]);
        }

        if ($challenge->status === X1Challenge::STATUS_AWAITING_OPPONENT) {
            return view('x1.join', [
                'challenge' => $challenge,
                'level' => $level,
                'joinUrl' => route('client.x1.join', [$client, $token]),
                'client' => $client,
            ]);
        }

        if ($challenge->status === X1Challenge::STATUS_PLAYING_CREATOR) {
            return view('x1.wait-creator', [
                'challenge' => $challenge,
                'level' => $level,
                'homeUrl' => route('client.hub', $client),
                'client' => $client,
            ]);
        }

        return redirect()->route('client.x1.scoreboard', [$client, $token]);
    }

    public function join(Request $request, QuizClient $client, string $token): RedirectResponse
    {
        $challenge = $this->challengeForClient($client, $token);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:40'],
        ]);

        $this->x1->joinOpponent($challenge, $data['name']);

        return redirect()
            ->route('client.x1.show', [$client, $token])
            ->cookie($this->roleCookieName($token), 'opponent', 60 * 24 * 8);
    }

    public function finish(Request $request, QuizClient $client, string $token): JsonResponse
    {
        $challenge = $this->challengeForClient($client, $token);
        $role = $this->roleFromRequest($request, $token);

        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        if ($role === 'creator') {
            $challenge = $this->x1->finishCreator($challenge, (int) $data['score']);

            return response()->json([
                'ok' => true,
                'status' => $challenge->status,
                'redirect' => route('client.x1.show', [$client, $token]),
                'whatsapp_url' => $this->x1->whatsappUrl(
                    $challenge,
                    $client->name,
                    route('client.x1.show', [$client, $token]),
                ),
            ]);
        }

        if ($role === 'opponent') {
            $challenge = $this->x1->finishOpponent($challenge, (int) $data['score']);

            return response()->json([
                'ok' => true,
                'status' => $challenge->status,
                'redirect' => route('client.x1.scoreboard', [$client, $token]),
            ]);
        }

        return response()->json(['message' => 'Sessão inválida para este desafio.'], 403);
    }

    public function scoreboard(QuizClient $client, string $token): View
    {
        $challenge = $this->challengeForClient($client, $token);

        return view('x1.scoreboard', [
            'challenge' => $challenge,
            'level' => $client->levelMeta(),
            'winner' => $challenge->winnerLabel(),
            'x1HubUrl' => route('client.x1.hub', $client),
            'homeUrl' => route('client.hub', $client),
            'client' => $client,
        ]);
    }

    private function challengeForClient(QuizClient $client, string $token): X1Challenge
    {
        $this->assertActive($client);
        $challenge = $this->x1->findByToken($token);
        abort_unless((int) $challenge->client_id === (int) $client->id, 404);

        return $challenge;
    }

    private function roleCookieName(string $token): string
    {
        return 'x1_role_'.str_replace('-', '', $token);
    }

    private function roleFromRequest(Request $request, string $token): ?string
    {
        $role = $request->cookie($this->roleCookieName($token));

        return in_array($role, ['creator', 'opponent'], true) ? $role : null;
    }

    private function assertActive(QuizClient $client): void
    {
        abort_unless($client->is_active, 404);
    }
}
