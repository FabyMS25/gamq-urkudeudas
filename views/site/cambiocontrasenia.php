<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Sindicatos;
use kartik\date\DatePicker;


/* @var $this yii\web\View */
/* @var $model app\models\Rol */



?>



<div class="sitios-form">

    <?php $form = ActiveForm::begin(); ?>
 
    <?php
       echo $form->field($model, 'contrasenia_actual')->passwordInput();
       echo $form->field($model, 'contrasenia_nueva')->passwordInput();
    ?>

	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    

</div>
