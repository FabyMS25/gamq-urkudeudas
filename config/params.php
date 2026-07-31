<?php

$clasificadoresCatalogo = [
    'graderias_sillas' => [
        'codigo' => '31450',
        'label' => 'Graderías y Sillas',
        'observacion' => 'REGISTRO DE GRADERÍAS Y SILLAS 2026',
        'grupo' => 'graderias',
    ],

    'actividades_economicas_eventuales' => [
        'codigo' => '31451',
        'label' => 'Actividades Económicas Eventuales',
        'observacion' => 'REGISTRO DE ACTIVIDADES ECONÓMICAS EVENTUALES 2026',
        'grupo' => 'eventuales',
    ],

    'espectaculos_publicos' => [
        'codigo' => '31452',
        'label' => 'Espectáculos Públicos',
        'observacion' => 'REGISTRO DE ESPECTÁCULOS PÚBLICOS 2026',
        'grupo' => 'general',
    ],

    'publicidad_propaganda' => [
        'codigo' => '31453',
        'label' => 'Publicidad y Propaganda',
        'observacion' => 'REGISTRO DE PUBLICIDAD Y PROPAGANDA 2026',
        'grupo' => 'general',
    ],

    'sentajes_urkupina' => [
        'codigo' => '31454',
        'label' => 'Sentajes Urkupiña',
        'observacion' => 'REGISTRO DE SENTAJES URKUPIÑA 2026',
        'grupo' => 'sentajes',
    ],

    'mingitorios' => [
        'codigo' => '31455',
        'label' => 'Mingitorios',
        'observacion' => 'REGISTRO DE MINGITORIOS 2026',
        'grupo' => 'sentajes',
    ],

    'tasa_aseo' => [
        'codigo' => '31456',
        'label' => 'Tasa de Aseo',
        'observacion' => 'REGISTRO DE TASA DE ASEO 2026',
        'grupo' => 'general',
    ],

    'alasitas' => [
        'codigo' => '31457',
        'label' => 'Alasitas',
        'observacion' => 'REGISTRO DE ALASITAS 2026',
        'grupo' => 'eventuales',
    ],

    'sentajes_alasitas' => [
        'codigo' => '31458',
        'label' => 'Sentajes Alasitas',
        'observacion' => 'REGISTRO DE SENTAJES ALASITAS 2026',
        'grupo' => 'sentajes',
    ],

    'multas_infracciones' => [
        'codigo' => '31459',
        'label' => 'Multas e Infracciones',
        'observacion' => 'REGISTRO DE MULTAS E INFRACCIONES 2026',
        'grupo' => 'infracciones',
    ],

    'otros' => [
        'codigo' => '31460',
        'label' => 'Otros',
        'observacion' => 'REGISTRO DE OTROS INGRESOS 2026',
        'grupo' => 'sentajes',
    ],
];

return [
    'adminEmail' => 'siscorso@gmail.com',
    'costos' => [
        'comprobante' => 13,
    ],
    'eventPeriods' => [
        'alasitas' => [
            'start' => '2026-08-17',
            'end' => '2026-08-31',
        ],
        'urkupina' => [
            'start' => '2026-08-14',
            'end' => '2026-08-16',
        ],
    ],

    'ruat' => [
        'environment' => getenv('RUAT_ENVIRONMENT') ?: 'production',
        'baseUrls' => [
            'testing' => 'https://consolidacionjboss.ruat.gob.bo',
            'production' => 'https://aplicaciones.ruat.gob.bo',
        ],
        'usuario' => getenv('RUAT_USUARIO') ?: 'SWTRAMITESURKUPINIAQUI',
        'clave' => getenv('RUAT_CLAVE') ?: 'Gam#1209',
        'servicioMunicipal' => '2174',
    ],
    'clasificadoresCatalogo' => $clasificadoresCatalogo,
    'clasificadores' => array_map(function ($config){return $config['codigo'];}, $clasificadoresCatalogo),

    'websocketNotifications' => [
        'enabled' => getenv('WS_EVENTS_ENABLED') === false ? true : getenv('WS_EVENTS_ENABLED'),
        'publishUrl' => getenv('WS_PUBLISH_URL') ?: 'http://127.0.0.1:8082/publish',
//         'clientUrl' => getenv('WS_CLIENT_URL') ?: 'ws://localhost:8082/ws-notificaciones',
        'clientUrl' => getenv('WS_CLIENT_URL') ?: 'wss://urkupina.quillacollo.gob.bo/ws-notificaciones',
        'secret' => getenv('WS_PUBLISH_SECRET') ?: '',
        'timeout' => getenv('WS_PUBLISH_TIMEOUT') ?: 0.25,
    ],
];
