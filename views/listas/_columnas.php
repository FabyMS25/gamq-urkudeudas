
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
        'attribute'=>'zona',
        'value' => 'zona.zona_nombre',
        'filter' =>[1 => 'Zona 1', 2=>'Zona 2', 3=> 'Zona 3', 4=>'Zona 4', 5=>'Zona 5', 6=>'Zona 6', 7=>'Zona 7'],
        'width' => '95px',
               
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
    [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'grad_reservado',
         'value'  => function($model){
                return ($model->grad_reservado == 1)? "Si":"No";
         },
        'filter' =>[1 => 'Si', 0=>'No']
     ],
  

];   
