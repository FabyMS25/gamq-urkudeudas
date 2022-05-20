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
        'attribute'=>'eventual_nro_comprobante',
        'width' => '60px',
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
    /*  [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'eventual_importe_total',
          'pageSummary'=>true,        
        'format'=>['decimal', 2],
        'hAlign' => \kartik\grid\GridView::ALIGN_RIGHT,
        'width' => '50px',
     ],
    
     [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'eventual_user_id_preliquidacion',
        'value' => function ($model){
            return (new app\models\Usuario())->nombreCompletoUsuario($model->eventual_user_id_preliquidacion);
        }
    ],*/
     [
      'class'=>'\kartik\grid\DataColumn',
      'attribute'=>'eventual_anulado_fecha_hora',
      'width' => '70px',
      /*'filterType'=> kartik\grid\GridView::FILTER_DATE_RANGE,      
        'filterWidgetOptions' => [                                   
            'convertFormat' => true,
            'pluginOptions'=>[
                'format' => 'yyyy-mm-dd',               
                'endDate' => '+0d',                
                'todayHighlight' => true,
                'locale' => [                                        
                    'separator' => ' a ',
                    ]
             ],
        
        ],*/
    ],
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
        // 'attribute'=>'eventual_preliquidacion',
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
        'template' => '{view} ',
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
                        return Html::a('<i class="glyphicon glyphicon-print"></i>', ['recibo-liquidacion', 'id'=>$model->eventual_id],
                                ['title'=> 'Imprimir liquidacion ', 'role'=>'modal-remote', 'data-toggle'=>'tooltip',]);                    
                },
                'anular-pago' => function ($url, $model, $key){ //glyphicon glyphicon-user                           
                        return Html::a('<i class="glyphicon glyphicon-remove-sign"></i>', ['anular-liquidacion', 'id'=>$model->eventual_id],
                                ['title'=> 'Anular liquidacion ', 'role'=>'modal-remote', 
                                    'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                                    'data-request-method'=>'post',
                                    'data-toggle'=>'tooltip',
                                    'data-confirm-title'=>'ADVERTENCIA',
                                    'data-confirm-message'=>'¿Esta seguro de anula la liquidacion: <strong>'.$model->eventual_nro_liquidacion.'</strong>?'
                                ]);                    
                },
                
        ],
       
    ],

];   