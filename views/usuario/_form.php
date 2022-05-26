<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Usuario */
/* @var $form yii\widgets\ActiveForm */
$items = ['administrador' => 'Administrador', 'preliquidador' => 'Preliquidador', 'cajero'=>'Cajero', 'supervisor'=>'Supervisor'];
?>

<div class="usuario-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'usua_nombres')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'usua_apellidos')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'usua_ci')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'usua_cuenta')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'usua_password')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'usua_rol')->dropDownList($items, ['prompt' => '* Seleccione una opcion*']) ?>
  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
