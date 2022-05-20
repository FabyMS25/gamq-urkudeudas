<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Pagos */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pagos-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'grad_id')->textInput() ?>

    <?= $form->field($model, 'usua_id')->textInput() ?>

    <?= $form->field($model, 'contri_id')->textInput() ?>

    <?= $form->field($model, 'pago_codigo_control')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pago_longitud_modificada')->textInput() ?>

    <?= $form->field($model, 'pago_nro_comprobante')->textInput() ?>

    <?= $form->field($model, 'pago_descuento_porcentaje')->textInput() ?>

    <?= $form->field($model, 'pago_descuento_monto')->textInput() ?>

    <?= $form->field($model, 'pago_importe_patente')->textInput() ?>

    <?= $form->field($model, 'pago_aseo')->textInput() ?>

    <?= $form->field($model, 'pago_reposicion')->textInput() ?>

    <?= $form->field($model, 'pago_importe_total')->textInput() ?>

    <?= $form->field($model, 'pago_fecha_hora')->textInput() ?>

    <?= $form->field($model, 'pago_anulado')->textInput() ?>

    <?= $form->field($model, 'pago_anulado_detalle')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pago_anulado_fecha_hora')->textInput() ?>

    <?= $form->field($model, 'pago_preliquidacion')->textInput() ?>

    <?= $form->field($model, 'pago_id_user_preliquidacion')->textInput() ?>

    <?= $form->field($model, 'pago_estado')->textInput() ?>

  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
