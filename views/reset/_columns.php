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
        'attribute'=>'usua_nombres',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'usua_apellidos',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'usua_ci',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'usua_cuenta',
    ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'usua_password',
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'usua_rol',
     ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'dropdown' => false,
        //'template' => '{print}',
        'vAlign'=>'middle',
        'urlCreator' => function($action, $model, $key, $index) { 
                return Url::to([$action,'id'=>$key]);
        },
        
        'viewOptions'=>['role'=>'modal-remote','title'=>'View','data-toggle'=>'tooltip','hidden' => true],

        'updateOptions'=>['role'=>'modal-remote','title'=>'Update', 'data-toggle'=>'tooltip'],
        'deleteOptions'=>['role'=>'modal-remote','title'=>'Delete', 
                          'data-confirm'=>false, 'data-method'=>false,
                          'data-request-method'=>'post',
                          'data-toggle'=>'tooltip',
                          'data-confirm-title'=>'Are you sure?',
                          'data-confirm-message'=>'Are you sure want to delete this item','hidden' => true],
    ],

];   