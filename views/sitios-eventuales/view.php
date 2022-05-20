<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\SitiosEventuales */
?>
<div class="sitios-eventuales-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'sitios_id',
            'sitios_codigo',
            'sitios_descripcion',
            'sitios_numero_sitio',
            'sitios_vendido',
            'sitios_estado',
        ],
    ]) ?>

</div>
