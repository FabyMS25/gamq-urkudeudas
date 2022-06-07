<?php
namespace app\components;

//require 'C:\laragon\www\proyecto-urkupina\web\phpqrcode\qrlib.php';
include "C:\laragon\www\proyecto-urkupina\components\src\Qrcodetest.php";
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class Incrustar extends Component{
/*public  function Generar($contenido, $file){
    $dir = 'temp/';
    if(!file_exists($dir))
    mkdir($dir);
    //$filename = $dir.'test.png';
    $tamanio = 7;
    $level = 'H';
    $frameSize = 1;
    //$contenido = 'http://www.codigosdeprogramacion.com';


    
    //QRcode::png($contenido, $filename, $level, $tamanio, $frameSize);
return 1;
}*/
public function generador($contenido, $file){
    
    $qc = new Qrcodetest();
    // Create Text Code
    $qc->TEXT($contenido);
    // Save QR Code
    $qc->QRCODE(400,$file);


}
}
?>