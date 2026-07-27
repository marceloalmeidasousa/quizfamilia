<?php

namespace App\Http\Controllers;

use App\Support\QuestionBank;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameController extends Controller
{
    /**
     * @var array<string, array{title: string, subtitle: string, description: string, accent: string, age?: string}>
     */
    public const LEVELS = [
        'crianca' => [
            'title' => 'Criança',
            'subtitle' => 'Nível fácil',
            'description' => 'Perguntas fáceis com 2 respostas — desenhos animados para 3 a 6 anos.',
            'accent' => 'sunshine',
            'age' => '3–6 anos',
        ],
        'adolescente' => [
            'title' => 'Adolescente',
            'subtitle' => 'Nível médio',
            'description' => 'Perguntas de nível médio para 7 a 14 anos.',
            'accent' => 'coral',
            'age' => '7–14 anos',
        ],
        'adulto' => [
            'title' => 'Adulto',
            'subtitle' => 'Nível difícil',
            'description' => 'Perguntas de nível moderado para 15 anos ou mais — com algumas pegadinhas no meio.',
            'accent' => 'ocean',
            'age' => '15+',
        ],
    ];

    /** Quantidade de perguntas sorteadas por partida. */
    public const RODADAS = 10;

    public function home(): View
    {
        return view('home');
    }

    public function quiz(): View
    {
        $categories = [];

        foreach (array_keys(self::LEVELS) as $slug) {
            $categories[$slug] = QuestionBank::categoriesFor($slug);
        }

        return view('game.quiz-levels', [
            'levels' => self::LEVELS,
            'categoriesByLevel' => $categories,
        ]);
    }

    public function level(Request $request, string $nivel): View
    {
        abort_unless(array_key_exists($nivel, self::LEVELS), 404);

        $categoria = trim((string) $request->query('categoria', ''));
        if ($categoria === '' || strtolower($categoria) === 'todas') {
            $categoria = null;
        }

        $available = collect(QuestionBank::categoriesFor($nivel))->pluck('nome')->all();
        if ($categoria !== null && ! in_array($categoria, $available, true)) {
            abort(404, 'Categoria não encontrada neste nível.');
        }

        $perguntas = QuestionBank::allFor($nivel, $categoria);

        return view('game.level', [
            'nivel' => $nivel,
            'level' => self::LEVELS[$nivel],
            'perguntas' => $perguntas,
            'categoria' => $categoria,
            'categorias' => QuestionBank::categoriesFor($nivel),
            'rodadas' => min(self::RODADAS, count($perguntas)),
        ]);
    }
}
