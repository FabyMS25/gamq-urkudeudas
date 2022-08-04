<?php
use yii\helpers\Url;
use yii\helpers\Html;

return [
    
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'detalle_id',
        'filter' =>false
    ],
     
    
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'desc_id',
        'filter' =>false
    ],
    
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'detalle_precio',
        'filter' =>false
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'detalle_nro_inicio',
        'filter' =>false
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'detalle_nro_limite',
        'filter' =>false
    ],
    [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'detalle_cantidad',
         'filter' =>false
     ],
     [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'detalle_fecha_entrega',
        'filter' =>false
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'detalle_importe_bs',
         'filter' =>false
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'detalle_estado',
         'filter' =>false
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        'vAlign'=>'middle',
        'urlCreator' => function($action, $model, $key, $index) { 
                return Url::to([$action,'id'=>$key]);
        },
        
        'viewOptions'=>['role'=>'modal-remote','title'=>'View','data-toggle'=>'tooltip', 'hidden' => true],
        'updateOptions'=>['role'=>'modal-remote','title'=>'Update', 'data-toggle'=>'tooltip', 'hidden' => true],
        'deleteOptions'=>['role'=>'modal-remote','title'=>'Anular', 
                          'data-confirm'=>false, 'data-method'=>false,
                          'data-request-method'=>'post',
                          'data-toggle'=>'tooltip',
                          'data-confirm-title'=>'Anular registro',
                          'data-confirm-message'=>'Esta seguro de anular este registro?'],
    ],

];   