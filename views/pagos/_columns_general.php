<?php

use yii\helpers\Url;
use yii\helpers\Html;

return [
        [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
        [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'pago_nro_comprobante',
    ],
        [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'pago_nro_liquidacion',
    //'value'=>'graderiaSilla.grad_codigo',
    ],
    //pago_id
    [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'codigo',
        'value' => 'graderiaSilla.grad_codigo',
    ], //
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute' => 'nombre',
        'value'=>'contribuyente.nombreCompletoContribuyente',
    ],
    
    [
     
        'class'=>'\kartik\grid\DataColumn',
        'attribute' => 'ci',
        'value'=>'contribuyente.contri_ci',
        'width' => '120px',
    ],
        [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'pago_importe_total',
        'pageSummary' => true,
        'format' => ['decimal', 2],
        'hAlign' => \kartik\grid\GridView::ALIGN_RIGHT,
    ],
        [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'pago_preliquidacion',
        'format' => 'html',
        'value' => function($model) {
            return ($model->pago_preliquidacion == 1) ? "<span class='glyphicon glyphicon-ok text-success'> </span>" : null;
        },
        'filter' => ["1" => "Si", "0" => "No"]
    ],
        [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'pago_cobrado',
        'format' => 'html',
        'value' => function($model) {
            return ($model->pago_cobrado == 1) ? "<span class='glyphicon glyphicon-ok text-success'> </span>" : null;
        },
        'filter' => ["1" => "Si", "0" => "No"]
    ],
        [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'pago_anulado',
        'format' => 'html',
        'value' => function($model) {
            return ($model->pago_anulado == 1) ? "<span class='glyphicon glyphicon-ok text-success'> </span>" : null;
        },
        'filter' => ["1" => "Si", "0" => "No"]
    ],
        [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'pago_fecha_hora_cobro',
    /* 'filterType'=> kartik\grid\GridView::FILTER_DATE_RANGE,
      'filterWidgetOptions' => [
      'readonly' => TRUE,
      'pluginOptions'=>[
      'format' => 'dd/mm/yyyy',
      'autoWidget' => true,
      'autoclose' => true,
      'endDate' => '+0d',
      'todayHighlight' => true,
      'locale' => [
      'separator' => ' a ',
      ]
      ],
      'options' => ['placeholder' => ''],
      ], */
    ],
    /*

      [
      'class'=>'\kartik\grid\DataColumn',
      'attribute'=>'pago_id_user_preliquidacion',
      'value' => function ($model){
      return (new app\models\Usuario())->nombreCompletoUsuario($model->pago_id_user_preliquidacion);
      }
      ], */
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'pago_con_exencion',
         'value'=> function($model){
                return ($model->pago_con_exencion)?"Si": "No";     
         },
         'filter' => [true=>'Si', false=>'No'],
         'width' => '120px',
     ],
        [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{view}',
        'dropdown' => false,
        'vAlign' => 'middle',
        'urlCreator' => function($action, $model, $key, $index) {
            return Url::to([$action, 'id' => $key]);
        },
        'viewOptions' => ['role' => 'modal-remote', 'title' => 'Ver', 'data-toggle' => 'tooltip'],
        
        
    ],
];
