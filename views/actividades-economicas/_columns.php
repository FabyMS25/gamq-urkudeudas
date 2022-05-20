<?php
use yii\helpers\Url;

return [
   
    [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
     
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'categ_id',
        'value'=>'categoria.categ_nombre',
        'filter'=> \yii\helpers\ArrayHelper::map((new app\models\Categorias())->listaCategoriasModel(), 'categ_id', 'categ_nombre')
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'activi_descripcion',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'activi_largo_mts',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'activi_ancho_mts',
    ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'activi_superficie',
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'activi_costo_patente',
     ],
    [
         'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'activi_costo_sentaje_dia',
     ],
    [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'activi_costo_aseo_por_dia',
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'activi_costo_aseo_por_sitio',
     ],
    [
        'class' => 'kartik\grid\ActionColumn',
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