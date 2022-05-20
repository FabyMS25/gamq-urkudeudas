<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\GraderiasSillas */
/* @var $form yii\widgets\ActiveForm */
$aceras = ['ESTE'=>'Este', 'OESTE'=>'Oeste', 'NORTE'=>'Norte','SUD'=>'Sud', ];
$tipos = ['GRADERIAS'=>'Graderias', 'SILLAS'=>'Sillas',];
$tipoSitios = ['BOCA CALLE' => "Boca calle", "FRONTIS" => "Frontis"];
?>

<div class="graderias-sillas-form">

    <?php $form = ActiveForm::begin(); ?>   
    
    <?php // $form->errorSummary($model); ?>

    <?= $form->field($model, 'grad_codigo')->textInput(['maxlength' => true]) ?>
    
    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'grad_longitud')->textInput() ?>   
        </div><div class="col-sm-6">
            <?= $form->field($model, 'grad_acera')->dropDownList($aceras,['prompt' => "** Seleccione la acera **"]) ?>
        </div>
    </div>

    <?= $form->field($model, 'grad_direccion')->textarea(['maxlength' => true, 'rows'=>2]) ?>    

    <?= $form->field($model, 'grad_tipo_armado')->dropDownList($tipos,['prompt' => "** Seleccione el tipo **"]) ?>

    <?= $form->field($model, 'grad_tipo_sitio')->dropDownList($tipoSitios,['prompt' => "** Seleccione el tipo de sitio **"]) ?>   
    
    <?= $form->field($model, 'grad_reservado')->dropDownList(["1"=>"Si"],['prompt' => "** Seleccione una opcion **"]) ?>   
  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>
