
<?php
use yii\bootstrap\Modal;
use johnitvn\ajaxcrud\CrudAsset; 
use yii\helpers\Html;
use app\models\Sitios;
use app\models\Tpsitios;
use miloschuman\highcharts\Highcharts;

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
    /*border: 1px solid; opacity: 0.5; */'
    border: none;
}
.p1 { box-shadow: 5px 10px 18px #FF0000; }
.p2 { box-shadow: 5px 10px 18px #FFFF00; }
.p3 { box-shadow: 5px 10px 18px #274E13; }
.padding {
  padding: 5px 5px 0 5px;
  border-radius: 10%;
  box-shadow: 5px 10px 18px gray;
}
.amarillo { background: #FFFF00;  }
.verde { background: #274E13; color: white }
.rojo { background: #FF0000 }
.azul { background: #0000FF }
.naranja { background: #FF9900 }
.celeste { background: #3D85C6 }
</style>

<div class="img-fluid">
<div class="container">
        <div class="panel panel-default p1" style="max-width: 53rem; height:30rem">
            <div class="panel-heading"> <strong>Graderias y Sillas</strong> </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4">
                    <?php  foreach ($graderias as $row ): ?>
                    <div class="amarillo padding">
                        <strong><?=$row['zona'] ?> </strong><br>
                        <p>
                            TOTALES: <?=$row['totales'] ?> <br>
                            RESERVADOS: <?=$row['reservados'] ?> <br>
                            VENDIDOS: <?=$row['vendidos'] ?>  <br>
                            DISPONIBLES: <?=$row['disponibles'] ?>
                    </p>
                    </div>
                    <?php endforeach ?>
                    </div>

                    <div class="col-md-4">
                    <?php  foreach ($graderiasz2 as $row ): ?>
                    <div class="verde padding">
                        <strong><?=$row['zona'] ?> </strong><br>
                        <p>
                            TOTALES: <?=$row['totales'] ?> <br>
                            RESERVADOS: <?=$row['reservados'] ?> <br>
                            VENDIDOS: <?=$row['vendidos'] ?>  <br>
                            DISPONIBLES: <?=$row['disponibles'] ?>
                    </p>
                    </div>
                    <?php endforeach ?>
                    </div>

                    <div class="col-md-4">
                    <?php  foreach ($graderiasz3 as $row ): ?>
                    <div class="rojo padding">
                        <strong><?=$row['zona'] ?> </strong><br>
                        <p>
                            TOTALES: <?=$row['totales'] ?> <br>
                            RESERVADOS: <?=$row['reservados'] ?> <br>
                            VENDIDOS: <?=$row['vendidos'] ?>  <br>
                            DISPONIBLES: <?=$row['disponibles'] ?>
                    </p>
                    </div>
                    <?php endforeach ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                    <?php  foreach ($graderiasz4 as $row ): ?>
                        <div class="azul padding">
                            <strong><?=$row['zona'] ?> </strong><br>
                            <p>
                                TOTALES: <?=$row['totales'] ?> <br>
                                RESERVADOS: <?=$row['reservados'] ?> <br>
                                VENDIDOS: <?=$row['vendidos'] ?>  <br>
                                DISPONIBLES: <?=$row['disponibles'] ?>
                        </p>
                        </div>
                        <?php endforeach ?>
                    </div>

                    <div class="col-md-4">
                    <?php  foreach ($graderiasz5 as $row ): ?>
                        <div class="naranja padding">
                            <strong><?=$row['zona'] ?> </strong><br>
                            <p>
                                TOTALES: <?=$row['totales'] ?> <br>
                                RESERVADOS: <?=$row['reservados'] ?> <br>
                                VENDIDOS: <?=$row['vendidos'] ?>  <br>
                                DISPONIBLES: <?=$row['disponibles'] ?>
                        </p>
                        </div>
                        <?php endforeach ?>
                    </div>

                    <div class="col-md-4">
                    <?php  foreach ($graderiasz6 as $row ): ?>
                        <div class="celeste padding">
                            <strong><?=$row['zona'] ?> </strong><br>
                            <p>
                                TOTALES: <?=$row['totales'] ?> <br>
                                RESERVADOS: <?=$row['reservados'] ?> <br>
                                VENDIDOS: <?=$row['vendidos'] ?>  <br>
                                DISPONIBLES: <?=$row['disponibles'] ?>
                        </p>
                        </div>
                        <?php endforeach ?>
                    </div>
                </div>
                                        
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
            <div class="panel panel-default p2" style="max-width: 24rem; height:14rem">
                <div class="panel-heading"><strong>Alasitas</strong> </div>
                <?php  foreach ($alasitas as $row ): ?>
                <div class="panel-body">
                    <h5> Totales: <?=$row['totales'] ?> </h5>
                    <h5> Vendidos: <?=$row['vendidos'] ?> </h5>
                    <h5> Disponibles: <?=$row['disponibles'] ?> </h5>
                </div>
                <?php endforeach ?>
            </div>
            </div>
            <div class="col-md-3">
            <div class="panel panel-default p3" style="max-width: 24rem; height:14rem">
                <div class="panel-heading"> <strong>Eventuales</strong> </div>
                <?php  foreach ($eventuales as $row ): ?>
                <div class="panel-body">
                    <h5> Totales: <?=$row['totales'] ?> </h5>
                    <h5> Vendidos: <?=$row['vendidos'] ?> </h5>
                    <h5> Disponibles: <?=$row['disponibles'] ?> </h5>
                </div>
                <?php endforeach ?>
            </div>
            </div>
        </div>

</div>
</div>



<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"",// always need it for jquery plugin
])?>
<?php Modal::end(); ?>



