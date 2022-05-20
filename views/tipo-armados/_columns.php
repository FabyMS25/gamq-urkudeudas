<?php
use yii\helpers\Url;

return [
    
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'gest_id',
         'value' => 'gestion.gest_nombre',
        'filter' =>false
    ],
    
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'zona_id',
        'value' => 'zona.zona_nombre',
        'filter' =>false
    ],
    
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'tip_arm_descricpion',
        'width' => '450px',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'tip_arm_patente',
    ],
    [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'tip_arm_tasa_aseo',
    ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'tip_arm_unidad_medida',
         'width' => '80px',
    ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'tip_arm_estado',
    // ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{view} {update}',
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