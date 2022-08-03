<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Descargos;

/* @var $this yii\web\View */
/* @var $model app\models\Descargos */
/* @var $form yii\widgets\ActiveForm */
$listaResponsablesDesc = (new Descargos())->listaResponsables();
$items = ArrayHelper::map($listaResponsablesDesc, 'desc_id', 'desc_responsable');

?>

<div class="descargos-form">

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'desc_id')->dropDownList($items, ['prompt'=>'** Seleccione una opcion **']) ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'detalle_precio')->textInput() ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'detalle_nro_inicio')->textInput() ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'detalle_nro_limite')->textInput() ?>
            </div>
            <div class="col-md-6">
                <?= 
                    $form->field($model, 'detalle_cantidad')->textInput([
                        'readonly' => true,
                        'type'    =>'number',
                        //'onchange'=>"alert('test')",
                        //'onchange' => 'calcularCantidad()',
                        //'onkeyup' => 'calcularCantidad();' 
                    ])        
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'detalle_importe_bs')->textInput(['readonly' => true]) ?> 
            </div>
            <div class="col-md-6">
            </div>
        </div>  
  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end(); ?>
    
</div>


<script type="text/javascript">
    function calcularCantidad() {
        var inicio = $("#<?= Html::getInputId($model, 'detalle_nro_inicio') ?>").val();
        var limite = $("#<?= Html::getInputId($model, 'detalle_nro_limite') ?>").val();
        var totalCant = 0;

        console.log('dsd es =>', inicio);
        alert('hola');

        if (inicio > 0 && limite > 0) {
            //$.post("index.php?r=generador%2Findex",
            //function (data) {
                t = limite - inicio;
                totalCant = parseFloat(t); //(limite) - parseInt(inicio);
                
                $("#<?= Html::getInputId($model, 'detalle_cantidad') ?>").val(totalCant);
            //}      
            //); 
        } else {
            //$("#<?= Html::getInputId($model, 'detalle_nro_inicio') ?>").val(null);
            //$("#<?= Html::getInputId($model, 'detalle_nro_limite') ?>").val(null);
            $("#<?= Html::getInputId($model, 'detalle_cantidad') ?>").val(totalCant);
        }
    }

    $(document).ready(function () {
        $("form").keypress(function (e) {
            var codigoTecla = parseInt(e.keyCode);
            if (codigoTecla === 13) {
                return false;
            }
        });
    });
</script>