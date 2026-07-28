<?php

/**
 * Gera resources/data/perguntas.json a partir das fontes PHP.
 *
 * Rode: php resources/data/build-perguntas.php
 *
 * Fontes:
 *  - perguntas-{nivel}.php          (base)
 *  - perguntas-{nivel}-extra.php    (opcional, extras geradas)
 */

require __DIR__.'/perguntas-crianca.php';
require __DIR__.'/perguntas-adolescente.php';
require __DIR__.'/perguntas-adulto.php';

foreach ([
    __DIR__.'/perguntas-crianca-extra.php',
    __DIR__.'/perguntas-crianca-temas-min10.php',
    __DIR__.'/perguntas-adolescente-extra.php',
    __DIR__.'/perguntas-adolescente-temas.php',
    __DIR__.'/perguntas-adolescente-ingles.php',
    __DIR__.'/perguntas-adolescente-lote.php',
    __DIR__.'/perguntas-adulto-extra.php',
    __DIR__.'/perguntas-adulto-bts.php',
    __DIR__.'/perguntas-adulto-dorama.php',
    __DIR__.'/perguntas-adulto-futebol-internacional.php',
    __DIR__.'/perguntas-adulto-futebol-nacional.php',
] as $extra) {
    if (is_file($extra)) {
        require_once $extra;
    }
}

/**
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
 * Remove perguntas duplicadas pelo texto.
 *
 * @param  array<int, array<int, mixed>>  $rows
 * @return array<int, array<int, mixed>>
 */
function uniqueByPergunta(array $rows, int $perguntaIndex = 2): array
{
    $seen = [];
    $out = [];
    foreach ($rows as $row) {
        $key = mb_strtolower(trim((string) ($row[$perguntaIndex] ?? '')));
        if ($key === '' || isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $out[] = $row;
    }

    return $out;
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
            $novosEmojis[] = $opcoesEmoji[$antigo] ?? '❔';
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

$crianca = uniqueByPergunta(array_merge(
    criancaRows(),
    function_exists('criancaExtraRows') ? criancaExtraRows() : [],
    function_exists('criancaTemasMin10Rows') ? criancaTemasMin10Rows() : [],
));

$adolescente = uniqueByPergunta(array_merge(
    adolescenteRows(),
    function_exists('adolescenteExtraRows') ? adolescenteExtraRows() : [],
    function_exists('adolescenteTemasRows') ? adolescenteTemasRows() : [],
    function_exists('adolescenteInglesRows') ? adolescenteInglesRows() : [],
    function_exists('adolescenteLoteRows') ? adolescenteLoteRows() : [],
));

$adulto = uniqueByPergunta(array_merge(
    adultoRows(),
    function_exists('adultoExtraRows') ? adultoExtraRows() : [],
    function_exists('adultoBtsRows') ? adultoBtsRows() : [],
    function_exists('adultoDoramaRows') ? adultoDoramaRows() : [],
    function_exists('adultoFutebolInternacionalRows') ? adultoFutebolInternacionalRows() : [],
    function_exists('adultoFutebolNacionalRows') ? adultoFutebolNacionalRows() : [],
));

$minimo = 500;
$data = [
    'crianca' => buildCrianca($crianca),
    'adolescente' => buildQuatro($adolescente, 'd'),
    'adulto' => buildQuatro($adulto, 'a'),
];

foreach ($data as $nivel => $rows) {
    $count = count($rows);
    fwrite(STDERR, sprintf("%-12s %d perguntas%s\n", $nivel, $count, $count < $minimo ? ' ⚠ abaixo de 500' : ''));

    $perguntas = array_column($rows, 'pergunta');
    $dupes = array_filter(array_count_values($perguntas), fn ($n) => $n > 1);
    if ($dupes) {
        fwrite(STDERR, "  ⚠ duplicadas em {$nivel}: ".count($dupes)."\n");
    }
}

$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents(__DIR__.'/perguntas.json', $json."\n");

fwrite(STDERR, "OK -> resources/data/perguntas.json\n");
