<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\LivePlayer;
use App\Models\LiveSession;
use App\Models\QuizClient;
use App\Services\LiveGameService;
use App\Support\QuestionBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientLiveController extends Controller
{
    public function __construct(private LiveGameService $live) {}

    public function hub(QuizClient $client): View
    {
        $this->assertActive($client);

        $categorias = QuestionBank::categoriesFor(QuizClient::CUSTOM_NIVEL, $client->id);

        return view('client.live-hub', [
            'client' => $client,
            'categorias' => $categorias,
            'level' => $client->levelMeta(),
        ]);
    }

    public function create(Request $request, QuizClient $client): RedirectResponse
    {
        $this->assertActive($client);

        $data = $request->validate([
            'categoria' => ['nullable', 'string', 'max:80'],
        ]);

        $session = $this->live->createSession(
            QuizClient::CUSTOM_NIVEL,
            $data['categoria'] ?? null,
            $request,
            $client->id,
        );

        return redirect()
            ->route('client.live.host', [$client, $session->pin])
            ->cookie('live_host_token', $session->host_token, 60 * 12, null, null, false, true);
    }

    public function host(Request $request, QuizClient $client, string $pin): View
    {
        $this->assertActive($client);
        $session = $this->sessionForClient($client, $pin);
        $this->live->assertHost($session, $request->cookie('live_host_token'));

        return view('live.host', [
            'session' => $session,
            'level' => $client->levelMeta(),
            'joinUrl' => route('client.live.hub', $client),
            'liveBackUrl' => route('client.live.hub', $client),
            'stateUrl' => route('client.live.host.state', [$client, $session->pin]),
            'startUrl' => route('client.live.start', [$client, $session->pin]),
            'advanceUrl' => route('client.live.advance', [$client, $session->pin]),
            'client' => $client,
        ]);
    }

    public function join(Request $request, QuizClient $client): RedirectResponse
    {
        $this->assertActive($client);

        $data = $request->validate([
            'pin' => ['required', 'string', 'size:6'],
            'name' => ['required', 'string', 'max:40'],
        ]);

        $player = $this->live->join(
            preg_replace('/\D/', '', $data['pin']),
            $data['name'],
            $client->id,
        );

        return redirect()->route('client.live.player', [$client, $player->token]);
    }

    public function play(QuizClient $client, string $token): View
    {
        $this->assertActive($client);
        $player = LivePlayer::query()->where('token', $token)->with('session')->firstOrFail();
        abort_unless((int) $player->session->client_id === (int) $client->id, 404);

        return view('live.player', [
            'player' => $player,
            'session' => $player->session,
            'level' => $client->levelMeta(),
            'stateUrl' => route('client.live.player.state', [$client, $player->token]),
            'answerUrl' => route('client.live.answer', [$client, $player->token]),
            'hubUrl' => route('client.hub', $client),
            'client' => $client,
        ]);
    }

    public function start(Request $request, QuizClient $client, string $pin): JsonResponse
    {
        $session = $this->sessionForClient($client, $pin);
        $this->live->assertHost($session, $request->cookie('live_host_token'));

        return response()->json($this->live->hostState($this->live->start($session)));
    }

    public function advance(Request $request, QuizClient $client, string $pin): JsonResponse
    {
        $session = $this->sessionForClient($client, $pin);
        $this->live->assertHost($session, $request->cookie('live_host_token'));

        return response()->json($this->live->hostState($this->live->advance($session)));
    }

    public function answer(Request $request, QuizClient $client, string $token): JsonResponse
    {
        $player = LivePlayer::query()->where('token', $token)->with('session')->firstOrFail();
        abort_unless((int) $player->session->client_id === (int) $client->id, 404);

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

    public function hostState(Request $request, QuizClient $client, string $pin): JsonResponse
    {
        $session = $this->sessionForClient($client, $pin);
        $this->live->assertHost($session, $request->cookie('live_host_token'));

        return response()->json($this->live->hostState($session));
    }

    public function playerState(QuizClient $client, string $token): JsonResponse
    {
        $player = LivePlayer::query()->where('token', $token)->with('session')->firstOrFail();
        abort_unless((int) $player->session->client_id === (int) $client->id, 404);

        return response()->json($this->live->playerState($player));
    }

    private function sessionForClient(QuizClient $client, string $pin): LiveSession
    {
        $this->assertActive($client);

        return LiveSession::query()
            ->where('pin', $pin)
            ->where('client_id', $client->id)
            ->firstOrFail();
    }

    private function assertActive(QuizClient $client): void
    {
        abort_unless($client->is_active, 404);
    }
}
