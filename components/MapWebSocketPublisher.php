<?php

namespace app\components;

use Yii;
use app\models\GraderiasSillas;
use app\models\Pagos;
use app\models\PagosEventuales;
use app\models\SitiosEventuales;

class MapWebSocketPublisher
{
    public static function publishGraderiaSilla($action, $gradId, $pagoId = null, array $extra = [])
    {
        $sitio = GraderiasSillas::findOne($gradId);
        if ($sitio === null) {
            return false;
        }

        $pago = $pagoId ? Pagos::findOne($pagoId) : null;
        $payload = self::basePayload('map.graderia_silla.' . $action, 'graderia_silla', $action);
        $payload['message'] = isset($extra['message']) ? $extra['message'] : self::graderiaSillaMessage($action, $sitio, $pago);
        $payload['sitio'] = self::graderiaSillaPayload($sitio);
        $payload['pago'] = $pago ? self::pagoPayload($pago) : null;
        $payload['map_update'] = [
            'resource' => 'graderia_silla',
            'id' => (int)$sitio->grad_id,
            'code' => $sitio->grad_codigo,
            'state' => self::graderiaSillaState($sitio),
        ];
        $payload['context'] = $extra;

        return self::publish($payload);
    }

    public static function publishSitioEventual($action, $sitiosId, $eventualId = null, array $extra = [])
    {
        if (!$sitiosId) {
            return false;
        }

        $sitio = SitiosEventuales::findOne($sitiosId);
        if ($sitio === null) {
            return false;
        }

        $pago = $eventualId ? PagosEventuales::findOne($eventualId) : null;
        $payload = self::basePayload('map.sitio_eventual.' . $action, 'sitio_eventual', $action);
        $payload['message'] = isset($extra['message']) ? $extra['message'] : self::sitioEventualMessage($action, $sitio, $pago);
        $payload['sitio'] = self::sitioEventualPayload($sitio);
        $payload['pago'] = $pago ? self::pagoEventualPayload($pago) : null;
        $payload['map_update'] = [
            'resource' => 'sitio_eventual',
            'id' => (int)$sitio->sitios_id,
            'code' => $sitio->sitios_codigo,
            'state' => self::sitioEventualState($sitio, $pago),
        ];
        $payload['context'] = $extra;

        return self::publish($payload);
    }

