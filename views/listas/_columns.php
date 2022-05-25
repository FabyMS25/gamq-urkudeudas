
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
        'filter' =>false,
        'width' => '80px',
    ],
    
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'grad_codigo',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'grad_direccion',
    ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'grad_longitud',
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'grad_acera',
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'grad_tipo_armado',
     ],
    [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'grad_tipo_sitio',
     ],
     /*[
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'grad_vendido',
     ],*/
    [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'grad_reservado',
         'value'  => function($model){
                return ($model->grad_reservado == 1)? "Si":"No";
         },
         'filter' =>[1 => "Si", 0=>"No"]
     ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'grad_estado',
    // ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{view} {update} ',
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