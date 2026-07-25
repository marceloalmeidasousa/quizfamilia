<?php

/**
 * Gerador do banco de perguntas (resources/data/perguntas.json).
 *
 * Rode com:  php resources/data/build-perguntas.php
 *
 * Formatos compactos:
 *  - Criança:      [categoria, emoji, pergunta, [opA, opB], [emojiA, emojiB], correta]
 *  - Adolescente:  [categoria, emoji, pergunta, [op1, op2, op3, op4], correta]
 *  - Adulto:       [categoria, emoji, pergunta, [op1, op2, op3, op4], correta]
 *
 * Os ids são gerados automaticamente (c001, d001, a001...).
 */

require __DIR__.'/perguntas-crianca.php';
require __DIR__.'/perguntas-adolescente.php';
require __DIR__.'/perguntas-adulto.php';

/**
 * Ordem embaralhada de índices, determinística por pergunta (diff estável).
 *
 * @return array<int, int>
 */
function shuffledOrder(int $size, string $seedText): array
{
    $order = range(0, $size - 1);
    mt_srand(crc32($seedText));
    for ($i = $size - 1; $i > 0; $i--) {
        $j = mt_rand(0, $i);
        [$order[$i], $order[$j]] = [$order[$j], $order[$i]];
    }
    mt_srand();

    return $order;
}

/**
 * @param  array<int, array{0:string,1:string,2:string,3:array<int,string>,4:array<int,string>,5:int}>  $rows
 * @return array<int, array<string, mixed>>
 */
function buildCrianca(array $rows): array
{
    $out = [];
    foreach ($rows as $i => $r) {
        [$categoria, $emoji, $pergunta, $opcoes, $opcoesEmoji, $correta] = $r;

        $order = shuffledOrder(count($opcoes), $pergunta);
        $novasOpcoes = [];
        $novosEmojis = [];
        $novaCorreta = 0;
        foreach ($order as $novoIndice => $antigo) {
            $novasOpcoes[] = $opcoes[$antigo];
            $novosEmojis[] = $opcoesEmoji[$antigo];
            if ($antigo === $correta) {
                $novaCorreta = $novoIndice;
            }
        }

        $out[] = [
            'id' => 'c'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
            'categoria' => $categoria,
            'emoji' => $emoji,
            'pergunta' => $pergunta,
            'opcoes' => $novasOpcoes,
            'opcoesEmoji' => $novosEmojis,
            'correta' => $novaCorreta,
        ];
    }

    return $out;
}

/**
 * @param  array<int, array{0:string,1:string,2:string,3:array<int,string>,4:int}>  $rows
 * @return array<int, array<string, mixed>>
 */
function buildQuatro(array $rows, string $prefix): array
{
    $out = [];
    foreach ($rows as $i => $r) {
        [$categoria, $emoji, $pergunta, $opcoes, $correta] = $r;

        $order = shuffledOrder(count($opcoes), $pergunta);
        $novasOpcoes = [];
        $novaCorreta = 0;
        foreach ($order as $novoIndice => $antigo) {
            $novasOpcoes[] = $opcoes[$antigo];
            if ($antigo === $correta) {
                $novaCorreta = $novoIndice;
            }
        }

        $out[] = [
            'id' => $prefix.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
            'categoria' => $categoria,
            'emoji' => $emoji,
            'pergunta' => $pergunta,
            'opcoes' => $novasOpcoes,
            'correta' => $novaCorreta,
        ];
    }

    return $out;
}

$data = [
    'crianca' => buildCrianca(criancaRows()),
    'adolescente' => buildQuatro(adolescenteRows(), 'd'),
    'adulto' => buildQuatro(adultoRows(), 'a'),
];

foreach ($data as $nivel => $rows) {
    $count = count($rows);
    fwrite(STDERR, sprintf("%-12s %d perguntas\n", $nivel, $count));

    $perguntas = array_column($rows, 'pergunta');
    $dupes = array_filter(array_count_values($perguntas), fn ($n) => $n > 1);
    if ($dupes) {
        fwrite(STDERR, "  ⚠ duplicadas em {$nivel}:\n");
        foreach (array_keys($dupes) as $p) {
            fwrite(STDERR, "    - {$p}\n");
        }
    }
}

$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents(__DIR__.'/perguntas.json', $json."\n");

fwrite(STDERR, "OK -> resources/data/perguntas.json\n");
