<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Usuario */
?>
<div class="usuario-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'usua_id',
            'usua_nombres',
            'usua_apellidos',
            'usua_ci',
            'usua_cuenta',
            'usua_password',
            'usua_rol',
            'usua_estado',
        ],
    ]) ?>

</div>
