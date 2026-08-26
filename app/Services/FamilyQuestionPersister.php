<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Support\QuestionBank;
use Illuminate\Support\Facades\DB;

class FamilyQuestionPersister
{
    /** @var array<string, string> */
    private const CODE_PREFIX = [
        'crianca' => 'c',
        'adolescente' => 'd',
        'adulto' => 'a',
    ];

    /**
     * @param  list<array{categoria: string, pergunta: string, opcoes: list<string>, correta: int, emoji?: string, opcoesEmoji?: list<string>, imagem?: string}>  $items
     */
    public function persist(string $nivel, array $items, bool $useEmoji = true): int
    {
        $prefix = self::CODE_PREFIX[$nivel] ?? 'f';
        $saved = 0;

        DB::transaction(function () use ($nivel, $prefix, $items, $useEmoji, &$saved) {
            $next = $this->nextSequence($nivel, $prefix);

            foreach ($items as $item) {
                $code = sprintf('%s_ai_%03d', $prefix, $next);

                while (Question::query()->where('code', $code)->exists()) {
                    $next++;
                    $code = sprintf('%s_ai_%03d', $prefix, $next);
                }

                $question = Question::query()->create([
                    'client_id' => null,
                    'nivel' => $nivel,
                    'code' => $code,
                    'categoria' => $item['categoria'],
                    'emoji' => $useEmoji ? ($item['emoji'] ?? null) : null,
                    'imagem' => $item['imagem'] ?? null,
                    'pergunta' => $item['pergunta'],
                ]);

                $opcoesEmoji = $useEmoji ? array_values($item['opcoesEmoji'] ?? []) : [];

                foreach ($item['opcoes'] as $i => $texto) {
                    QuestionOption::query()->create([
                        'question_id' => $question->id,
                        'sort_order' => $i,
                        'texto' => $texto,
                        'emoji' => $opcoesEmoji[$i] ?? null,
                        'is_correct' => $i === (int) $item['correta'],
                    ]);
                }

                $next++;
                $saved++;
            }
        });

        QuestionBank::forgetCache($nivel);

        return $saved;
    }

    private function nextSequence(string $nivel, string $prefix): int
    {
        $count = Question::query()
            ->whereNull('client_id')
            ->where('nivel', $nivel)
            ->where('code', 'like', $prefix.'_ai_%')
            ->count();

        return $count + 1;
    }
}
