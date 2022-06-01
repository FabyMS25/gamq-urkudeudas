
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
        'attribute'=>'zona_id',
        'width'=>'90px',
        'value' => 'zona_id',
               
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
