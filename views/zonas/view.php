<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Zonas */
?>
<div class="zonas-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
          //  'zona_id',
            'gestion.gest_nombre',
            'zona_nombre',
            'zona_color',
            'zona_color_hexadecimal',
            'zona_descripcion',
            'zona_estado',
        ],
    ]) ?>

</div>
