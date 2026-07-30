<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google AdSense
    |--------------------------------------------------------------------------
    |
    | Ative só em produção depois da aprovação do AdSense.
    | Client: ca-pub-XXXXXXXXXXXXXXXX
    | Slots: criados no painel AdSense (Anúncios → Por unidade de anúncio)
    |
    */

    'enabled' => (bool) env('ADSENSE_ENABLED', false),

    'client' => env('ADSENSE_CLIENT', ''),

    /*
    | Teste do Google (anúncios de exemplo). Use true só em desenvolvimento.
    | https://support.google.com/adsense/answer/9915669
    */
    'test_mode' => (bool) env('ADSENSE_TEST_MODE', false),

    'slots' => [
        'home' => env('ADSENSE_SLOT_HOME', ''),
        'hub' => env('ADSENSE_SLOT_HUB', ''),
        'footer' => env('ADSENSE_SLOT_FOOTER', ''),
    ],

];
