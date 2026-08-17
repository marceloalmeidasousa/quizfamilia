<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiQuestionGenerator
{
    /**
     * Gera um lote de perguntas no formato do banco.
     *
     * @param  list<string>  $categories
     * @return list<array{categoria: string, pergunta: string, opcoes: list<string>, correta: int, emoji?: string}>
     */
    public function generateBatch(string $prompt, array $categories, int $count): array
    {
        $count = max(1, min(10, $count));
        $key = (string) config('services.openai.key');

        if ($key === '') {
            throw new RuntimeException('OPENAI_API_KEY não configurada no .env.');
        }

        $categoriesList = implode(', ', $categories);
        $system = <<<'TXT'
Você gera perguntas de quiz educativo em português do Brasil.
Responda APENAS com um JSON válido (sem markdown) no formato:
{"perguntas":[{"categoria":"...","emoji":"🩺","pergunta":"...","opcoes":["A","B","C","D"],"correta":0}]}
Regras:
- exatamente 4 opções por pergunta
- "correta" é o índice 0-3 da resposta certa
- use somente as categorias informadas
- perguntas claras, objetivas, nível universitário/adulto
- evite repetir perguntas
TXT;

        $user = "Contexto do cliente / tema: {$prompt}\n".
            "Categorias permitidas: {$categoriesList}\n".
            "Gere exatamente {$count} perguntas, distribuídas entre as categorias quando possível.";

        $baseUrl = rtrim((string) config('services.openai.base_url'), '/');
        $model = (string) config('services.openai.model', 'gpt-4o-mini');

        try {
            $response = Http::timeout(90)
                ->withToken($key)
                ->acceptJson()
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'temperature' => 0.7,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                ])
                ->throw()
                ->json();
        } catch (RequestException $e) {
            throw new RuntimeException('Falha na API OpenAI: '.$e->getMessage(), 0, $e);
        }

        $content = $response['choices'][0]['message']['content'] ?? '';
        $decoded = json_decode((string) $content, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Resposta da IA não é JSON válido.');
        }

        $items = $decoded['perguntas'] ?? $decoded['questions'] ?? null;
        if (! is_array($items)) {
            throw new RuntimeException('JSON da IA sem lista de perguntas.');
        }

        $normalized = [];
        $allowed = array_map('strval', $categories);

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $opcoes = array_values(array_map('strval', $item['opcoes'] ?? $item['options'] ?? []));
            if (count($opcoes) !== 4) {
                continue;
            }

            $categoria = trim((string) ($item['categoria'] ?? $item['category'] ?? ''));
            if ($categoria === '' || ! in_array($categoria, $allowed, true)) {
                $categoria = $allowed[0] ?? 'Geral';
            }

            $correta = (int) ($item['correta'] ?? $item['correct'] ?? 0);
            if ($correta < 0 || $correta > 3) {
                $correta = 0;
            }

            $pergunta = trim((string) ($item['pergunta'] ?? $item['question'] ?? ''));
            if ($pergunta === '') {
                continue;
            }

            $row = [
                'categoria' => mb_substr($categoria, 0, 80),
                'pergunta' => $pergunta,
                'opcoes' => array_map(fn ($t) => mb_substr($t, 0, 255), $opcoes),
                'correta' => $correta,
            ];

            $emoji = trim((string) ($item['emoji'] ?? ''));
            if ($emoji !== '') {
                $row['emoji'] = mb_substr($emoji, 0, 16);
            }

            $normalized[] = $row;

            if (count($normalized) >= $count) {
                break;
            }
        }

        if ($normalized === []) {
            throw new RuntimeException('A IA não retornou perguntas válidas.');
        }

        return $normalized;
    }
}
