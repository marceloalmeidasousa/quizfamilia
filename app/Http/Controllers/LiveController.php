<?php

namespace App\Http\Controllers;

use App\Models\LivePlayer;
use App\Models\LiveSession;
use App\Services\LiveGameService;
use App\Support\QuestionBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveController extends Controller
{
    public function __construct(private LiveGameService $live) {}

    public function hub(): View
    {
        return view('live.hub', [
            'levels' => QuestionBank::levels(),
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nivel' => ['required', 'string', 'in:'.implode(',', array_keys(QuestionBank::levels()))],
        ]);

        $session = $this->live->createSession($data['nivel']);

        return redirect()
            ->route('live.host', $session->pin)
            ->cookie('live_host_token', $session->host_token, 60 * 12, null, null, false, true);
    }

    public function host(Request $request, string $pin): View
    {
        $session = LiveSession::query()->where('pin', $pin)->firstOrFail();
        $this->live->assertHost($session, $request->cookie('live_host_token'));

        return view('live.host', [
            'session' => $session,
            'level' => QuestionBank::levels()[$session->nivel] ?? null,
            'joinUrl' => url('/ao-vivo'),
        ]);
    }

    public function join(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pin' => ['required', 'string', 'size:6'],
            'name' => ['required', 'string', 'max:40'],
        ]);

        $player = $this->live->join(preg_replace('/\D/', '', $data['pin']), $data['name']);

        return redirect()->route('live.player', $player->token);
    }

    public function play(string $token): View
    {
        $player = LivePlayer::query()->where('token', $token)->with('session')->firstOrFail();

        return view('live.player', [
            'player' => $player,
            'session' => $player->session,
            'level' => QuestionBank::levels()[$player->session->nivel] ?? null,
        ]);
    }

    public function start(Request $request, string $pin): JsonResponse
    {
        $session = LiveSession::query()->where('pin', $pin)->firstOrFail();
        $this->live->assertHost($session, $request->cookie('live_host_token'));

        $session = $this->live->start($session);

        return response()->json($this->live->hostState($session));
    }

    public function advance(Request $request, string $pin): JsonResponse
    {
        $session = LiveSession::query()->where('pin', $pin)->firstOrFail();
        $this->live->assertHost($session, $request->cookie('live_host_token'));

        $session = $this->live->advance($session);

        return response()->json($this->live->hostState($session));
    }

    public function answer(Request $request, string $token): JsonResponse
    {
        $player = LivePlayer::query()->where('token', $token)->firstOrFail();

        $data = $request->validate([
            'choice' => ['required', 'integer', 'min:0', 'max:10'],
        ]);

        $answer = $this->live->answer($player, (int) $data['choice']);

        return response()->json([
            'ok' => true,
            'correct' => $answer->correct,
            'points' => $answer->points,
            'state' => $this->live->playerState($player->fresh()),
        ]);
    }

    public function hostState(Request $request, string $pin): JsonResponse
    {
        $session = LiveSession::query()->where('pin', $pin)->firstOrFail();
        $this->live->assertHost($session, $request->cookie('live_host_token'));

        return response()->json($this->live->hostState($session));
    }

    public function playerState(string $token): JsonResponse
    {
        $player = LivePlayer::query()->where('token', $token)->firstOrFail();

        return response()->json($this->live->playerState($player));
    }
}
