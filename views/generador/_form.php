<?php

use yii\helpers\Html;
use app\models\Descargos;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

$listaResponsablesDesc = (new Descargos())->listaResponsables();
$items = ArrayHelper::map($listaResponsablesDesc, 'desc_id', 'desc_responsable');
$sentajeroCantidad = 10;
?>

<div class="descargos-form">
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'desc_id')->dropDownList(
        $items,
        [
            'prompt' => '** Seleccione una opcion **',
            'class' => 'form-control',
        ]
    ) ?>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'detalle_precio')->textInput([
                'id' => 'detalle_precio',
                'type' => 'number',
                'min' => 0,
                'onchange' => 'onchangeValues()'
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'detalle_nro_inicio')->textInput([
                'id' => 'detalle_nro_inicio',
                'type' => 'number',
                'min' => 0,
                'onchange' => 'onchangeValues()'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'detalle_nro_limite')->textInput([
                'id' => 'detalle_nro_limite',
                'type' => 'number',
                'min' => 0,
                'onchange' => 'onchangeValues()'
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'detalle_cantidad_anulado')->textInput([
                'id' => 'detalle_cantidad_anulado',
                'type' => 'number',
                'min' => 0,
                'onchange' => 'onchangeValues()'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'detalle_cantidad')->textInput([
                'readonly' => true,
                'type' => 'number',
                'id' => 'detalle_cantidad'
            ])
            ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'detalle_importe_bs')->textInput([
                'readonly' => true,
                'type' => 'number',
                'id' => 'detalle_importe_bs'
            ]) ?>
        </div>
        <div class="col-md-6">
        </div>
    </div>

    <?php if (!Yii::$app->request->isAjax) { ?>
        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    <?php } ?>

    <?php ActiveForm::end(); ?>

</div>


<script type="text/javascript">
    function onchangeValues() {
        var precio = document.getElementById("detalle_precio").value;
        var inicio = document.getElementById("detalle_nro_inicio").value;
        var limite = document.getElementById("detalle_nro_limite").value;
        var anulado = document.getElementById("detalle_cantidad_anulado").value;
        if (precio >= 0 && inicio > 0 && limite > 0 && anulado >= 0) {
            if (limite >= inicio) {
                var cantidad = (parseFloat(limite) - parseFloat(inicio)) + 1;
                var totalCantidad = (parseFloat(cantidad) - parseFloat(anulado));
                var totalImporte = parseFloat(precio) * parseFloat(totalCantidad);
                var sentajeroCantidad = "<?php echo $sentajeroCantidad; ?>";
                document.getElementById("detalle_cantidad").setAttribute('value', totalCantidad);
                document.getElementById("detalle_importe_bs").setAttribute('value', totalImporte);

            } else {
                alert('El  Nro limite no puede ser menor al Nro inicio');
                document.getElementById("detalle_cantidad_anulado").setAttribute('value', null);
                document.getElementById("detalle_cantidad").setAttribute('value', null);
                document.getElementById("detalle_importe_bs").setAttribute('value', null);
            }
        } else {
            document.getElementById("detalle_cantidad").setAttribute('value', null);
            document.getElementById("detalle_importe_bs").setAttribute('value', null);
        }
    }

    function formularioValido() {
        let res = false;
        var precio = document.getElementById("detalle_precio").value;
        var inicio = document.getElementById("detalle_nro_inicio").value;
        var limite = document.getElementById("detalle_nro_limite").value;
        var anulado = document.getElementById("detalle_cantidad_anulado").value;
    }
</script>