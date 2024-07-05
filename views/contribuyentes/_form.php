<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\Contribuyentes */
/* @var $form yii\widgets\ActiveForm */

$modelCiudad = new \app\models\Extensiones();
$listaCiudad = $modelCiudad->find()->where(['ext_estado' => 1])->all();
$modelSindicato = new \app\models\Sindicatos();
$listaSindicatos = $modelSindicato->find()->where(['sindi_estado' => 1])->orderBy('sindi_nombre asc')->all();
$listasexo = ['M', 'F'];
?>
<div class="contribuyentes-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-6"><?= $form->field($model, 'contri_nombres')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-6"><?= $form->field($model, 'contri_apellidocasada')->textInput(['maxlength' => true]) ?></div>
    </div>
    <div class="row">
        <div class="col-md-6"> <?= $form->field($model, 'contri_paterno')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-6"> <?= $form->field($model, 'contri_materno')->textInput(['maxlength' => true]) ?></div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'contri_ci')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'ext_id')->dropDownList(yii\helpers\ArrayHelper::map($listaCiudad, 'ext_id', 'ext_nombre'), ['prompt' => '*Seleccione una ciudad*']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3"> <?= $form->field($model, 'contri_estadocivil')->dropDownList(['CA' => 'CASADO', 'SO' => 'SOLTERO', 'VI' => 'VIUDO', 'SO' => 'SOLTERO', 'DI' => 'DIVORCIADO']) ?></div>
        <div class="col-md-3">
            <?= $form->field($model, 'contri_sexo')->dropDownList(['M' => 'MASCULINO', 'F' => 'FEMENINO']) ?></div>
        <div class="col-md-6">
            <?= $form->field($model, 'contri_fechanac')->widget(
                DatePicker::classname(),
                [
                    'pluginOptions' => [
                        'format' => 'yyyy-mm-dd'
                    ],
                ]
            );
            ?>
        </div>

    </div>
    <?= $form->field($model, 'contri_direccion')->textarea(['maxlength' => true, 'rows' => 2]) ?>
    <div class="row">
        <div class="col-md-6"><?= $form->field($model, 'contri_telefono')->textInput(['type' => 'text', 'maxlength' => 8]) ?></div>
        <div class="col-md-6"><?= $form->field($model, 'contri_nit')->textInput() ?></div>
    </div>

    <?= $form->field($model, 'sindi_id')->dropDownList(yii\helpers\ArrayHelper::map($listaSindicatos, 'sindi_id', 'sindi_nombre'), ['prompt' => '*Seleccione un sindicato*']) ?>


    <?php if (!Yii::$app->request->isAjax) { ?>
        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    <?php } ?>

    <?php ActiveForm::end(); ?>

</div>