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
        'attribute'=>'generadores.desc_responsable',     
        'filter' =>false
    ],
    
    /* [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'desc_id',
        'filter' =>false
    ], */
    
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
        'attribute'=>'detalle_cantidad_anulado',
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
     /* [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'detalle_estado',
         'filter' =>false
    ], */
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'nro_comprobante',
        'filter' =>false
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'detalle_tasa',
        'filter' =>false
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{delete} {cobro} {print} {tasa}',
        'dropdown' => false,
        'width' => '160px',
        'vAlign'=>'middle',
        'urlCreator' => function($action, $model, $key, $index) { 
                return Url::to([$action,'id'=>$key]);
        },
        
        'buttons' => [
            'cobro' => function ($url, $model, $key) {    
                if ($model->nro_comprobante == null) {                        
                    return Html::a('Tasa', ['cobrar', 'id'=>$model->detalle_id],
                        ['title'=> 'Cobrar', 
                              'class'=>'btn btn-primary btn-xs',
                            'role'=>'modal-remote', 'data-toggle'=>'tooltip',]);  
                }                  
            },
            'print' => function ($url, $model, $key){                            
                return Html::a('<i class="glyphicon glyphicon-print"></i>', ['recibo-liquidacion', 'id'=>$model->detalle_id],
                        ['title'=> 'Recibo preliquidacion ',
                             'class'=>'btn btn-primary btn-xs',
                            'role'=>'modal-remote', 'data-toggle'=>'tooltip',]);                    
            },
            
        ],

        //'viewOptions'=>['role'=>'modal-remote','title'=>'View','data-toggle'=>'tooltip', 'hidden' => true],
        //'updateOptions'=>['role'=>'modal-remote','title'=>'Update', 'data-toggle'=>'tooltip', 'hidden' => true],
        'deleteOptions'=>['role'=>'modal-remote','title'=>'Anular', 
                          'data-confirm'=>false, 'data-method'=>false,
                          'data-request-method'=>'post',
                          'data-toggle'=>'tooltip',
                          'data-confirm-title'=>'Anular registro',
                          'data-confirm-message'=>'Esta seguro de anular este registro?'],
    ],

];   