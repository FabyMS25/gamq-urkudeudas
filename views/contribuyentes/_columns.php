<?php
use yii\helpers\Url;

return [
  
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
       /* [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'contri_id',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'ext_id',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'sindi_id',
    ],*/
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'contri_nombres',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'contri_paterno',
    ],
     [
       'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'contri_materno',
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'contri_apellidocasada',
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'contri_ci',
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'expedido.ext_nombre',
     ],
    
    /*[
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'contri_direccion',
     ],*/
     
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'contri_nit',
    // ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'contri_fecharegistro',
    // ],
   [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'contri_estado',
         'value'=> function ($model) {
            return ($model->contri_estado ===1)? "Si": "No";
         },
         'filter' =>false
,
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{view}  {update}',
        'dropdown' => false,
        'vAlign'=>'middle',
        'urlCreator' => function($action, $model, $key, $index) { 
                return Url::to([$action,'id'=>$key]);
        },
        'viewOptions'=>['role'=>'modal-remote','title'=>'Ver','data-toggle'=>'tooltip'],
        'updateOptions'=>['role'=>'modal-remote','title'=>'Actualizar', 'data-toggle'=>'tooltip'],
        'deleteOptions'=>['role'=>'modal-remote','title'=>'Delete', 
                          'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                          'data-request-method'=>'post',
                          'data-toggle'=>'tooltip',
                          'data-confirm-title'=>'Are you sure?',
                          'data-confirm-message'=>'Are you sure want to delete this item'], 
    ],

];   