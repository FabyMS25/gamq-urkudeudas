<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Descargos */
?>
<div class="descargos-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'detalle_id', 
            'desc_id',
            'detalle_precio',
            'detalle_nro_inicio',
            'detalle_nro_limite',
            'detalle_cantidad',
            'detalle_fecha_entrega',
            'detalle_importe_bs',
            'detalle_estado',
        ],
    ]) ?>

</div>
