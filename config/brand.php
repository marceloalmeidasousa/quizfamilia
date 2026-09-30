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
    | Empresa responsável (rodapé, hero e contato comercial)
    |--------------------------------------------------------------------------
    */
    'company' => [
        'name' => 'Ti3 Tecnologia',
        'domain' => 'ti3tecnologia.com.br',
        'url' => 'https://ti3tecnologia.com.br',
        'whatsapp' => env('COMPANY_WHATSAPP', '5535997158741'),
        'whatsapp_display' => env('COMPANY_WHATSAPP_DISPLAY', '+55 35 99715-8741'),
        'whatsapp_message' => 'Olá! Quero levar o quiz para a minha Instituição de Ensino.',
    ],

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

        'quizedu' => [
            'hosts' => [
                'quizedu.com.br',
                'www.quizedu.com.br',
            ],
            'name' => 'Quiz Edu',
            'name_html' => 'Quiz <span class="text-brand-soft">Edu</span>',
            'tagline' => 'Feito para jogar junto.',
            'description' => 'Quiz Edu — diversão para criança, adolescente e adulto jogarem juntos.',
        ],

    ],

];
