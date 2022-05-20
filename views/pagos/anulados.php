<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use johnitvn\ajaxcrud\CrudAsset; 
use app\models\Usuario;

/* @var $this yii\web\View */
/* @var $searchModel app\models\SearchPagos */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Graderias y sillas pagados y anulados';
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
<div class="pagos-index">
    <div id="ajaxCrudDatatable">
        <?=GridView::widget([
            'id'=>'crud-datatable',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'pjax'=>true,
            'columns' => require(__DIR__.'/_columns_anulados.php'),
            'toolbar'=> [
                ['content'=>  
                    ((Usuario::getRolCajero())?
                     Html::a('<i class="glyphicon glyphicon-print"></i> Reporte anulados', ['reporte-anulado-cajero'],
                        ['role'=>'modal-remote','title'=> 'Reporte de anulados','class'=>'btn btn-default']):null).
                    Html::a('<i class="glyphicon glyphicon-repeat"></i>', [''],
                    ['data-pjax'=>1, 'class'=>'btn btn-default', 'title'=>'Recargar datos']).
                    '{toggleData}'.
                    '{export}'
                ],
            ],          
            'striped' => true,
            'condensed' => true,
            'responsive' => true,          
            'panel' => [
                'type' => 'primary', 
                'heading' => '<i class="glyphicon glyphicon-list"></i> '.$this->title,
                'before'=>'',
                'after'=>                   
                        '<div class="clearfix"></div>',
            ]
        ])?>
    </div>
</div>
<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"",// always need it for jquery plugin
])?>
<?php Modal::end(); ?>
<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

