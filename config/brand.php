<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Marca padrão (quando o host não casar com nenhum site)
    |--------------------------------------------------------------------------
    */
    'default' => env('BRAND_DEFAULT', 'quizfamilia'),

    /*
    |--------------------------------------------------------------------------
    | Forçar marca (útil em local: BRAND_FORCE=animaquiz)
    |--------------------------------------------------------------------------
    */
    'force' => env('BRAND_FORCE'),

    /*
    |--------------------------------------------------------------------------
    | Sites por domínio
    |--------------------------------------------------------------------------
    */
    'sites' => [

        'quizfamilia' => [
            'hosts' => [
                'quizemfamilia.com.br',
                'www.quizemfamilia.com.br',
                'localhost',
                '127.0.0.1',
            ],
            'name' => 'Quiz em Família',
            'name_html' => 'Quiz em <span class="text-brand-soft">Família</span>',
            'tagline' => 'Feito para jogar junto.',
            'description' => 'Quiz em Família — diversão para criança, adolescente e adulto jogarem juntos.',
        ],

        'animaquiz' => [
            'hosts' => [
                'animaquiz.com.br',
                'www.animaquiz.com.br',
            ],
            'name' => 'Anima Quiz',
            'name_html' => 'Anima <span class="text-brand-soft">Quiz</span>',
            'tagline' => 'Feito para jogar junto.',
            'description' => 'Anima Quiz — diversão para criança, adolescente e adulto jogarem juntos.',
        ],

    ],

];
