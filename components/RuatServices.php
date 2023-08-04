<?php

namespace app\components;

use yii\base\Component;
use yii\httpclient\Client;


class RuatServices extends Component
{
    public $baseUrl;

    public function init()
    {
        $this->baseUrl = 'https://consolidacionjboss.ruat.gob.bo';
    }

    public function login($username, $password)
    {
        $client = new Client();
        $request = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($this->baseUrl . '/ServiciosRuatJEE-web/api/autentificacion')
            ->setHeaders(['content-type' => 'application/json'])
            ->addHeaders(['usuario' => $username])
            ->addHeaders(['clave' => $password]);

        $response = $request->send();
        if ($response->isOk) {
            $data = json_decode($response->content);
            return $data->token;
        } else {
            return null;
        }
    }

    public function getContribuyentePorCi($token, $ci)
    {
        $client = new Client();
        $request = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($this->baseUrl . '/RuatServiciosWebContribuyentes/contribuyentes/comun/busquedaContribuyente')
            ->setHeaders([
                'Authorization' => "Bearer $token"
            ])
            ->setData([
                'codigoAlcaldia' => 'QUI',
                'numeroDocumento' => $ci,
                'tipoDocumento' => 'CI',
            ]);

        $response = $request->send();
        if ($response->isOk) {
            $data = json_decode($response->content);
            return $data->codigoContribuyente;
        } else {
            return null;
        }
    }

    public function getTieneDeudaContribuyente($token, $ci)
    {
        return false;
    }

    public function createTasa($token, $codigoUsuario, $codigoContribuyente, $codigoClasificador, $monto, $obsercaciones)
    {
        $client = new Client();
        $request = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($this->baseUrl . '/RuatServiciosWebTasasOI/tasasOI/registroTasa')
            ->setHeaders([
                'Authorization' => "Bearer $token"
            ])
            ->setData([
                "codigoAlcaldia" => "QUI",
                "codigoUsuario" => $codigoUsuario,
                "codigoContribuyente" => $codigoContribuyente,
                "servicioMunicipal" => "2174",
                "datosConcepto" => [
                    [
                        "codigoClasificador" => $codigoClasificador,
                        "tipoArancel" => "DI",
                        "monto" => $monto
                    ]
                ],
                "observacion" => $obsercaciones
            ]);

        $response = $request->send();
        if ($response->isOk) {
            $data = json_decode($response->content);
            return $data->numeroTasa;
        } else {
            $data = json_decode($response->content);
            return null;
        }
    }

    public function anularTasa($token, $codigoUsuario, $nroTasa, $motivo, $obsercaciones)
    {
        $client = new Client();
        $request = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($this->baseUrl . '/RuatServiciosWebTasasOI/tasasOI/anulacionTasa')
            ->setHeaders([
                'Authorization' => "Bearer $token"
            ])
            ->setData([
                "codigoAlcaldia" => "QUI",
                "codigoUsuario" => $codigoUsuario,
                "numeroTasa" => $nroTasa,
                "tipoTasa" => 'TO',
                "motivoTasa" => $motivo,
                "observacion" => $obsercaciones
            ]);

        $response = $request->send();
        if ($response->isOk) {
            $data = json_decode($response->content);
            return $data->continuarFlujo;
        } else {
            $data = json_decode($response->content);
            return $data->continuarFlujo;;
        }
    }
}
