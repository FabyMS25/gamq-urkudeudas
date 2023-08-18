<?php

use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use app\models\Descargos;
use app\models\DetalleDescargos;
use app\models\RazonSociales;
use yii\helpers\ArrayHelper;
use kartik\daterange\DateRangePicker;
use vova07\select2\Widget;
use yii\helpers\Html;

//use app\models\SitiosEventuales;

/* @var $this yii\web\View */
/* @var $model app\models\Pagos */

/**********/
if (Yii::$app->user->isGuest) {
    Yii::$app->user->logout(true);
    Yii::$app()->session->clear();
    return $this->goHome();
}
//$model = new DetalleDescargos();
//$datoModel = $modelSitiosEventuales->findOne($model->desc_id);

// contribuyentes
$modelContribuyente = new app\models\Descargos();
$listaModelContri = $modelContribuyente->listaResponsables();
$listaContribuyentes = ArrayHelper::map($listaModelContri, 'desc_id', 'desc_responsable');

//$datoCont = $modelContribuyente->findOne($model->contri_id);
// actividades economicas

//$modelActividadesEconomicas = new \app\models\RazonSociales();
//$listaActividades = ArrayHelper::map($modelActividadesEconomicas->listaActividadesEconomicasAlasitasModel(), 'activi_id', 'activi_descripcion');
?>

<div class="cobrar-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row p-3">
        <div class="col-12 col-sm-8">
            <?= $form->field($model, 'desc_id')->widget(Widget::className(), [
                'options' => [
                    'prompt' => "",
                    'placeholder' => 'Elija el Sentajero...',
                    'multiple' => false,
                    'allowClear' => true,
                    'onchange' => 'getActividad($this.val());'
                ],
                'settings' => ['width' => '100%',],
                'items' => $listaContribuyentes,
            ]);
            ?>

        </div>
        <div class="col-12 col-sm-4">
            <label>Actividad :</label>
            <div id="txt_actividad"></div>
        </div>
    </div>
    <div class="alert alert-info">
        <?= $form->field($model, 'detalle_observacion')->textInput(['maxlength' => true]) ?>

        <?php if (!Yii::$app->request->isAjax) { ?>
            <div class="form-group">
                <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
            </div>
        <?php } ?>
        <?php ActiveForm::end(); ?>
    </div>

</div>


<script type="text/javascript">
    function getActividad(id) {

        if (id > 0) {
            $.post("index.php?r=razon-sociales/ajax-razon&id=" + id,
                function(data) {

                    $("#txt_actividad").text(data);
                }
            );

        }
    }



    $(document).ready(function() {
        $("form").keypress(function(e) {
            var codigoTecla = parseInt(e.keyCode);
            if (codigoTecla === 13) {
                return false;
            }
        });
    });
</script>