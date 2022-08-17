<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\RazonSociales;


$listaRazones = (new RazonSociales())->listaRazonesSocialesModel();
$items = ArrayHelper::map($listaRazones, 'razon_id', 'razon_nombre');
//var_dump($items);
?>

<div class="descargos-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'usua_id')->textInput(['id'=>'usua_id','readonly'=> true]) ?>

    <?= $form->field($model, 'razon_id')->dropDownList($items, ['prompt'=>'** Seleccione una opcion **']) ?>

    <?= $form->field($model, 'desc_nro_comprobante')->textInput(['id'=>'desc_nro_comprobante']) ?>

    <?= $form->field($model, 'desc_responsable')->textInput(['id'=>'desc_responsable', 'maxlength' => true]) ?>

    

    <?= $form->field($model, 'desc_impreso')->textInput(['id'=>'desc_impreso']) ?>

    

  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
