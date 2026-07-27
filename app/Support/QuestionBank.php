<?php

namespace App\Support;

use App\Http\Controllers\GameController;
use App\Models\Question;
use Illuminate\Support\Facades\Cache;

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
    public static function allFor(string $nivel, ?string $categoria = null): array
    {
        $questions = Cache::remember("questions.bank.{$nivel}", now()->addHour(), function () use ($nivel) {
            return Question::query()
                ->where('nivel', $nivel)
                ->with('options')
                ->orderBy('id')
                ->get()
                ->map(fn (Question $question) => $question->toBankItem())
                ->all();
        });

        if ($categoria === null || $categoria === '' || $categoria === 'todas') {
            return $questions;
        }

        return array_values(array_filter(
            $questions,
            fn (array $q) => ($q['categoria'] ?? '') === $categoria,
        ));
    }

    /**
     * @return array<int, array{nome: string, total: int}>
     */
    public static function categoriesFor(string $nivel): array
    {
        return Cache::remember("questions.categories.{$nivel}", now()->addHour(), function () use ($nivel) {
            return Question::query()
                ->where('nivel', $nivel)
                ->selectRaw('categoria as nome, count(*) as total')
                ->groupBy('categoria')
                ->orderBy('categoria')
                ->get()
                ->map(fn ($row) => [
                    'nome' => $row->nome,
                    'total' => (int) $row->total,
                ])
                ->all();
        });
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
    public static function draw(string $nivel, int $count = 10, ?string $categoria = null): array
    {
        $questions = self::allFor($nivel, $categoria);

        shuffle($questions);

        $drawn = array_slice($questions, 0, min($count, count($questions)));

        return array_values(array_map(
            fn (array $question) => self::withShuffledOptions($question),
            $drawn,
        ));
    }

    public static function forgetCache(?string $nivel = null): void
    {
        if ($nivel) {
            Cache::forget("questions.bank.{$nivel}");
            Cache::forget("questions.categories.{$nivel}");

            return;
        }

        foreach (array_keys(self::levels()) as $key) {
            Cache::forget("questions.bank.{$key}");
            Cache::forget("questions.categories.{$key}");
        }
    }
}
