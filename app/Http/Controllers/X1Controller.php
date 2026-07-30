<?php

namespace App\Http\Controllers;

use App\Models\X1Challenge;
use App\Services\X1ChallengeService;
use App\Support\QuestionBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class X1Controller extends Controller
{
    public function __construct(private X1ChallengeService $x1) {}

    public function hub(): View
    {
        $levels = collect(QuestionBank::levels())
            ->only(X1Challenge::LEVELS)
            ->all();

        $categories = [];
        foreach (X1Challenge::LEVELS as $slug) {
            $categories[$slug] = QuestionBank::categoriesFor($slug);
        }

        return view('x1.hub', [
            'levels' => $levels,
            'categoriesByLevel' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:40'],
            'nivel' => ['required', 'string', 'in:'.implode(',', X1Challenge::LEVELS)],
            'categoria' => ['nullable', 'string', 'max:80'],
        ]);

        $challenge = $this->x1->create(
            $data['name'],
            $data['nivel'],
            $data['categoria'] ?? null,
            $request,
        );

        return redirect()
            ->route('x1.show', $challenge->token)
            ->cookie($this->roleCookieName($challenge->token), 'creator', 60 * 24 * 8);
    }

    public function show(Request $request, string $token): View|RedirectResponse
    {
        $challenge = $this->x1->findByToken($token);
        $role = $this->roleFromRequest($request, $token);

        if ($challenge->status === X1Challenge::STATUS_FINISHED) {
            return redirect()->route('x1.scoreboard', $token);
        }

        if ($challenge->status === X1Challenge::STATUS_EXPIRED || $challenge->isExpired()) {
            return view('x1.expired', [
                'challenge' => $challenge,
                'level' => QuestionBank::levels()[$challenge->nivel] ?? null,
            ]);
        }

        if ($role === 'creator') {
            if ($challenge->status === X1Challenge::STATUS_PLAYING_CREATOR) {
                return view('x1.play', [
                    'challenge' => $challenge,
                    'role' => 'creator',
                    'playerName' => $challenge->creator_name,
                    'level' => QuestionBank::levels()[$challenge->nivel] ?? null,
                ]);
            }

            if ($challenge->status === X1Challenge::STATUS_AWAITING_OPPONENT) {
                return view('x1.result', [
                    'challenge' => $challenge,
                    'role' => 'creator',
                    'whatsappUrl' => $this->x1->whatsappUrl($challenge, config('app.name')),
                    'level' => QuestionBank::levels()[$challenge->nivel] ?? null,
                ]);
            }

            if (in_array($challenge->status, [
                X1Challenge::STATUS_PLAYING_OPPONENT,
                X1Challenge::STATUS_FINISHED,
            ], true)) {
                return redirect()->route('x1.scoreboard', $token);
            }
        }

        if ($role === 'opponent') {
            if ($challenge->status === X1Challenge::STATUS_PLAYING_OPPONENT) {
                return view('x1.play', [
                    'challenge' => $challenge,
                    'role' => 'opponent',
                    'playerName' => $challenge->opponent_name,
                    'level' => QuestionBank::levels()[$challenge->nivel] ?? null,
                ]);
            }

            return redirect()->route('x1.scoreboard', $token);
        }

        // Visitante sem cookie: convidar a entrar se aguardando
        if ($challenge->status === X1Challenge::STATUS_AWAITING_OPPONENT) {
            return view('x1.join', [
                'challenge' => $challenge,
                'level' => QuestionBank::levels()[$challenge->nivel] ?? null,
            ]);
        }

        if ($challenge->status === X1Challenge::STATUS_PLAYING_CREATOR) {
            return view('x1.wait-creator', [
                'challenge' => $challenge,
                'level' => QuestionBank::levels()[$challenge->nivel] ?? null,
            ]);
        }

        return redirect()->route('x1.scoreboard', $token);
    }

    public function join(Request $request, string $token): RedirectResponse
    {
        $challenge = $this->x1->findByToken($token);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:40'],
        ]);

        $challenge = $this->x1->joinOpponent($challenge, $data['name']);

        return redirect()
            ->route('x1.show', $token)
            ->cookie($this->roleCookieName($token), 'opponent', 60 * 24 * 8);
    }

    public function finish(Request $request, string $token): JsonResponse
    {
        $challenge = $this->x1->findByToken($token);
        $role = $this->roleFromRequest($request, $token);

        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        if ($role === 'creator') {
            $challenge = $this->x1->finishCreator($challenge, (int) $data['score']);

            return response()->json([
                'ok' => true,
                'status' => $challenge->status,
                'redirect' => route('x1.show', $token),
                'whatsapp_url' => $this->x1->whatsappUrl($challenge, config('app.name')),
            ]);
        }

        if ($role === 'opponent') {
            $challenge = $this->x1->finishOpponent($challenge, (int) $data['score']);

            return response()->json([
                'ok' => true,
                'status' => $challenge->status,
                'redirect' => route('x1.scoreboard', $token),
            ]);
        }

        return response()->json(['message' => 'Sessão inválida para este desafio.'], 403);
    }

    public function scoreboard(string $token): View
    {
        $challenge = $this->x1->findByToken($token);

        return view('x1.scoreboard', [
            'challenge' => $challenge,
            'level' => QuestionBank::levels()[$challenge->nivel] ?? null,
            'winner' => $challenge->winnerLabel(),
        ]);
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
}
