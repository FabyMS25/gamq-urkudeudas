<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Gestiones */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="gestiones-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'gest_nombre')->textInput() ?>

    <?= $form->field($model, 'gest_ordenanza')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'gest_vigente')->textInput() ?>

    <?= $form->field($model, 'gest_estado')->textInput() ?>

  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
