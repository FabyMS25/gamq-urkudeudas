
<?php
use yii\bootstrap\Modal;
use johnitvn\ajaxcrud\CrudAsset; 
use yii\helpers\Html;
use app\models\Sitios;
use app\models\Tpsitios;

/* @var $this yii\web\View */
CrudAsset::register($this);
$this->title = 'sisUrku18';

?>
<div class="site-index">

    <div class="jumbotron">
                  <br>
                  <br>
                  <br>
                  <br>
                  <br>
                  <br>
         <?php //echo Html::img('@web/img/sisim608big.png',['width'=>'400']) ?>
    </div>


</div>
<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"",// always need it for jquery plugin
])?>
<?php Modal::end(); ?>



