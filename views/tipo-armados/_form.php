<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\TipoArmados */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tipo-armados-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'tip_arm_descricpion')->textarea(['maxlength' => true, 'rows'=>3]) ?>

    <?= $form->field($model, 'tip_arm_patente')->textInput() ?>

    <?= $form->field($model, 'tip_arm_tasa_aseo')->textInput() ?>

    <?= $form->field($model, 'tip_arm_unidad_medida')->textInput(['maxlength' => true, 'readonly'=>true]) ?>

   

  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
