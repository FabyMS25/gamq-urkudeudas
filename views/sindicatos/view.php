<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Sindicatos */
?>
<div class="sindicatos-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
           // 'sindi_id',
            'sindi_nombre',
            'sindi_descripcion',
            'sindi_estado',
        ],
    ]) ?>

</div>
