
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
    height: auto;
}
.panel {
    border: 1px solid;
    /* //padding: 10px; */
    box-shadow: 5px 10px 18px red;
}
</style>

<div class="img-fluid cards">
<div class="container">
    <!-- <div class="col-md-4"> -->
        <div class="panel panel-default" style="max-width: 20rem;">
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
    <!-- </div> -->
     
    <!-- <div class="col-md-4"> -->    
        <div class="panel panel-default" style="max-width: 20rem;">
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
    <!-- </div> -->

    <!-- <div class="col-md-4"> -->    
        <div class="panel panel-default" style="max-width: 20rem;">
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
    <!-- </div> -->

</div>
</div>



<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"",// always need it for jquery plugin
])?>
<?php Modal::end(); ?>



