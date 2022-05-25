<?php

use yii\helpers\Html;
//use yii\widgets\ActiveForm;

use yii\helpers\ArrayHelper;
use vova07\select2\Widget;
use kartik\daterange\DateRangePicker;
use kartik\form\ActiveForm;
use app\models\SitiosEventuales;

/****************************/
if (Yii::$app->user->isGuest) {
    Yii::$app->user->logout(true);
    Yii::app()->session->clear();
    return $this->goHome();
}
/****************************/
//datos del sitio
$modelSitiosEventuales = new SitiosEventuales();
$datoModel = $modelSitiosEventuales->findOne($model->sitios_id);

// contribuyentes
$modelContribuyente = new app\models\Contribuyentes();
$listaModelContri = $modelContribuyente->ListaContribuyentesModel();
$listaContribuyentes = ArrayHelper::map($listaModelContri, 'contri_id', 'nombreCompletoCiContribuyente');

// actividades economicas

$modelActividadesEconomicas = new \app\models\ActividadesEconomicas();
$listaActividades = ArrayHelper::map($modelActividadesEconomicas->listaActividadesEconomicasAlasitasModel(), 'activi_id', 'activi_descripcion');
?>

<div class="pagos-eventuales-form">   
    <?php $form = ActiveForm::begin(); ?>
     <div class="row alert-info">
        <div class="col-md-3 col-sm-3" >
            <label>Codigo sitio </label><br><?= $datoModel->sitios_codigo; ?>
        </div>
        <div class="col-md-3 col-sm-3">
            <label>N° de puesto</label><br><?= $datoModel->sitios_numero_sitio; ?>
        </div>
        <div class="col-md-6 col-sm-6">
            <label>Ubicacion</label><br><?= $datoModel->sitios_descripcion; ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-sm-4">
            <?=  $form->field($model, 'patente')->textInput([ 'readonly'=>true]) ?>
        </div><div class="col-sm-4">
            <?=  $form->field($model, 'sentaje')->textInput([ 'readonly'=>true]) ?>
        </div><div class="col-sm-4">
            <?=  $form->field($model, 'aseo')->textInput(['readonly'=>true]) ?>
        </div>
    </div>    
    
    <div class="row">
        <div class="col-md-8 col-sm-8">
             <?=  $form->field($model, 'contri_id')->widget(Widget::className(), [
                'options' => [
                    'prompt' => "",
                    'placeholder' => 'Elija el contribuyente...',
                    'multiple' => false,
                    'allowClear' => true,           
                    'onchange' => 'sindicatoComprador($(this).val());'
                ],
                'settings' => [ 'width' => '100%',],
                'items' => $listaContribuyentes,
            ]);
            ?>            
        </div><div class="col-md-4 col-sm-4">
            <label>Sindicato:</label><div id="txt_sindicato"></div>                   
        </div>
        
    </div>
   
    
    
    

    <?= $form->field($model, 'activi_id')->dropDownList($listaActividades, ['prompt' => '* Seleccione una opcion *',
        //AjaxActividadPrecios
        'onchange' => '
            var id = $(this).val();    
            var importeTotalPatente = 0;
            if( id > 0)
                    {            
                        var cantidadSitio = $("#'.Html::getInputId($model, 'eventual_cantidad_sitio').'").val();  
                        var comprobante =  $("#'.Html::getInputId($model, 'eventual_costo_comprobante').'").val();      
                        $.post("index.php?r=actividades-economicas/ajax-actividad-precios&id="+id,
                            function(data){ 
                                lista = data.split(" - ");
                                patente = lista[0];
                                sentaje = lista[1];
                                aseo = lista[3]; // tasa de aseo por sitio
                                
                                 
                                $("#'.Html::getInputId($model, 'patente').'").val(patente);                               
                               $("#'.Html::getInputId($model, 'aseo') . '").val(aseo);  
                               
                                if(cantidadSitio > 0){
                                    importeTotalPatente = cantidadSitio * patente;
                                    importeTotalAseo = cantidadSitio * aseo;
                                    $("#'.Html::getInputId($model, 'eventual_importe_patente') . '").val(importeTotalPatente);  
                                    $("#'.Html::getInputId($model, 'eventual_costo_aseo') . '").val(importeTotalAseo); 
                                    var impTotal= parseFloat(importeTotalPatente) + parseFloat(importeTotalAseo) + parseFloat(comprobante);
                                    impTotal=impTotal.toFixed(2);
                                    $("#'.Html::getInputId($model, 'eventual_importe_total') . '").val(impTotal); 
                                }
                            }
                        );
                    }'
    ])->label("Actividad Ecoomica de Alasitas")
    ?>


    <div class="row">    
                  
         <div  class="col-md-7 col-sm-7">            
            <?php
            echo $form->field($model, 'rango_fechas', ['addon' => ['prepend' => ['content' => '<i class="glyphicon glyphicon-calendar"></i>']],
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
                    ],
                    'options' => [  'class'=>'form-control',
                    'onchange' => 'calcDia();' ]   
            ]);
            ?>
        </div> 
        <div class="col-md-3 col-sm-3">
            <?=
            $form->field($model, 'eventual_cantidad_sitio')->textInput([
                'value'   => 1,
                'readonly' => true,
                'type'    =>'number', 
                'min'     =>1, 
                'max'     =>10, 
                'step'    =>1,
                'onkeypress'=> 'return isNumber(event)',
                'onchange' => 'preciosAlasitas()'
            ])
            ?>
        </div>

    </div>
    <div class="row">

        <div class="col-md-3 col-sm-3">
            <?= $form->field($model, 'eventual_importe_patente')->textInput(['readonly' => true]) ?>
        </div><div class="col-md-3 col-sm-3">
            <?= $form->field($model, 'eventual_costo_sentaje')->textInput(['readonly' => true]) ?>
        </div><div class="col-md-2 col-sm-2">
            <?= $form->field($model, 'eventual_costo_aseo')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col-md-2 col-sm-2">
            <?= $form->field($model, 'eventual_costo_comprobante')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col-md-2 col-sm-2">
            <?= $form->field($model, 'eventual_importe_total')->textInput(['readonly' => true]) ?>   
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

