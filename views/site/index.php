
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
<style>
body { 
    background-image: url("https://quillacollo.gob.bo/images/gallery-masonry-5-original.jpg");
    width: 100%;
    height: 100%;
    position: relative;
    background-position: center center; 
    background-size: cover;
    background-repeat: no-repeat;
    top: 0;
    left: 0;
    z-index: -100;
}
.panel {
    border: 1px solid;
    opacity: 0.5;
}
.p1 { box-shadow: 5px 10px 18px red; }
.p2 { box-shadow: 5px 10px 18px yellow; }
.p3 { box-shadow: 5px 10px 18px green; }
.panel-body {
  padding: 0 0 0 15px;
}
</style>

<div class="img-fluid">
<div class="container">
        <div class="panel panel-default p1" style="max-width: 24rem; height:16rem">
            <div class="panel-heading"> <strong>Graderias y Sillas</strong> </div>
            <?php  foreach ($graderias as $row ): ?>
            <div class="panel-body">
                <h5> Totales: <?=$row['totales'] ?> </h5>
                <h5> Reservados: <?=$row['reservados'] ?> </h5>
                <h5> Vendidos: <?=$row['vendidos'] ?> </h5>
                <h5> Disponibles: <?=$row['disponibles'] ?> </h5>
            </div>
            <?php endforeach ?>
        </div>

        <div class="panel panel-default p2" style="max-width: 24rem; height:14rem">
            <div class="panel-heading"><strong>Alasitas</strong> </div>
            <?php  foreach ($alasitas as $row ): ?>
            <div class="panel-body">
                <h5> Totales: <?=$row['totales'] ?> </h5>
                <h5> Vendidos: <?=$row['vendidos'] ?> </h5>
                <h5> Disponibles: <?=$row['disponibles'] ?> </h5>
                <!-- <h5>-</h5> -->
            </div>
            <?php endforeach ?>
        </div>
    
        <div class="panel panel-default p3" style="max-width: 24rem; height:14rem">
            <div class="panel-heading"> <strong>Eventuales</strong> </div>
            <?php  foreach ($eventuales as $row ): ?>
            <div class="panel-body">
                <h5> Totales: <?=$row['totales'] ?> </h5>
                <h5> Vendidos: <?=$row['vendidos'] ?> </h5>
                <h5> Disponibles: <?=$row['disponibles'] ?> </h5>
                <!-- <h5>-</h5> -->
            </div>
            <?php endforeach ?>
        </div>

</div>
</div>



<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"",// always need it for jquery plugin
])?>
<?php Modal::end(); ?>



