<?php
use yii\helpers\Url;

return [
    [
        'class' => 'kartik\grid\CheckboxColumn',
        'width' => '20px',
    ],
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
        [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'gest_id',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'gest_nombre',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'gest_ordenanza',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'gest_vigente',
        'value'=> function ($model) {
            return ($model->gest_vigente === 1 )? "Si": "No";
        },
        'filter' =>["1" => "Si", "0"=>"No"]
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'gest_estado',
        'value'=> function ($model) {
            return ($model->gest_estado === 1)? "ACTIVO": "INACTIVO";
         },
         'filter' =>["1" => "ACTIVO", "0"=>"INACTIVO"]
    ],
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