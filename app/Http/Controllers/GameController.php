<?php

namespace App\Http\Controllers;

use App\Support\QuestionBank;
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
            'subtitle' => 'Nível moderado',
            'description' => 'Perguntas de nível moderado para 15 anos ou mais — com algumas pegadinhas no meio.',
            'accent' => 'ocean',
            'age' => '15+',
        ],
    ];

    public function home(): View
    {
        return view('home');
    }

    public function quiz(): View
    {
        return view('game.quiz-levels', [
            'levels' => self::LEVELS,
        ]);
    }

    /** Quantidade de perguntas sorteadas por partida. */
    public const RODADAS = 10;

    public function level(string $nivel): View
    {
        abort_unless(array_key_exists($nivel, self::LEVELS), 404);

        $perguntas = QuestionBank::allFor($nivel);

        return view('game.level', [
            'nivel' => $nivel,
            'level' => self::LEVELS[$nivel],
            'perguntas' => $perguntas,
            'rodadas' => min(self::RODADAS, count($perguntas)),
        ]);
    }
}