function calcDia()
    {
        var cad = $("#<?= Html::getInputId($model, 'rango_fechas') ?>").val();
        console.log('cad=> ',cad);
        let arre=cad.split(' a ');
        f1= new Date(arre[0].trim());
        f2= new Date(arre[1].trim());
        dif=f2-f1;
        var dias = (dif/86400).toFixed()/1000;
        dias++;
        
        console.log('dias es : ', dias);
        preciosAlasitas();
    }    

function preciosAlasitas()
{  
                  var cantidad =  $("#<?= Html::getInputId($model, 'eventual_cantidad_sitio') ?> ").val();
                    var patente = $("#<?= Html::getInputId($model, 'patente') ?> ").val();
                    var aseo = $("#<?= Html::getInputId($model, 'aseo') ?> ").val();
                    var comprobante =  $("#<?= Html::getInputId($model, 'eventual_costo_comprobante') ?> ").val();
                    var totalImporte = 0;
                   
                    if( cantidad > 0 && patente > 0){ 
                    
                        importeTotalPatente = cantidad * patente;
                        importeTotalAseo = cantidad * aseo;
                        totalImporte = parseFloat(importeTotalPatente) + parseFloat(importeTotalAseo) + parseFloat(comprobante);
                        totalImporte = totalImporte.toFixed(2);
                        $("#<?= Html::getInputId($model, 'eventual_importe_patente') ?> ").val(importeTotalPatente);
                        $("#<?= Html::getInputId($model, 'eventual_costo_aseo') ?> ").val(importeTotalAseo);
                        $("#<?= Html::getInputId($model, 'eventual_importe_total') ?> ").val(totalImporte);
                                                       
                    }

}
    
function sindicatoComprador(idContribuyente){                         
        if( idContribuyente> 0){ 
            $.post("index.php?r=sindicatos/ajax-sindicato&id="+idContribuyente,
                function(data){ 
                    $("#txt_sindicato").text(data);
                }
            );
        }  
        
    }
    
    
    
$(document).ready(function() {
    $("form").keypress(function(e) {
        var codigoTecla = parseInt(e.keyCode);    
    if(codigoTecla === 13){        
        return false;        
    }
    });
});



</script>

