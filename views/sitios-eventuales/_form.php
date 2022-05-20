<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SitiosEventuales */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sitios-eventuales-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'sitios_codigo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sitios_descripcion')->textarea(['maxlength' => true, 'rows'=>3]) ?>

    <?= $form->field($model, 'sitios_numero_sitio')->textInput() ?>  
  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
