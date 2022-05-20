<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Sindicatos */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sindicatos-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'sindi_nombre')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sindi_descripcion')->textInput(['maxlength' => true]) ?>
  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
