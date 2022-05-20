<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\RazonSociales;

/* @var $this yii\web\View */
/* @var $model app\models\Descargos */
/* @var $form yii\widgets\ActiveForm */
$listaRazones = (new RazonSociales())->listaRazonesSocialesModel();
$items = ArrayHelper::map($listaRazones, 'razon_id', 'razon_nombre')
?>

<div class="descargos-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'usua_id')->textInput() ?>

    <?= $form->field($model, 'razon_id')->dropDownList($items, ['prompt'=>'** Seleccione una opcion **']) ?>

    <?= $form->field($model, 'desc_nro_comprobante')->textInput() ?>

    <?= $form->field($model, 'desc_responsable')->textInput(['maxlength' => true]) ?>

    

    <?= $form->field($model, 'desc_impreso')->textInput() ?>

    

  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
