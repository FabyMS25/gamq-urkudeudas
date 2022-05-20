<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Descargos */
?>
<div class="descargos-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'desc_id',
            'usua_id',
            'razon_id',
            'desc_nro_comprobante',
            'desc_responsable',
            'desc_fecha_hora',
            'desc_anulado',
            'desc_anulado_justificacion',
            'desc_anulado_fecha_hora',
            'desc_impreso',
            'desc_estado',
        ],
    ]) ?>

</div>
