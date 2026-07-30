<?php

namespace App\Http\Controllers;

use App\Models\GamePlay;
use App\Services\GeoLookup;
use App\Support\QuestionBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GamePlayController extends Controller
{
    public function store(Request $request, string $nivel, GeoLookup $geo): JsonResponse
    {
        abort_unless(array_key_exists($nivel, QuestionBank::levels()), 404);

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
            'nivel' => $nivel,
            'categoria' => $categoria,
            'live_session_id' => null,
            'player_names' => ['Visitante'],
            'ip_address' => $ip,
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'country' => $location['country'],
            'city' => $location['city'],
            'started_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }
}
