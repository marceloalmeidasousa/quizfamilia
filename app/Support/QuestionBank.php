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
     * Embaralha as opções mantendo o índice da resposta correta.
     *
     * @param  array<string, mixed>  $question
     * @return array<string, mixed>
     */
    public static function withShuffledOptions(array $question): array
    {
        $opcoes = array_values($question['opcoes'] ?? []);
        $count = count($opcoes);

        if ($count < 2) {
            return $question;
        }

        $emojis = array_values($question['opcoesEmoji'] ?? []);
        $hasEmojis = count($emojis) === $count;
        $correta = (int) ($question['correta'] ?? 0);

        $indices = range(0, $count - 1);
        shuffle($indices);

        $novasOpcoes = [];
        $novosEmojis = [];
        $novaCorreta = 0;

        foreach ($indices as $novoIndice => $antigo) {
            $novasOpcoes[] = $opcoes[$antigo];
            if ($hasEmojis) {
                $novosEmojis[] = $emojis[$antigo];
            }
            if ($antigo === $correta) {
                $novaCorreta = $novoIndice;
            }
        }

        $question['opcoes'] = $novasOpcoes;
        $question['correta'] = $novaCorreta;

        if ($hasEmojis) {
            $question['opcoesEmoji'] = $novosEmojis;
        }

        return $question;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function draw(string $nivel, int $count = 10): array
    {
        $questions = self::allFor($nivel);

        shuffle($questions);

        $drawn = array_slice($questions, 0, min($count, count($questions)));

        return array_values(array_map(
            fn (array $question) => self::withShuffledOptions($question),
            $drawn,
        ));
    }
}
