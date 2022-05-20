<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\RazonSociales */
?>
<div class="razon-sociales-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'razon_id',
            'razon_nombre',
            'razon_estado',
        ],
    ]) ?>

</div>
