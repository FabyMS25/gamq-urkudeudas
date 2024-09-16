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
        'attribute' => 'pago_nro_liquidacion',
        
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute' => 'codigo',
        'value'=>'graderiaSilla.grad_codigo',
    ],//
    
    
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
    /*[
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'graderiaSilla.grad_longitud',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'pago_longitud_modificada',
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'pago_importe_patente',
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'pago_aseo',
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'pago_reposicion',
     ],
    
    
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'pago_descuento_monto',
     ],*/
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'pago_importe_total',
     ],
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
        'template' => '{view} ',
        'dropdown' => false,
        'vAlign'=>'middle',
        'urlCreator' => function($action, $model, $key, $index) { 
                return Url::to([$action,'id'=>$key]);
        },
        'viewOptions'=>['role'=>'modal-remote','title'=>'View','data-toggle'=>'tooltip'],
        'updateOptions'=>['role'=>'modal-remote','title'=>'Update', 'data-toggle'=>'tooltip'],
        'deleteOptions'=>['role'=>'modal-remote','title'=>'Delete', 
                          'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                          'data-request-method'=>'post',
                          'data-toggle'=>'tooltip',
                          'data-confirm-title'=>'Are you sure?',
                          'data-confirm-message'=>'Are you sure want to delete this item'], 
         
        
    ],

];   