<?php

return [
    'adminEmail' => 'siscorso@gmail.com',
    'costos' => [
        'comprobante' => 13,
    ],
    'ruat' => [
        'environment' => getenv('RUAT_ENVIRONMENT') ?: 'production',
        'baseUrls' => [
            /** Testing */
            'testingConsolidacion' => 'https://consolidacionjboss.ruat.gob.bo',
            'testingVerificacion' => 'https://verificacionjboss.ruat.gob.bo',

            /** Production */
            'production' => 'https://aplicaciones.ruat.gob.bo',
        ],
        'usuario' => getenv('RUAT_USUARIO') ?: 'SWTRAMITESURKUPINIAQUI',
        'clave' => getenv('RUAT_CLAVE') ?: 'Gam#1209',
    ],
    'websocketNotifications' => [
        'enabled' => getenv('WS_EVENTS_ENABLED') === false ? true : getenv('WS_EVENTS_ENABLED'),
        'publishUrl' => getenv('WS_PUBLISH_URL') ?: 'http://127.0.0.1:8082/publish',
        'clientUrl' => getenv('WS_CLIENT_URL') ?: 'ws://localhost:8082/ws-notificaciones',
        'secret' => getenv('WS_PUBLISH_SECRET') ?: '',
        'timeout' => getenv('WS_PUBLISH_TIMEOUT') ?: 0.25,
    ],
];
