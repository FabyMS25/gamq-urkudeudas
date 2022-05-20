<?php
use kartik\form\ActiveForm;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Pagos */
/****************************/
if (Yii::$app->user->isGuest) {
    Yii::$app->user->logout(true);
    Yii::app()->session->clear();
    return $this->goHome();
}

?>

<div id="form-reporte_pagos">
    <?php $form = ActiveForm::begin(); ?>   
    
    <?= $form->errorSummary($model); ?>
     <?=  $form->field($model, 'tipo')->dropDownList(['PAGADOS'=>'Pagados', 'ANULADOS' => 'Anulados']);   ?>

    <?=  $form->field($model, 'fecha_rango', ['addon' => ['prepend' => ['content' => '<i class="glyphicon glyphicon-calendar"></i>']],
                'options' => ['class' => 'drp-container form-group']
            ])->widget(DateRangePicker::classname(), [
                'readonly' => true,
                'useWithAddon' => true,
                'convertFormat' => true,
                'pluginOptions' => [
                    'locale' => [
                        'format' => 'Y-m-d',
                        'separator' => ' a ',
                    ]
                ]
            ]);
    ?>

    
      <?php if (!Yii::$app->request->isAjax) { ?>
            <div class="form-group">
            <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
            </div>
            <?php } ?>
        <?php ActiveForm::end(); ?>
    </div>
    </div>