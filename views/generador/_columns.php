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
    ],
     
    
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'desc_id',
    ],
    
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'detalle_precio',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'detalle_nro_inicio',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'detalle_nro_limite',
    ],
    [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'detalle_cantidad',
     ],
     [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'detalle_fecha_entrega',
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'detalle_importe_bs',
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'detalle_estado',
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{print}',
        'dropdown' => false,
        'vAlign'=>'middle',
        'urlCreator' => function($action, $model, $key, $index) { 
                return Url::to([$action,'id'=>$key]);
        },
        'buttons' =>[             
            'print' => function ($url, $model, $key){ //glyphicon glyphicon-user                           
                    return Html::a('<i class="glyphicon glyphicon-remove"></i>', ['test', 'id'=>$model->detalle_id],
                            ['title'=> 'Test',
                                 'class'=>'btn btn-primary btn-xs',
                                'role'=>'modal-remote', 'data-toggle'=>'tooltip',]);   
                            },
        ] 
        /*'viewOptions'=>['role'=>'modal-remote','title'=>'View','data-toggle'=>'tooltip'],
        'updateOptions'=>['role'=>'modal-remote','title'=>'Update', 'data-toggle'=>'tooltip'],
        'deleteOptions'=>['role'=>'modal-remote','title'=>'Delete', 
                          'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                          'data-request-method'=>'post',
                          'data-toggle'=>'tooltip',
                          'data-confirm-title'=>'Are youuuuuuuuuuuuu sure?',
                          'data-confirm-message'=>'Are you sure want to delete this item'],*/
    ],

];   