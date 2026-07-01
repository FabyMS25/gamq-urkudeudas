<?php

namespace app\components;

use DateTime;
use yii\base\Component;
use yii\httpclient\Client;

class RuatServices extends Component
{
    public $baseUrl;

    public function init()
    {
        /** Testing */
        // $this->baseUrl = 'https://consolidacionjboss.ruat.gob.bo';
        // $this->baseUrl = 'https://verificacionjboss.ruat.gob.bo';

        /** Production */
        $this->baseUrl = 'https://aplicaciones.ruat.gob.bo';
    }

    public function login($username, $password)
    {
        $client = new Client();

        $request = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($this->baseUrl . '/ServiciosRuatJEE-web/api/autentificacion')
            ->setHeaders([
                'content-type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->addHeaders(['usuario' => $username])
            ->addHeaders(['clave' => $password]);

        $response = $request->send();

        if (!$response->isOk) {
            return null;
        }

        $data = json_decode($response->content);

        return $data && isset($data->token) ? $data->token : null;
    }

    public function getContribuyentePorCi($token, $ci, $tipoDocumento, $codigoAlcaldia = 'QUI', $expedido = '')
    {
        $data = $this->getContribuyentePorCiResponse($token, $ci, $tipoDocumento, $codigoAlcaldia, $expedido);

        return $data && isset($data->codigoContribuyente) ? $data->codigoContribuyente : null;
    }

    public function getContribuyentePorCiResponse($token, $ci, $tipoDocumento, $codigoAlcaldia = 'QUI', $expedido = '')
    {
        $client = new Client();

        $request = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($this->baseUrl . '/RuatServiciosWebContribuyentes/contribuyentes/comun/busquedaContribuyente')
            ->setHeaders([
                'Authorization' => "Bearer $token",
                'Accept' => 'application/json',
            ])
            ->setData([
                'codigoAlcaldia' => $codigoAlcaldia,
                'numeroDocumento' => $ci,
                'tipoDocumento' => $tipoDocumento,
                'expedido' => $tipoDocumento === 'CE' ? '' : $expedido,
            ]);

        $response = $request->send();
        $data = json_decode($response->content);

        if ($data !== null) {
            return $data;
        }

        return (object)[
            'continuarFlujo' => false,
            'mensaje' => 'RUAT no devolvió una respuesta JSON válida.',
            'httpStatus' => $response->statusCode,
            'raw' => $response->content,
        ];
    }

    public function registerContribuyente(
        $token,
        $codigoUsuario,
        $contribuyente,
        $codigoAlcaldia = 'QUI',
        $motivo = 'REGISTRO TASAS Y OTROS INGRESOS',
        $observacion = 'REGISTRO URKUPINA 2025',
        $expedido = null
    )
    {
        $response = $this->registerContribuyenteResponse(
            $token,
            $codigoUsuario,
            $contribuyente,
            $codigoAlcaldia,
            $motivo,
            $observacion,
            $expedido
        );

        return $response && isset($response->codigoContribuyente)
            ? $response->codigoContribuyente
            : null;
    }

    public function registerContribuyenteResponse(
        $token,
        $codigoUsuario,
        $contribuyente,
        $codigoAlcaldia = 'QUI',
        $motivo = 'REGISTRO TASAS Y OTROS INGRESOS',
        $observacion = 'REGISTRO URKUPINA 2025',
        $expedido = null
    )
    {
        $tipoDocumento = $contribuyente->contri_tipo_documento_ruat;
        $expedido = $tipoDocumento === 'CE'
            ? ''
            : ($expedido === null ? $this->getExpedidoById($contribuyente->ext_id) : $expedido);

        $client = new Client();

        $request = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($this->baseUrl . '/RuatServiciosWebContribuyentes/contribuyentes/registroContribuyente')
            ->setHeaders([
                'Authorization' => "Bearer $token",
                'Accept' => 'application/json',
            ])
            ->setData([
                'codigoAlcaldia' => $codigoAlcaldia,
                'codigoUsuario' => $codigoUsuario,
                'numeroDocumento' => $contribuyente->contri_ci,
                'tipoDocumento' => $tipoDocumento,
                'expedido' => $expedido,
                'nombre' => $contribuyente->contri_nombres,
                'primerApellido' => $contribuyente->contri_paterno ? $contribuyente->contri_paterno : 'NO LLEVA',
                'segundoApellido' => $contribuyente->contri_materno ? $contribuyente->contri_materno : 'NO LLEVA',
                'estadoCivil' => $contribuyente->contri_estadocivil,
                'fechaNacimiento' => $this->formatRuatDate($contribuyente->contri_fechanac),
                'genero' => $contribuyente->contri_sexo,
                'apellidoEsposo' => $contribuyente->contri_apellidocasada,
                'motivo' => $motivo,
                'observacion' => $observacion,
            ]);

        $response = $request->send();
        $data = json_decode($response->content);

        if ($data !== null) {
            return $data;
        }

        return (object)[
            'continuarFlujo' => false,
            'mensaje' => 'RUAT no devolvió una respuesta JSON válida.',
            'httpStatus' => $response->statusCode,
            'raw' => $response->content,
        ];
    }

    public function getTieneDeudaContribuyentePorNroDocumento(
        $token,
        $contribuyente,
        $codigoAlcaldia = 'QUI',
        $tipoConsulta = 1,
        $expedido = ''
    ) {
        $tipoDocumento = $contribuyente->contri_tipo_documento_ruat;

        $client = new Client();

        $request = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($this->baseUrl . '/RuatServiciosWebTasasOI/tasasOI/consultaDeudaTasaContrib')
            ->setHeaders([
                'Authorization' => "Bearer $token",
                'Accept' => 'application/json',
            ])
            ->setData([
                'codigoAlcaldia' => $codigoAlcaldia,
                'tipoConsulta' => $tipoConsulta,
                'numeroDocumento' => $contribuyente->contri_ci,
                'tipoDocumento' => $tipoDocumento,
                'expedido' => $tipoDocumento === 'CE' ? '' : $expedido,
            ]);

        $response = $request->send();
        $data = json_decode($response->content);

        if ($data !== null) {
            return $data;
        }

        return (object)[
            'continuarFlujo' => false,
            'mensaje' => 'RUAT no devolvió una respuesta JSON válida.',
            'httpStatus' => $response->statusCode,
            'raw' => $response->content,
        ];
    }

    public function createTasa(
        $token,
        $codigoUsuario,
        $codigoContribuyente,
        $codigoClasificador,
        $monto,
        $observaciones,
        $codigoAlcaldia = 'QUI',
        $servicioMunicipal = '2174',
        $tipoArancel = 'DI',
        $incluirNumeroConcepto = false
    ) {
        $client = new Client();
        $concepto = [
            'codigoClasificador' => $codigoClasificador,
            'tipoArancel' => $tipoArancel,
            'monto' => $this->formatRuatMonto($monto),
        ];

        if ($incluirNumeroConcepto) {
            $concepto = array_merge(['numero' => '1'], $concepto);
        }

        $request = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($this->baseUrl . '/RuatServiciosWebTasasOI/tasasOI/registroTasa')
            ->setHeaders([
                'Authorization' => "Bearer $token",
                'Accept' => 'application/json',
            ])
            ->setData([
                'codigoAlcaldia' => $codigoAlcaldia,
                'codigoUsuario' => $codigoUsuario,
                'codigoContribuyente' => $codigoContribuyente,
                'servicioMunicipal' => $servicioMunicipal,
                'datosConcepto' => [
                    $concepto,
                ],
                'observacion' => $observaciones,
            ]);

        $response = $request->send();
        $data = json_decode($response->content);

        if ($data !== null) {
            return $data;
        }

        return (object)[
            'continuarFlujo' => false,
            'mensaje' => 'RUAT no devolvió una respuesta JSON válida.',
            'httpStatus' => $response->statusCode,
            'raw' => $response->content,
        ];
    }

    public function anularTasa(
        $token,
        $codigoUsuario,
        $nroTasa,
        $motivo,
        $observaciones,
        $codigoAlcaldia = 'QUI',
        $tipoTasa = 'TO'
    ) {
        $client = new Client();

        $request = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($this->baseUrl . '/RuatServiciosWebTasasOI/tasasOI/anulacionTasa')
            ->setHeaders([
                'Authorization' => "Bearer $token",
                'Accept' => 'application/json',
            ])
            ->setData([
                'codigoAlcaldia' => $codigoAlcaldia,
                'codigoUsuario' => $codigoUsuario,
                'numeroTasa' => $nroTasa,
                'tipoTasa' => $tipoTasa,
                'motivoTasa' => $motivo,
                'observacion' => $observaciones,
            ]);

        $response = $request->send();
        $data = json_decode($response->content);

        if ($data !== null) {
            return $data;
        }

        return (object)[
            'continuarFlujo' => false,
            'mensaje' => 'RUAT no devolvió una respuesta JSON válida.',
            'httpStatus' => $response->statusCode,
            'raw' => $response->content,
        ];
    }

    public function consultaPagoTasa($token, $nroTasa, $codigoAlcaldia = 'QUI', $tipoTasa = 'TO')
    {
        $client = new Client();

        $request = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($this->baseUrl . '/RuatServiciosWebTasasOI/tasasOI/consultaPagoTasa')
            ->setHeaders([
                'Authorization' => "Bearer $token",
                'Accept' => 'application/json',
            ])
            ->setData([
                'codigoAlcaldia' => $codigoAlcaldia,
                'numeroTasa' => $nroTasa,
                'tipoTasa' => $tipoTasa,
            ]);

        $response = $request->send();
        $data = json_decode($response->content);

        if ($data !== null) {
            return $data;
        }

        return (object)[
            'continuarFlujo' => false,
            'mensaje' => 'RUAT no devolvió una respuesta JSON válida.',
            'httpStatus' => $response->statusCode,
            'raw' => $response->content,
        ];
    }

    public function buscarPagadoPorNroTasa($token, $nroTasa, $codigoAlcaldia = 'QUI', $tipoTasa = 'TO')
    {
        $data = $this->consultaPagoTasa($token, $nroTasa, $codigoAlcaldia, $tipoTasa);

        return isset($data->continuarFlujo) ? (bool)$data->continuarFlujo : false;
    }

    public function buscarPagadoPorNroTasas($token, $nroTasa, $codigoAlcaldia = 'QUI', $tipoTasa = 'TO')
    {
        $data = $this->consultaPagoTasa($token, $nroTasa, $codigoAlcaldia, $tipoTasa);

        return isset($data->pagoTasa) ? $data->pagoTasa : null;
    }

    /** Utils */

    private function getExpedidoById($id)
    {
        $map = [
            4 => 1,
            5 => 2,
            3 => 3,
            6 => 4,
            8 => 5,
            11 => 6,
            7 => 7,
            9 => 8,
            10 => 9,
            12 => 12,
        ];

        return isset($map[(int)$id]) ? $map[(int)$id] : 3;
    }

    private function formatRuatMonto($monto)
    {
        $value = trim((string)$monto);

        if ($value === '') {
            return null;
        }

        $normalized = $this->normalizeDecimalString($value);

        if (!is_numeric($normalized)) {
            return $monto;
        }

        return round((float)$normalized, 2);
    }

    private function normalizeDecimalString($value)
    {
        $value = str_replace(' ', '', trim((string)$value));
        $hasComma = strpos($value, ',') !== false;
        $hasDot = strpos($value, '.') !== false;

        if ($hasComma && $hasDot) {
            if (strrpos($value, ',') > strrpos($value, '.')) {
                return str_replace(',', '.', str_replace('.', '', $value));
            }

            return str_replace(',', '', $value);
        }

        if ($hasComma) {
            return str_replace(',', '.', $value);
        }

        return $value;
    }

    private function formatRuatDate($value): string
    {
        $value = trim((string)$value);

        if ($value === '') {
            return '01/01/1990';
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            return $value;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $date = DateTime::createFromFormat('Y-m-d', $value);

            return $date ? $date->format('d/m/Y') : '01/01/1990';
        }

        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
            $date = DateTime::createFromFormat('d-m-Y', $value);

            return $date ? $date->format('d/m/Y') : '01/01/1990';
        }

        if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $value)) {
            $date = DateTime::createFromFormat('Y/m/d', $value);

            return $date ? $date->format('d/m/Y') : '01/01/1990';
        }

        return '01/01/1990';
    }
}
