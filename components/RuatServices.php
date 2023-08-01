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
            return 'no token';
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
                'expedido' => '',
            ]);

        $response = $request->send();
        if ($response->isOk) {
            $data = json_decode($response->content);
            return $data;
        } else {
            $data = json_decode($response->content);
            return $data;
        }
    }

    public function getTieneDeudaContribuyente($token, $ci)
    {
        return false;
        /*
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
                'expedido' => '',
            ]);

        $response = $request->send();
        if ($response->isOk) {
            $data = json_decode($response->content);
            return $data;
        } else {
            $data = json_decode($response->content);
            return $data;
        }*/
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
                        "numero" => "1",
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
            return $data;
        } else {
            $data = json_decode($response->content);
            return $data;
        }
    }
}
