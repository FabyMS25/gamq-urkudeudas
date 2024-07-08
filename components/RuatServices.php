<?php

namespace app\components;

use DateTime;
use Yii;
use yii\base\Component;
use yii\helpers\VarDumper;
use yii\httpclient\Client;


class RuatServices extends Component
{
    public $baseUrl;

    public function init()
    {
        /**Testing */
        $this->baseUrl = 'https://verificacionjboss.ruat.gob.bo';
        /**Production */
        /*$this->baseUrl = 'https://aplicaciones.ruat.gob.bo';*/
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

    public function getContribuyentePorCi($token, $ci, $tipoDocumento)
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
                'tipoDocumento' => $tipoDocumento,
                'expedido' => ''
            ]);

        $response = $request->send();
        if ($response->isOk) {
            $data = json_decode($response->content);
            return $data->codigoContribuyente;
        } else {
            return null;
        }
    }

    public function registerContribuyente($token, $codigoUsuario, $contribuyente)
    {
        $client = new Client();
        $request = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($this->baseUrl . '/RuatServiciosWebContribuyentes/contribuyentes/registroContribuyente')
            ->setHeaders([
                'Authorization' => "Bearer $token"
            ])
            ->setData([
                'codigoAlcaldia' => 'QUI',
                'codigoUsuario' => $codigoUsuario,
                'numeroDocumento' => $contribuyente->contri_ci,
                'tipoDocumento' => $contribuyente->ext_id == 12 ? 'CE' : 'CI',
                'expedido' => $contribuyente->ext_id == 12 ? '' : $this->getExpedidoById($contribuyente->ext_id),
                'nombre' => $contribuyente->contri_nombres,
                'primerApellido' => $contribuyente->contri_paterno ? $contribuyente->contri_paterno : 'NO LLEVA',
                'segundoApellido' => $contribuyente->contri_materno ? $contribuyente->contri_materno : 'NO LLEVA',
                'estadoCivil' =>  $contribuyente->contri_estadocivil ? $contribuyente->contri_estadocivil : 'SO',
                'fechaNacimiento' => $contribuyente->contri_fechanac ? Yii::$app->formatter->asDate($contribuyente->contri_fechanac, 'php:d/m/Y') : '01/01/1990',
                'genero' => $contribuyente->contri_sexo ? $contribuyente->contri_sexo : 'M',
                'apellidoEsposo' => $contribuyente->contri_apellidocasada,
                'motivo' => 'REGISTRO TASAS Y OTROS INGRESOS',
                'observacion' => 'REGISTRO URKUPINA 2024'
            ]);
        $response = $request->send();
        if ($response->isOk) {
            $data = json_decode($response->content);
            return $data->codigoContribuyente;
        } else {
            return null;
        }
    }

    public function getTieneDeudaContribuyente($token, $ciContribuyente)
    {
        return false;
    }

    public function getTieneDeudaContribuyentePorNroDocumento($token, $tipoConsulta, $nroDocumento, $tipoDocumento)
    {
        $client = new Client();
        $request = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($this->baseUrl . '/RuatServiciosWebTasasOI/tasasOI/consultaDeudaTasaContrib')
            ->setHeaders([
                'Authorization' => "Bearer $token"
            ])
            ->setData([
                'codigoAlcaldia' => 'QUI',
                'tipoConsulta' => $tipoConsulta,
                'numeroDocumento' => $nroDocumento,
                'tipoDocumento' => $tipoDocumento,
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
            // return $data->numeroTasa;
            return  $data;
        } else {
            $data = json_decode($response->content);
            // return null;
            return $data;
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
            return $data;
        } else {
            $data = json_decode($response->content);
            // return $data->continuarFlujo;
            return $data;
        }
    }

    public function buscarPagadoPorNroTasa($token, $nroTasa)
    {
        $client = new Client();
        $request = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($this->baseUrl . '/RuatServiciosWebTasasOI/tasasOI/consultaPagoTasa')
            ->setHeaders([
                'Authorization' => "Bearer $token"
            ])
            ->setData([
                "codigoAlcaldia" => "QUI",
                "numeroTasa" => $nroTasa,
                "tipoTasa" => 'TO',
            ]);

        $response = $request->send();
        if ($response->isOk) {
            $data = json_decode($response->content);
            return $data->continuarFlujo;
        } else {
            $data = json_decode($response->content);
            return $data->continuarFlujo;
        }
    }

    public function buscarPagadoPorNroTasas($token, $nroTasa)
    {
        $client = new Client();
        $request = $client->createRequest()
            ->setMethod('POST')
            ->setFormat(Client::FORMAT_JSON)
            ->setUrl($this->baseUrl . '/RuatServiciosWebTasasOI/tasasOI/consultaPagoTasa')
            ->setHeaders([
                'Authorization' => "Bearer $token"
            ])
            ->setData([
                "codigoAlcaldia" => "QUI",
                "numeroTasa" => $nroTasa,
                "tipoTasa" => 'TO',
            ]);

        $response = $request->send();
        if ($response->isOk) {
            $data = json_decode($response->content);
            return $data->pagoTasa;
        } else {
            $data = json_decode($response->content);
            return $data->pagoTasa;
        }
    }

    /**Utils */
    private function getExpedidoById($id)
    {
        $res = 0;
        switch ($id) {
            case 4:
                $res = 1;
                break;
            case 5:
                $res = 2;
                break;
            case 3:
                $res = 3;
                break;
            case 6:
                $res = 4;
                break;
            case 8:
                $res = 5;
                break;
            case 11:
                $res = 6;
                break;
            case 7:
                $res = 7;
                break;
            case 9:
                $res = 8;
                break;
            case 10:
                $res = 9;
                break;
            case 12:
                $res = 12;
                break;
            default:
                $res = 3;
                break;
        }
        return $res;
    }
}
