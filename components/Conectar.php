<?php
namespace app\components;

//require 'C:\laragon\www\proyecto-urkupina\web\phpqrcode\qrlib.php';
//include "C:\laragon\www\proyecto-urkupina\components\src\Qrcodetest.php";
use Yii;
use yii\base\Component;
//use yii\httpclient\Client;
//use yii\base\BaseObject;
use yii\helpers\VarDumper;
use yii\helpers\Json;

class Conectar extends Component
{
    private $apiUrl = 'https://aplicaciones.ruat.gob.bo/ServiciosRuatJEE-web/api/autentificacion';

    public function getToken() {
        $cabecera= ['Usuario: SWTASASQUILLACOLLO',
                    'Clave: S1234567'];

        //$data=array('usuario'=>'erodriguez', 'pasword'=>'12345678');
        $data=null;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        //curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER,($cabecera));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $respuesta = curl_exec($ch);
        

        if (curl_errno($ch)) {
            return 'no token';
           // VarDumper::dump($respuesta);
            
        }
        else
        {
            $resp=json_decode($respuesta,true);
        
            //VarDumper::dump('entro!! ');
            
            return $resp;
        }
        
        curl_close($ch);
    }
    /*public function gettoken()
    {
        //$BASE_URL = 'https://consolidacionjboss.ruat.gob.bo/ServiciosRuatJEE-web/api/autentificacion';
        $BASE_URL ='http://181.177.143.185:8080/api/auth/signin';
        //$_token = null;
        $client =  new Client();
        //$client=Yii::$app->client;
        $response = $client->createRequest()
        ->setMethod('POST')
        ->setUrl($BASE_URL)
        ->setData(['username' => 'erodriguez', 'password' => '12345678'])
        ->addHeaders([
            'Usuario' => 'SWTASASQUILLACOLLO',
            'Clave' => '1234567',
        ])
         ->send();
        if ($response->isOk) {
            //$this->_token = $response->data['token'];
            //Yii::app()->clientScript->registerScript(1, 'alert("entro!!")');
            VarDumper::dump('Ingreso al EndPoint');

         }
         else
         { 
           VarDumper::dump('No entra al EndPoint');
          }

    }*/
}
?>