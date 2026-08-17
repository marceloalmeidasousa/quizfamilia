<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GameController;
use App\Models\GamePlay;
use App\Models\QuizClient;
use App\Services\GeoLookup;
use App\Support\QuestionBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientPortalController extends Controller
{
    public function hub(QuizClient $client): View
    {
        $this->assertActive($client);

        return view('client.hub', [
            'client' => $client,
            'questionCount' => $client->usesSystemCategories()
                ? array_sum(array_column(QuestionBank::categoriesFamily(), 'total'))
                : $client->questions()->count(),
        ]);
    }

    public function quiz(QuizClient $client): View
    {
        $this->assertActive($client);

        $categorias = QuestionBank::categoriesForClient($client);

        return view('client.quiz', [
            'client' => $client,
            'categorias' => $categorias,
            'level' => $client->levelMeta(),
        ]);
    }

    public function play(Request $request, QuizClient $client): View
    {
        $this->assertActive($client);

        $categoria = trim((string) $request->query('categoria', ''));
        if ($categoria === '' || strtolower($categoria) === 'todas') {
            $categoria = null;
        }

        $categorias = QuestionBank::categoriesForClient($client);
        $available = collect($categorias)->pluck('nome')->all();
        if ($categoria !== null && ! in_array($categoria, $available, true)) {
            abort(404, 'Categoria não encontrada.');
        }

        $perguntas = QuestionBank::allForClient($client, $categoria);

        return view('game.level', [
            'nivel' => QuizClient::CUSTOM_NIVEL,
            'level' => $client->levelMeta(),
            'perguntas' => $perguntas,
            'categoria' => $categoria,
            'categorias' => $categorias,
            'rodadas' => min(GameController::RODADAS, count($perguntas)),
            'quizBackUrl' => route('client.quiz', $client),
            'playStoreUrl' => route('client.play.store', $client),
            'client' => $client,
        ]);
    }

    public function playStore(Request $request, QuizClient $client, GeoLookup $geo): JsonResponse
    {
        $this->assertActive($client);

        $data = $request->validate([
            'categoria' => ['nullable', 'string', 'max:80'],
        ]);

        $categoria = isset($data['categoria']) ? trim($data['categoria']) : null;
        if ($categoria === '' || strtolower((string) $categoria) === 'todas') {
            $categoria = null;
        }

        $ip = $request->ip();
        $location = $geo->lookup($ip);

        GamePlay::query()->create([
            'type' => GamePlay::TYPE_SOLO,
            'nivel' => QuizClient::CUSTOM_NIVEL,
            'categoria' => $categoria,
            'live_session_id' => null,
            'player_names' => [$client->name],
            'ip_address' => $ip,
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'country' => $location['country'],
            'city' => $location['city'],
            'started_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    private function assertActive(QuizClient $client): void
    {
        abort_unless($client->is_active, 404);
    }
}
