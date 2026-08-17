<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuizClient;
use App\Support\QuestionBank;
use Illuminate\Support\Facades\DB;

class ClientQuestionPersister
{
    /**
     * @param  list<array{categoria: string, pergunta: string, opcoes: list<string>, correta: int, emoji?: string, opcoesEmoji?: list<string>, imagem?: string}>  $items
     */
    public function persist(QuizClient $client, array $items, bool $useEmoji = true): int
    {
        $saved = 0;

        DB::transaction(function () use ($client, $items, $useEmoji, &$saved) {
            $next = $this->nextSequence($client->id);

            foreach ($items as $item) {
                $code = sprintf('c%d_%03d', $client->id, $next);
                if (strlen($code) > 16) {
                    $code = sprintf('c%d%03d', $client->id, $next);
                }

                while (Question::query()->where('code', $code)->exists()) {
                    $next++;
                    $code = sprintf('c%d_%03d', $client->id, $next);
                    if (strlen($code) > 16) {
                        $code = sprintf('c%d%03d', $client->id, $next);
                    }
                }

                $question = Question::query()->create([
                    'client_id' => $client->id,
                    'nivel' => QuizClient::CUSTOM_NIVEL,
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

        QuestionBank::forgetCache(QuizClient::CUSTOM_NIVEL, $client->id);

        return $saved;
    }

    private function nextSequence(int $clientId): int
    {
        $count = Question::query()->where('client_id', $clientId)->count();

        return $count + 1;
    }
}
