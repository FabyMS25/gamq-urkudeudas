<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PagosEventuales */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pagos-eventuales-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'usua_id')->textInput() ?>

    <?= $form->field($model, 'contri_id')->textInput() ?>

    <?= $form->field($model, 'sitios_id')->textInput() ?>

    <?= $form->field($model, 'activi_id')->textInput() ?>

    <?= $form->field($model, 'eventual_fecha_hora_pago')->textInput() ?>

    <?= $form->field($model, 'eventual_fecha_inicio')->textInput() ?>

    <?= $form->field($model, 'eventual_fecha_limite')->textInput() ?>

    <?= $form->field($model, 'eventual_cantidad_dia')->textInput() ?>

    <?= $form->field($model, 'eventual_nro_comprobante')->textInput() ?>

    <?= $form->field($model, 'eventual_importe_patente')->textInput() ?>

    <?= $form->field($model, 'eventual_costo_comprobante')->textInput() ?>

    <?= $form->field($model, 'eventual_costo_sentaje')->textInput() ?>

    <?= $form->field($model, 'eventual_costo_aseo')->textInput() ?>

    <?= $form->field($model, 'eventual_importe_total')->textInput() ?>

    <?= $form->field($model, 'eventual_anulado')->textInput() ?>

    <?= $form->field($model, 'eventual_anulado_detalle')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'eventual_anulado_fecha_hora')->textInput() ?>

    <?= $form->field($model, 'eventual_preliquidacion')->textInput() ?>

    <?= $form->field($model, 'eventual_user_id_preliquidacion')->textInput() ?>

    <?= $form->field($model, 'eventual_estado')->textInput() ?>

    <?= $form->field($model, 'eventual_cantidad_sitio')->textInput() ?>

  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
