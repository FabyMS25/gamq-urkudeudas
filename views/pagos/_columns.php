
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
        'attribute'=>'gest_id',
         'value' => 'gestion.gest_nombre',
        'filter' =>false
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'zona',
        'value' => 'zona.zona_nombre',
        'width' => '90px',
    ],
    
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'grad_codigo',
        //'value'=>'grad_codigo',
    ],
    [
        'class'=>'\kartik\grid\DataColumn',
        'attribute'=>'grad_direccion',
    ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'grad_longitud',
          'filter' =>false
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'grad_acera',
          'filter' =>false
     ],
     [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'grad_tipo_armado',
          'filter' =>false
     ],
    [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'grad_tipo_sitio',
         'filter' =>false
     ],
    [
         'class'=>'\kartik\grid\DataColumn',
         'attribute'=>'grad_reservado',
         'value'  => function($model){
                return ($model->grad_reservado == 1)? "Si":"No";
         },
         'filter' =>["1" => "Si", "0"=>"No"]
     ],
     
    [
        'label' => 'Acciones',
        'content' => function ($model, $key, $index, $column) {
             $reservado = $model->grad_reservado;
            $datos =  "<p><strong>". 
                    $model->zona->zona_nombre."<br> Codigo: ".$model->grad_codigo.
                    "<br> Tipo sitio: ".$model->grad_tipo_sitio."</strong>"
                    . "</p>"
                    .'<span class="text-danger"> ¿Esta seguro de realizar la preliquidacion? </span>';
            
            if($reservado !== 1 && (Usuario::getRolPreli())):
                return Html::a('<span class="btn btn-success btn-xs">Preliquidacion</span>', 
                    ['pagos/preliquidar', 'id' => $model->grad_id],                         
                    ['role'=>'modal-remote',                         
                        'title'=>'Preliquidar',
                       
                          'data-confirm'=>false, 'data-method'=>false,
                          'data-request-method'=>'post',
                          'data-toggle'=>'tooltip',
                          'data-confirm-title'=>'<strong>Datos del sitio para graderia o sillas</strong>',
                          'data-confirm-message'=> $datos]);                    
            endif;
            
        }
    ],
    

];   


