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
        'attribute'=>'eventual_nro_liquidacion',
        'width' => '50px',
    ],
     [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'actividad.activi_descripcion',
        'value'=>'actividad.activi_descripcion',
         'width' => '180px',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'sitio.sitios_codigo',
        'value' => 'sitio.sitios_codigo'
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'sitio.sitios_numero_sitio',
        'value' => 'sitio.sitios_numero_sitio'
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
         'attribute'=>'eventual_importe_total',
         'width' => '50px',
     ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'eventual_fecha_hora_liquidacion',
        /*'filterType'=> kartik\grid\GridView::FILTER_DATE,
        'filterWidgetOptions' => [  
            'readonly' => TRUE,
            'pluginOptions'=>[
                'format' => 'yyyy-mm-dd',
                ]
            ]*/
    ],
    
     [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'eventual_user_id_preliquidacion',
        'value' => function ($model){
            return (new app\models\Usuario())->nombreCompletoUsuario($model->eventual_user_id_preliquidacion);
        }
    ],
    
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'eventual_cantidad_dia',
    // ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'eventual_nro_comprobante',
    // ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'eventual_importe_patente',
    // ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'eventual_costo_comprobante',
    // ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'eventual_costo_sentaje',
    // ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'eventual_costo_aseo',
    // ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'eventual_importe_total',
    // ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'eventual_anulado',
    // ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'eventual_anulado_detalle',
    // ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'eventual_anulado_fecha_hora',
    // ],
    
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'eventual_user_id_preliquidacion',
    // ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'eventual_estado',
    // ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'eventual_cantidad_sitio',
    // ],
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'eventual_nro_liquidacion',
    // ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{view} {anular} {cobrar} {print}',
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
                
                'print' => function ($url, $model, $key){ //glyphicon glyphicon-user    
                        $action = $model->linkReciboPreliquidacion();
                        return Html::a('<i class="glyphicon glyphicon-print"></i>', [$action, 'id'=>$model->eventual_id],
                                ['title'=> 'Recibo preliquidacion ',
                                     'class'=>'btn btn-primary btn-xs',
                                    'role'=>'modal-remote', 'data-toggle'=>'tooltip',]);                    
                },
                'anular' => function ($url, $model, $key){ //glyphicon glyphicon-user                           
                        $items=['A SOLICITUD DEL CONTRIBUYENTE','NO SE EFECTUO EL PAGO EN EL DIA DEL REGISTRO','REGISTRO DE DATOS INCONSISTENTES'];
                        
                        return Html::a('<i class="glyphicon glyphicon-remove-sign"></i>', ['anular-liquidacion', 'id'=>$model->eventual_id],
                                ['title'=> 'Anular preliquidacion ', 'class'=>'btn btn-primary btn-xs',
                                    'role'=>'modal-remote', 
                                    //'data-confirm'=>false, 
                                    //'data-method'=> false,// for overide yii data api
                                    //'data-request-method'=>'post',
                                    'data-toggle'=>'tooltip',
                                    //'data-confirm-title'=>'ADVERTENCIA',
                                    //'data-confirm-message'=>'Motivo: <br>' .Html::activeDropDownList($model,'eventual_descripcion',$items) .'<br> ¿Esta seguro de anular la liquidacion: <strong>'.$model->eventual_nro_liquidacion.'</strong>?',
                                    
                                ]);                    
                },
                'cobrar' => function ($url, $model, $key){ //glyphicon glyphicon-user                           
                        return Html::a('<i class="glyphicon glyphicon-usd"></i>', ['cobrar-liquidacion', 'id'=>$model->eventual_id],
                                ['title'=> 'Cobrar preliquidacion ',
                                    'class'=>'btn btn-primary btn-xs',
                                    'role'=>'modal-remote', 'data-toggle'=>'tooltip',]);                    
                },
                
        ],
        'visibleButtons' => [
            
            'anular' => function ($model, $key, $index) {                                     
                    $modelGeneral = new app\models\General;
                    $model->eventual_anulado=1;
                    $model->eventual_anulado_detalle="Noda"; 
                    $dia_vigente = $modelGeneral->verificarFechaVigente($model->eventual_fecha_hora_liquidacion);
                        return (Usuario::getRolPreli() && $dia_vigente) ; 
            },
             'cobrar' => function ($model, $key, $index) { 
                 return Usuario::getRolCajero() ;                   
            },
             'print' => function ($model, $key, $index){ //glyphicon glyphicon-user                           
                        return Usuario::getRolPreli() ;                    
                },
        ],
        'width' => '130px',
    ],

];   