    private static function publish(array $payload)
    {
        $config = isset(Yii::$app->params['websocketNotifications']) ? Yii::$app->params['websocketNotifications'] : [];
        $enabled = self::env('WS_EVENTS_ENABLED', isset($config['enabled']) ? $config['enabled'] : true);
        if ($enabled === false || $enabled === '0' || $enabled === 0 || $enabled === 'false') {
            return false;
        }

        $url = self::env('WS_PUBLISH_URL', isset($config['publishUrl']) ? $config['publishUrl'] : 'http://127.0.0.1:8082/publish');
        $secret = self::env('WS_PUBLISH_SECRET', isset($config['secret']) ? $config['secret'] : '');
        $timeout = self::env('WS_PUBLISH_TIMEOUT', isset($config['timeout']) ? $config['timeout'] : 0.25);

        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $headers = "Content-Type: application/json\r\n";
        if ($secret !== '') {
            $headers .= "X-WS-SECRET: " . $secret . "\r\n";
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => $headers,
                'content' => $body,
                'timeout' => (float)$timeout,
                'ignore_errors' => true,
            ],
        ]);

        try {
            @file_get_contents($url, false, $context);
            return true;
        } catch (\Exception $e) {
            Yii::warning('No se pudo publicar evento WebSocket: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    private static function basePayload($event, $resource, $action)
    {
        return [
            'event_id' => uniqid('map_', true),
            'event' => $event,
            'resource' => $resource,
            'action' => $action,
            'occurred_at' => date('c'),
            'source' => 'urkudeudas25',
        ];
    }

    private static function graderiaSillaPayload(GraderiasSillas $model)
    {
        return [
            'grad_id' => (int)$model->grad_id,
            'zona_id' => $model->zona_id !== null ? (int)$model->zona_id : null,
            'gest_id' => $model->gest_id !== null ? (int)$model->gest_id : null,
            'grad_codigo' => $model->grad_codigo,
            'grad_codigo_catastral' => $model->grad_codigo_catastral,
            'grad_direccion' => $model->grad_direccion,
            'grad_tipo_sitio' => $model->grad_tipo_sitio,
            'grad_tipo_armado' => $model->grad_tipo_armado,
            'grad_resto' => $model->grad_resto,
            'grad_longitud' => $model->grad_longitud,
            'grad_longitud_disponible' => $model->grad_longitud,
            'grad_vendido' => (int)$model->grad_vendido,
            'grad_reservado' => isset($model->grad_reservado) ? (int)$model->grad_reservado : 0,
            'grad_estado' => (int)$model->grad_estado,
            'estado_mapa' => self::graderiaSillaState($model),
            'zona' => $model->zona ? [
                'zona_id' => (int)$model->zona->zona_id,
                'zona_nombre' => $model->zona->zona_nombre,
                'zona_color' => $model->zona->zona_color,
                'zona_color_hexadecimal' => $model->zona->zona_color_hexadecimal,
            ] : null,
        ];
    }

    private static function sitioEventualPayload(SitiosEventuales $model)
    {
        return [
            'sitios_id' => (int)$model->sitios_id,
            'sitios_codigo' => $model->sitios_codigo,
            'sitios_descripcion' => $model->sitios_descripcion,
            'sitios_numero_sitio' => $model->sitios_numero_sitio !== null ? (int)$model->sitios_numero_sitio : null,
            'sitios_vendido' => $model->sitios_vendido !== null ? (int)$model->sitios_vendido : null,
            'sitios_es_alasita' => (bool)$model->sitios_es_alasita,
            'sitios_estado' => (int)$model->sitios_estado,
            'estado_mapa' => self::sitioEventualState($model, null),
        ];
    }

    private static function pagoPayload(Pagos $model)
    {
        return [
            'pago_id' => (int)$model->pago_id,
            'pago_nro_liquidacion' => $model->pago_nro_liquidacion,
            'pago_tasa' => $model->pago_tasa,
            'pago_longitud_modificada' => $model->pago_longitud_modificada,
            'pago_importe_total' => $model->pago_importe_total,
            'pago_preliquidacion' => (int)$model->pago_preliquidacion,
            'pago_cobrado' => (int)$model->pago_cobrado,
            'pago_estado' => (int)$model->pago_estado,
            'pago_fecha_hora_preliquidacion' => $model->pago_fecha_hora_preliquidacion,
            'pago_fecha_hora_cobro' => $model->pago_fecha_hora_cobro,
            'pago_nro_comprobante' => $model->pago_nro_comprobante,
        ];
    }

    private static function pagoEventualPayload(PagosEventuales $model)
    {
        return [
            'eventual_id' => (int)$model->eventual_id,
            'eventual_nro_liquidacion' => $model->eventual_nro_liquidacion,
            'eventual_tasa' => $model->eventual_tasa,
            'eventual_importe_total' => $model->eventual_importe_total,
            'eventual_preliquidacion' => (int)$model->eventual_preliquidacion,
            'eventual_cobrado' => (int)$model->eventual_cobrado,
            'eventual_estado' => (int)$model->eventual_estado,
            'eventual_fecha_hora_liquidacion' => $model->eventual_fecha_hora_liquidacion,
            'eventual_fecha_hora_pago' => $model->eventual_fecha_hora_pago,
            'eventual_nro_comprobante' => $model->eventual_nro_comprobante,
        ];
    }

    private static function graderiaSillaMessage($action, GraderiasSillas $sitio, $pago)
    {
        $tipo = trim((string)$sitio->grad_tipo_sitio) !== '' ? strtolower($sitio->grad_tipo_sitio) : 'graderia/silla';
        $codigo = $sitio->grad_codigo;
        $disponible = (float)$sitio->grad_longitud;

        if ($action === 'preliquidated') {
            $vendida = (int)$sitio->grad_vendido === 1 || $disponible <= 0;
            return $vendida
                ? "El sitio {$codigo} para {$tipo} fue preliquidado y ya no tiene longitud disponible."
                : "El sitio {$codigo} para {$tipo} fue preliquidado. Longitud disponible: {$sitio->grad_longitud} m.";
        }
        if ($action === 'paid') {
            $nro = $pago ? $pago->pago_nro_liquidacion : '';
            return "La preliquidacion {$nro} del sitio {$codigo} fue pagada.";
        }
        if ($action === 'preliquidation_cancelled') {
            return "La preliquidacion del sitio {$codigo} fue anulada. Longitud disponible actual: {$sitio->grad_longitud} m.";
        }
        if ($action === 'reserved') {
            return "El sitio {$codigo} fue reservado.";
        }
        if ($action === 'reservation_released') {
            return "La reserva del sitio {$codigo} fue liberada.";
        }

        return "El sitio {$codigo} para {$tipo} fue actualizado.";
    }

    private static function sitioEventualMessage($action, SitiosEventuales $sitio, $pago)
    {
        $tipo = $sitio->sitios_es_alasita ? 'alasitas' : 'sitio eventual';
        $codigo = $sitio->sitios_codigo;
        $puesto = $sitio->sitios_numero_sitio;

        if ($action === 'preliquidated') {
            return "El {$tipo} {$codigo} puesto {$puesto} fue preliquidado.";
        }
        if ($action === 'paid') {
            $nro = $pago ? $pago->eventual_nro_liquidacion : '';
            return "La preliquidacion eventual {$nro} del sitio {$codigo} fue pagada.";
        }
        if ($action === 'preliquidation_cancelled') {
            return "La preliquidacion del {$tipo} {$codigo} puesto {$puesto} fue anulada.";
        }

        return "El {$tipo} {$codigo} puesto {$puesto} fue actualizado.";
    }

    private static function graderiaSillaState(GraderiasSillas $sitio)
    {
        if ((int)$sitio->grad_estado !== 1) {
            return 'inactive';
        }
        if ((int)$sitio->grad_vendido === 1 || (float)$sitio->grad_longitud <= 0) {
            return 'sold_out';
        }
        if (isset($sitio->grad_reservado) && (int)$sitio->grad_reservado === 1) {
            return 'reserved';
        }
        return 'available';
    }

    private static function sitioEventualState(SitiosEventuales $sitio, $pago)
    {
        if ((int)$sitio->sitios_estado !== 1) {
            return 'inactive';
        }
        if ($pago && (int)$pago->eventual_estado === 1 && (int)$pago->eventual_preliquidacion === 1) {
            return (int)$pago->eventual_cobrado === 1 ? 'paid' : 'preliquidated';
        }
        if ((int)$sitio->sitios_vendido === 1) {
            return 'sold';
        }
        return 'available';
    }

    private static function env($name, $default)
    {
        $value = getenv($name);
        return $value === false ? $default : $value;
    }
}
