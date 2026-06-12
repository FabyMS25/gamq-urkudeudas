<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Usuario;

return [
    
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
   //pago_id
    
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute' => 'pago_nro_liquidacion',
        'width' => '120px',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute' => 'pago_tasa',
        'width' => '80px',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute' => 'codigo',
        'value'=>'graderiaSilla.grad_codigo',
        'width' => '120px',
    ],    
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
         'width' => '120px',
     ],
    [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'pago_con_exencion',
         'value'=> function($model){
                return ($model->pago_con_exencion)?"Si": "No";     
         },
         'filter' => [true=>'Si', false=>'No'],
         'width' => '120px',
     ],
    
    
    [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'pago_fecha_hora_preliquidacion',
     ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'pago_id_user_preliquidacion',
        'value' => function ($model){
            return (new app\models\Usuario())->nombreCompletoUsuario($model->pago_id_user_preliquidacion);
        },
        'filter' =>FALSE
    ],
    
    /* [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'pago_cobrado',
         'value'=> function($model){
                return ($model->pago_cobrado === 1)?"Si": "No";     
         },
         'filter' => ['1'=>'Si', '0'=>'No'],
     ],*/
             //pago_cobrado     
     
    
   
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{view}  {anular} {print} {cobro} ',
        'dropdown' => false,
        'vAlign'=>'middle',
        'urlCreator' => function($action, $model, $key, $index) { 
                return Url::to([$action,'id'=>$key]);
        },
        'viewOptions'=>['role'=>'modal-remote','title'=>'Ver','data-toggle'=>'tooltip'],
        
        'buttons' =>[           
                
                'print' => function ($url, $model, $key){ //glyphicon glyphicon-user                           
                        return Html::a('<i class="glyphicon glyphicon-print"></i>', ['recibo-liquidacion', 'id'=>$model->pago_id],
                                ['title'=> 'Recibo preliquidacion ',
                                     'class'=>'btn btn-primary btn-xs',
                                    'role'=>'modal-remote', 'data-toggle'=>'tooltip',]);                    
                },
                'cobro' => function ($url, $model, $key){ //glyphicon glyphicon-user                           
                        return Html::a('<i class="glyphicon glyphicon-usd"></i>', ['cobrar', 'id'=>$model->pago_id],
                                ['title'=> 'Cobrar', 
                                      'class'=>'btn btn-primary btn-xs',
                                    'role'=>'modal-remote', 'data-toggle'=>'tooltip',]);                   
                },
               
                'anular' => function ($url, $model, $key){ //glyphicon glyphicon-user                           
                        return Html::a('<i class="glyphicon  glyphicon-remove-circle"></i>', ['anular-preliquidacion', 'id'=>$model->pago_id],
                                ['title'=> 'Anular preliquidacion ',
                                     'class'=>'btn btn-primary btn-xs',
                                    'role'=>'modal-remote', 'data-toggle'=>'tooltip', ]);
                                  //  'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                          //'data-request-method'=>'post',
                          
                          //'data-confirm-title'=>'ADVERTENCIA',
                          //'data-confirm-message'=>'¿Esta seguro de anular la preliquidacion '.$model->pago_nro_liquidacion.'?'
                        
                    
                },
                
        ],
        'visibleButtons' => [   
            'print' => function ($model, $key, $index){ //glyphicon glyphicon-user                           
                        return Usuario::getRolPreli() ;                    
                },
            'anular' => function ($model, $key, $index){ //glyphicon glyphicon-user    
                    $modelGeneral = new app\models\General;
                    $dia_vigente = $modelGeneral->verificarFechaVigente($model->pago_fecha_hora_preliquidacion);
                        return (Usuario::getRolPreli() && $dia_vigente) ;                    
                },
                        
            'cobro' => function ($model, $key, $index){ //glyphicon glyphicon-user                           
                        return Usuario::getRolCajero() ;                    
                },
            
        ],
        'width' => '130px',
        
    ],

];   