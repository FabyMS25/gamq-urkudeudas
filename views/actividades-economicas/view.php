<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\ActividadesEconomicas */
?>
<div class="actividades-economicas-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'activi_id',
            'categ_id',
            'activi_descripcion',
            'activi_largo_mts',
            'activi_ancho_mts',
            'activi_superficie',
            'activi_costo_patente',
            'activi_costo_sentaje_dia',
            'activi_costo_aseo_por_dia',
            'activi_costo_aseo_por_sitio',
        ],
    ]) ?>

</div>
