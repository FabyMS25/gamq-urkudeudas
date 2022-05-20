<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\color\ColorInput;

/* @var $this yii\web\View */
/* @var $model app\models\Zonas */
/* @var $form yii\widgets\ActiveForm */
$gestion_valida = app\models\Gestiones::find()->where(['gest_estado' => 1, 'gest_vigente' => 1])->all();
$listaGestion = yii\helpers\ArrayHelper::map($gestion_valida, 'gest_id', 'gest_nombre')
?>

<div class="zonas-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'gest_id')->dropDownList($listaGestion) ?>

<?= $form->field($model, 'zona_nombre')->textInput(['maxlength' => true]) ?>

    <div class="row">
        <div class="col-md-6">
<?= $form->field($model, 'zona_color')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?=
            $form->field($model, 'zona_color_hexadecimal')->widget(ColorInput::classname(), [
                'options' => ['placeholder' => 'Seleccione un color ...',
                    'readonly' => true],
            ]);
            ?>
        </div>
    </div>
    
    <?= $form->field($model, 'zona_descripcion')->textarea(['maxlength' => true, 'rows' => 3]) ?>  

    <?php if (!Yii::$app->request->isAjax) { ?>
        <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
<?php } ?>

<?php ActiveForm::end(); ?>

</div>
