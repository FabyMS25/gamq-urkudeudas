<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\GraderiasSillas */
?>
<div class="graderias-sillas-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
           // 'grad_id',
            [
                'attribute' =>'gest_id',
                'value' => $model->gestion->gest_nombre,
            ],
            [
                'attribute' =>'zona_id',
                'value' =>$model->zona->zona_nombre
            ],   
            'grad_codigo',
            'grad_codigo_catastral',
            'grad_direccion',
            'grad_propietario',
            'grad_longitud',
            'grad_acera',
            'grad_tipo_armado',
            'grad_tipo_sitio',
            'grad_vendido',
             [
                'attribute' =>'grad_reservado',
                'value' =>($model->grad_reservado == 1)? "Si":"No"
            ],
            'grad_estado',
        ],
    ]) ?>

</div>
