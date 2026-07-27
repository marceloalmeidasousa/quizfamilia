<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Support\QuestionBank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QuestionSeeder extends Seeder
{
    /**
     * Lê resources/data/perguntas.json e popula o MySQL.
     * Idempotente: limpa e reinsere.
     *
     * Produção:
     *   php artisan db:seed --class=QuestionSeeder --force
     *
     * Atualizar o JSON (a partir dos PHP):
     *   php resources/data/build-perguntas.php
     */
    public function run(): void
    {
        $path = resource_path('data/perguntas.json');

        if (! is_file($path)) {
            throw new RuntimeException("Arquivo não encontrado: {$path}. Rode: php resources/data/build-perguntas.php");
        }

        $data = json_decode((string) file_get_contents($path), true);

        if (! is_array($data)) {
            throw new RuntimeException('perguntas.json inválido.');
        }

        DB::transaction(function () use ($data) {
            QuestionOption::query()->delete();
            Question::query()->delete();

            $this->seedNivel('crianca', 'c', $data['crianca'] ?? []);
            $this->seedNivel('adolescente', 'd', $data['adolescente'] ?? []);
            $this->seedNivel('adulto', 'a', $data['adulto'] ?? []);
        });

        QuestionBank::forgetCache();

        $counts = Question::query()
            ->selectRaw('nivel, count(*) as total')
            ->groupBy('nivel')
            ->pluck('total', 'nivel');

        foreach (['crianca', 'adolescente', 'adulto'] as $nivel) {
            $this->command?->info(sprintf('%s: %d perguntas', $nivel, (int) ($counts[$nivel] ?? 0)));
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function seedNivel(string $nivel, string $prefix, array $rows): void
    {
        $now = now();
        $optionRows = [];

        foreach ($rows as $i => $row) {
            $code = (string) ($row['id'] ?? $prefix.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT));

            $question = Question::query()->create([
                'nivel' => $nivel,
                'code' => $code,
                'categoria' => (string) ($row['categoria'] ?? 'Geral'),
                'emoji' => $row['emoji'] ?? null,
                'pergunta' => (string) ($row['pergunta'] ?? ''),
            ]);

            $opcoes = array_values($row['opcoes'] ?? []);
            $emojis = array_values($row['opcoesEmoji'] ?? []);
            $correta = (int) ($row['correta'] ?? 0);

            foreach ($opcoes as $j => $texto) {
                $optionRows[] = [
                    'question_id' => $question->id,
                    'sort_order' => $j,
                    'texto' => (string) $texto,
                    'emoji' => $emojis[$j] ?? null,
                    'is_correct' => $j === $correta,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($optionRows, 500) as $chunk) {
            QuestionOption::query()->insert($chunk);
        }
    }
}
