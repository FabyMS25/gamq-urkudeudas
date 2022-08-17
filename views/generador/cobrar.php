<?php

use yii\widgets\DetailView;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Pagos */
/****************************/
if (Yii::$app->user->isGuest) {
    Yii::$app->user->logout(true);
    Yii::app()->session->clear();
    return $this->goHome();
}
?>


<div class="cobrar-view">
    <?=
    DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => 'Precio (Bs.)',
                'value' => $model->detalle_precio
            ],
            [
                'label' => 'Nro inicio',
                'value' => $model->detalle_nro_inicio
            ],
            [
                'label' => 'Nro limite',
                'value' => $model->detalle_nro_limite
            ],
            [
                'label' => 'Cantidad',
                'value' => $model->detalle_cantidad
            ],
            [
                'label' => 'Importe total (Bs)',
                'value' => $model->detalle_importe_bs
            ],
        ],
    ])
    ?>


    <div class="alert alert-info">
    <?php $form = ActiveForm::begin(); ?>    
        <?php // echo $form ->errorSummary($model); ?>    
        <?= $form->field($model, 'nro_comprobante')->textInput(['maxlength' => true]) ?>     

        <?php if (!Yii::$app->request->isAjax) { ?>
            <div class="form-group">
            <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
            </div>
            <?php } ?>
        <?php ActiveForm::end(); ?>
    </div>

</div>


<script type="text/javascript">
    $(document).ready(function () {
        $("form").keypress(function (e) {
            var codigoTecla = parseInt(e.keyCode);
            if (codigoTecla === 13) {
                return false;
            }
        });
    });
</script>