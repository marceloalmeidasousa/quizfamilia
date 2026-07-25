<?php

namespace App\Support;

use App\Http\Controllers\GameController;

class QuestionBank
{
    /**
     * @return array<string, array{title: string, subtitle: string, description: string, accent: string, age: string}>
     */
    public static function levels(): array
    {
        return GameController::LEVELS;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function allFor(string $nivel): array
    {
        $path = resource_path('data/perguntas.json');

        if (! is_file($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return $data[$nivel] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function draw(string $nivel, int $count = 10): array
    {
        $questions = self::allFor($nivel);

        shuffle($questions);

        return array_values(array_slice($questions, 0, min($count, count($questions))));
    }
}
