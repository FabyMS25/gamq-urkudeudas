<?php
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Descargos;
use yii\helpers\Html;

$listaResponsablesDesc = (new Descargos())->listaResponsables();
$items = ArrayHelper::map($listaResponsablesDesc, 'desc_id', 'desc_responsable');
//var_dump($listaResponsablesDesc);
?>

<div class="descargos-form">

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'desc_id')->dropDownList($items, ['prompt'=>'** Seleccione una opcion **']) ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'detalle_precio')->textInput(['id' => 'detalle_precio']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'detalle_nro_inicio')->textInput(['id' => 'detalle_nro_inicio']) ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'detalle_nro_limite')->textInput(['id' => 'detalle_nro_limite', 'onchange'=>'actualizar()']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'detalle_cantidad_anulado')->textInput(['id' => 'detalle_cantidad_anulado', 'onchange'=>'actualizar()']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= 
                    $form->field($model, 'detalle_cantidad')->textInput([
                        'readonly' => true,
                        'type'    =>'number',
                        'id' => 'detalle_cantidad' 
                    ])        
                ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'detalle_importe_bs')->textInput(['readonly' => true, 'id'=>'detalle_importe_bs']) ?> 
            </div>
            <div class="col-md-6">
            </div>
        </div>  
  
	<?php if (!Yii::$app->request->isAjax){ ?>
	  	<div class="form-group">
	        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	    </div>
	<?php } ?>

    <?php ActiveForm::end();?>
    
</div>


<script type="text/javascript">

function actualizar() {           
  var precio  = document.getElementById("detalle_precio").value;
  var inicio  = document.getElementById("detalle_nro_inicio").value;
  var limite  = document.getElementById("detalle_nro_limite").value;
  var anulado  = document.getElementById("detalle_cantidad_anulado").value;
  
  if(inicio > 0 && limite > 0) { 
    var cantidad = (parseFloat(limite) - parseFloat(inicio)) + 1;
    var totalCantidad = (parseFloat(cantidad) - parseFloat(anulado));
    var totalImporte  = parseFloat(precio) * parseFloat(totalCantidad);

    //if (limite <= inicio ) {
      //  alert('NRO LIMITE NO PUEDE SER MAYOR A NRO INICIO');
        //document.getElementById("detalle_nro_inicio").setAttribute('value', '');
        //document.getElementById("detalle_nro_limite").setAttribute('value', ''); 
    //} else {
        document.getElementById("detalle_cantidad").setAttribute('value', totalCantidad);
        document.getElementById("detalle_importe_bs").setAttribute('value', totalImporte);
    //}

  }else {
        document.getElementById("detalle_cantidad").setAttribute('value', '');
        document.getElementById("detalle_importe_bs").setAttribute('value', '');  
  }

}
  
</script>