<?php

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
    'clasificadores' => [
        'graderias_sillas' => '31450',
        'actividades_economicas_eventuales' => '31451',
        'espectaculos_publicos' => '31452',
        'publicidad_propaganda' => '31453',
        'sentajes_urkupina' => '31454', 
        'mingitorios' => '31455',
        'tasa_aseo' => '31456',
        'alasitas' => '31457', 
        'sentajes_alasitas' => '31458',

        'multas_infracciones' => '31459',
        'otros' => '31460',
    ],
    'websocketNotifications' => [
        'enabled' => getenv('WS_EVENTS_ENABLED') === false ? true : getenv('WS_EVENTS_ENABLED'),
        'publishUrl' => getenv('WS_PUBLISH_URL') ?: 'http://127.0.0.1:8082/publish',
//         'clientUrl' => getenv('WS_CLIENT_URL') ?: 'ws://localhost:8082/ws-notificaciones',
        'clientUrl' => getenv('WS_CLIENT_URL') ?: 'wss://urkupina.quillacollo.gob.bo/ws-notificaciones',
        'secret' => getenv('WS_PUBLISH_SECRET') ?: '',
        'timeout' => getenv('WS_PUBLISH_TIMEOUT') ?: 0.25,
    ],
];
