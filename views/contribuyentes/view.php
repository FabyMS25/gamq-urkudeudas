<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Contribuyentes */
?>
<div class="contribuyentes-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'contri_id',
            'ext_id',
            'sindi_id',
            'contri_nombres',
            'contri_paterno',
            'contri_materno',
            'contri_apellidocasada',
            'contri_ci',
            'contri_direccion',
            'contri_telefono',
            'contri_nit',
            'contri_fecharegistro',
            'contri_estado',
        ],
    ]) ?>

</div>
