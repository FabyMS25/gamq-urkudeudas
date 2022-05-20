<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Gestiones */
?>
<div class="gestiones-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'gest_id',
            'gest_nombre',
            'gest_ordenanza',
            'gest_vigente',
            'gest_estado',
        ],
    ]) ?>

</div>
