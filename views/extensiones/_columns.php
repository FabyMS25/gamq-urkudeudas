<?php
use yii\helpers\Url;

return [
    
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
    
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'ext_nombre',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'ext_abreviado',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'ext_estado',
        'value'=> function ($model) {
            return ($model->ext_estado === 1)? "ACTIVO": "INACTIVO";
         },
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' =>'{view} {update}',
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