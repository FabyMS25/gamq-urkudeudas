<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\TipoArmados */
?>
<div class="tipo-armados-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
           // 'tip_arm_id',
            [
                'attribute' =>'gest_id',
                'value' => $model->gestion->gest_nombre,
            ],
            [
                'attribute' =>'zona_id',
                'value' =>$model->zona->zona_nombre
            ],           
            
            'tip_arm_descricpion',
            'tip_arm_patente',
            'tip_arm_tasa_aseo',
            'tip_arm_unidad_medida',
            'tip_arm_estado',
        ],
    ]) ?>

</div>
