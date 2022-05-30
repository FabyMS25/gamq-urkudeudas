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
            'pago_nro_liquidacion',
            'graderiaSilla.grad_codigo',
            // 'usua_id',
            [
                'attribute' => 'contri_id',
                'value' => $model->contribuyente->nombreCompletoContribuyente
            ],
             
            //'contri_id',
            // 'pago_codigo_control',
            [
                'label' => 'Longitud (metros lineales)',
                'value' => $model->pago_longitud_modificada
            ],
            
            //'pago_nro_comprobante',
            //'pago_descuento_porcentaje',
            'pago_importe_patente',
            'pago_aseo',
            'pago_reposicion',           
            'pago_importe_total',
            // 'pago_anulado',
            //'pago_anulado_detalle',
            //'pago_anulado_fecha_hora',
            //'pago_preliquidacion',
            //'pago_id_user_preliquidacion',
            [
                'attribute' => 'pago_id_user_preliquidacion',
                'value' => (new app\models\Usuario())->nombreCompletoUsuario($model->pago_id_user_preliquidacion)
            ],
            'pago_fecha_hora_preliquidacion',
           // 'pago_estado',
        ],
    ])
    ?>


    <div class="alert alert-info">
    <?php $form = ActiveForm::begin(); ?>    
        <?php // echo $form ->errorSummary($model); ?>    
        <?= $form->field($model, 'pago_nro_comprobante')->textInput(['maxlength' => true]) ?>     

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