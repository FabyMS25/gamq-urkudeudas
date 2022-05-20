<?php

use yii\helpers\Url;
use yii\helpers\Html;

return [
        [
        'class' => 'kartik\grid\SerialColumn',
        'width' => '30px',
    ],
        [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'gest_id',
        'value' => function($model) {
            return $model->gestion->gest_nombre;
        },
        'filter' => false,
                'width' => '50px',
    ],
        [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'zona_nombre',
            'width' => '70px',
    ],
        [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'zona_color',
            'width' => '70px',
    ],
        [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'zona_color_hexadecimal',
        'format' =>'html',
        'value' => function($model){ return  '<p style="background-color:'.$model->zona_color_hexadecimal.'"> &nbsp;&nbsp;&nbsp; </p>';},
            'width' => '70px',
        'filter' =>false
    ],
        [
        'class' => '\kartik\grid\DataColumn',
        'attribute' => 'zona_descripcion',
        
    ],
    // [
    // 'class'=>'\kartik\grid\DataColumn',
    // 'attribute'=>'zona_estado',
    // ],
    [
        'label' => 'Tipo armado - Graderias/sillas',
        'content' => function ($model, $key, $index, $column) {
            return Html::a('<span class="btn btn-default">Tipos</span>', ['tipo-armados/index', 'id' => $model->zona_id], ['title' => 'Tipos de armado - ' . $model->zona_nombre])
                    . "&nbsp &nbsp" .
                   Html::a('<span class="btn btn-default">Graderias/sillas</span>', ['graderias-sillas/index', 'id' => $model->zona_id], ['title' => 'Graderias y silla - ' . $model->zona_nombre]);
        }
    ],
        [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{view} {update}',
        'dropdown' => false,
        'vAlign' => 'middle',
        'urlCreator' => function($action, $model, $key, $index) {
            return Url::to([$action, 'id' => $key]);
        },
        'viewOptions' => ['role' => 'modal-remote', 'title' => 'View', 'data-toggle' => 'tooltip'],
        'updateOptions' => ['role' => 'modal-remote', 'title' => 'Update', 'data-toggle' => 'tooltip'],
        'deleteOptions' => ['role' => 'modal-remote', 'title' => 'Delete',
            'data-confirm' => false, 'data-method' => false, // for overide yii data api
            'data-request-method' => 'post',
            'data-toggle' => 'tooltip',
            'data-confirm-title' => 'Are you sure?',
            'data-confirm-message' => 'Are you sure want to delete this item'],
    ],
];
