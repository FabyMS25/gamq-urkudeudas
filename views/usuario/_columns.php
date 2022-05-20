<?php
use yii\helpers\Url;

return [
    /*[
        'class' => 'kartik\grid\CheckboxColumn',
        'width' => '20px',
    ],*/
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
       /* [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'usua_id',
    ],*/
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
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'usua_estado',
    // ],
    [
        'class' => 'kartik\grid\ActionColumn',
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