<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Usuario;

return [
    
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute' => 'pago_nro_comprobante',
        
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute' => 'pago_nro_liquidacion',
        //'value'=>'graderiaSilla.grad_codigo',
    ],
    
   //pago_id
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
    
     [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'pago_importe_total',
        'pageSummary'=>true,        
        'format'=>['decimal', 2],
        'hAlign' => \kartik\grid\GridView::ALIGN_RIGHT,
     ],
    
    [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'pago_cobrado',
         'value'=> function($model){
                return ($model->pago_cobrado !== null)?"Si": null;     
         }
     ],    
    [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'pago_fecha_hora_cobro',
         'filter' => FALSE
       /* 'filterType'=> kartik\grid\GridView::FILTER_DATE_RANGE,
        'filterWidgetOptions' => [                       
            'readonly' => TRUE,
            'pluginOptions'=>[
                'format' => 'dd/mm/yyyy',
                'autoWidget' => true,
                'autoclose' => true,
                'endDate' => '+0d',                
                'todayHighlight' => true,
                'locale' => [                     
                        'separator' => ' a ',
                    ]
            ],
        'options' => ['placeholder' => ''],
        ],*/
     ],
   [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'pago_con_exencion',
         'value'=> function($model){
                return ($model->pago_con_exencion)?"Si": "No";     
         },
         'filter' => [true=>'Si', false=>'No'],
         'hAlign' => 'center',
         'width' => '120px',
     ],
    
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{view} {anular} {comprobante}',
        'dropdown' => false,
        'vAlign'=>'middle',
        'urlCreator' => function($action, $model, $key, $index) { 
                return Url::to([$action,'id'=>$key]);
        },
        'viewOptions'=>['role'=>'modal-remote','title'=>'Ver','data-toggle'=>'tooltip'],
        /*'updateOptions'=>['role'=>'modal-remote','title'=>'Update', 'data-toggle'=>'tooltip'],
        'deleteOptions'=>['role'=>'modal-remote','title'=>'Delete', 
                          'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                          'data-request-method'=>'post',
                          'data-toggle'=>'tooltip',
                          'data-confirm-title'=>'Are you sure?',
                          'data-confirm-message'=>'Are you sure want to delete this item'], */
         'buttons' =>[
            
                'anular' => function ($url, $model, $key){ //glyphicon glyphicon-user                           
                        return Html::a('<i class="glyphicon glyphicon-remove-circle"></i>', ['anular-pago', 'id'=>$model->pago_id],
                                ['title'=> 'Anular cobro',
                                     'class'=>'btn btn-primary btn-xs',
                                    'role'=>'modal-remote', 'data-toggle'=>'tooltip',]);
                    
                },
                 'comprobante' => function ($url, $model, $key){ //glyphicon glyphicon-user                           
                        return Html::a('<i class="glyphicon glyphicon-print"></i>', ['comprobante-pago', 'id'=>$model->pago_id],
                                ['title'=> 'Comprobante de pago',
                                      'class'=>'btn btn-primary btn-xs',
                                    'role'=>'modal-remote', 'data-toggle'=>'tooltip',]);
                    
                }
                
                        
        ],
        'visibleButtons' => [            
            'comprobante' => function ($model, $key, $index) { 
               // $esCajero = ($model->usua_id == Yii::$app->user->id);
                $modelGeneral = new app\models\General();
                $dia_vigente = $modelGeneral->verificarFechaVigente($model->pago_fecha_hora_cobro);
                    return $dia_vigente ;
            },
            
            'anular' => function ($model, $key, $index) {                
                    return Usuario::getRolAdmin() ;
            }
        ],
                 'width' => '120px',
    ],

];   