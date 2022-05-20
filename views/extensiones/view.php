<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Extensiones */
?>
<div class="extensiones-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'ext_id',
            'ext_nombre',
            'ext_abreviado',
            'ext_estado',
        ],
    ]) ?>

</div>
