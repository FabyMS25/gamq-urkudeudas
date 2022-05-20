<?php

use yii\helpers\Html;
use yii\bootstrap\Modal;
use johnitvn\ajaxcrud\CrudAsset; 


$this->title = "";

CrudAsset::register($this);
?>
<div class="text-center rpt-margin">
    <h3 class="text-normal"><?= $this->title ?></h3>
    


    <object data="<?= $url ?>" type="application/pdf" name="Imprimir declaración" height="700px" width="100%;">
        <div>
            No se puede abrir el Reporte, haga click en el enlace para descargar <a href="<?= $url ?>">Descargar</a>
        </div>
    </object>
</div>
<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"",// always need it for jquery plugin
])?>
<?php Modal::end(); ?>
