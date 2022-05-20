<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Categorias */
?>
<div class="categorias-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
           // 'categ_id',
            'categ_nombre',
            'categ_codigo',
            'categ_estado',
        ],
    ]) ?>

</div>
