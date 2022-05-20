<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Extensiones */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="extensiones-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ext_nombre')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ext_abreviado')->textInput(['maxlength' => true]) ?>

    <?php // $form->field($model, 'ext_estado')->textInput() ?>

  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
