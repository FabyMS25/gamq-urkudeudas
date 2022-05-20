<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SearchPagosEventuales */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pagos-eventuales-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'eventual_id') ?>

    <?= $form->field($model, 'usua_id') ?>

    <?= $form->field($model, 'contri_id') ?>

    <?= $form->field($model, 'sitios_id') ?>

    <?= $form->field($model, 'activi_id') ?>

    <?php // echo $form->field($model, 'eventual_fecha_hora_pago') ?>

    <?php // echo $form->field($model, 'eventual_fecha_inicio') ?>

    <?php // echo $form->field($model, 'eventual_fecha_limite') ?>

    <?php // echo $form->field($model, 'eventual_cantidad_dia') ?>

    <?php // echo $form->field($model, 'eventual_nro_comprobante') ?>

    <?php // echo $form->field($model, 'eventual_importe_patente') ?>

    <?php // echo $form->field($model, 'eventual_costo_comprobante') ?>

    <?php // echo $form->field($model, 'eventual_costo_sentaje') ?>

    <?php // echo $form->field($model, 'eventual_costo_aseo') ?>

    <?php // echo $form->field($model, 'eventual_importe_total') ?>

    <?php // echo $form->field($model, 'eventual_anulado') ?>

    <?php // echo $form->field($model, 'eventual_anulado_detalle') ?>

    <?php // echo $form->field($model, 'eventual_anulado_fecha_hora') ?>

    <?php // echo $form->field($model, 'eventual_preliquidacion') ?>

    <?php // echo $form->field($model, 'eventual_user_id_preliquidacion') ?>

    <?php // echo $form->field($model, 'eventual_estado') ?>

    <?php // echo $form->field($model, 'eventual_cantidad_sitio') ?>

    <?php // echo $form->field($model, 'eventual_nro_liquidacion') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
