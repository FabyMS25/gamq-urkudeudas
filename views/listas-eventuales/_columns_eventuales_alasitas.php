
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
        'attribute'=>'sitios_codigo',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'sitios_numero_sitio',
        'width' => '120px',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'sitios_descripcion',
    ],
    //
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'sitios_es_alasita',
        'value'=>'sitioParaAlasita',
        'filter' => [TRUE => "Si", FALSE => "No"],
        'hAlign' => \kartik\grid\GridView::ALIGN_CENTER,
        'width' => '30px',
    ],
    
    /*[
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'sitios_vendido',
    ],*/
    // [
        // 'class'=>'\kartik\grid\DataColumn',
        // 'attribute'=>'sitios_estado',
    // ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{preliquidar-sitios-alasitas}',
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
         'buttons' =>[
                'preliquidar-sitios-alasitas' => function ($url, $model, $key){ 
                        $esAlasita = $model->sitios_es_alasita;                        
                        if($esAlasita){
                            return Html::a('Preliquidar', ['pagos-eventuales/create-alasitas', 'id'=>$model->sitios_id],
                                ['title'=> 'Preliquidar alasitas ',
                                     'class'=>'btn btn-success btn-xs',
                                    'role'=>'modal-remote', 'data-toggle'=>'tooltip'
                                ]);  
                        }
                        
                         if(!$esAlasita){
                            return Html::a('Preliquidar', ['pagos-eventuales/create-eventual', 'id'=>$model->sitios_id],
                                ['title'=> 'Preliquidar eventuales ',
                                     'class'=>'btn btn-primary btn-xs',
                                    'role'=>'modal-remote', 'data-toggle'=>'tooltip'
                                ]);  
                        }                                          
                },               
                
        ],
                        
       'visibleButtons' => [            
            'preliquidar-sitios-alasitas' => function ($model, $key, $index) {                                
                      
                    return Usuario::getRolPreli();
            }
        ],
                
    ],

];   