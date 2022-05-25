<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use vova07\select2\Widget;
use kartik\daterange\DateRangePicker;
use kartik\form\ActiveForm;
//use app\models\SitiosEventuales;

/****************************/
if (Yii::$app->user->isGuest) {
    Yii::$app->user->logout(true);
    Yii::app()->session->clear();
    return $this->goHome();
}
// contribuyentes
$modelContribuyente = new app\models\Contribuyentes();
$listaModelContri = $modelContribuyente->ListaContribuyentesModel();
$listaContribuyentes = ArrayHelper::map($listaModelContri, 'contri_id', 'nombreCompletoCiContribuyente');

// actividades economicas

$modelActividadesEconomicas = new \app\models\ActividadesEconomicas();
$listaActividades = ArrayHelper::map($modelActividadesEconomicas->listaActividadesEconomicasEspectaculosModel(), 'activi_id', 'activi_descripcion');
?>

<div class="pagos-eventuales-form">
    <?php $form = ActiveForm::begin(); ?>
    
     <?php // echo $form ->errorSummary($model); ?>
    
    <div class="row">        
        <div class="col-sm-4" id="txt_patente">Patente Bs.: 0</div>
        <div class="col-sm-4" id="txt_sentaje">Sentaje Bs.: 0</div>
        <div class="col-sm-4" id="txt_aseo">Aseo Bs.: 0</div>
        <input type="hidden" value="" id="precio_patente" />
        <input type="hidden" value="" id="precio_sentaje" />
        <input type="hidden" value="" id="precio_aseo" />             
    </div>
    <br>
    
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
    
    
    
     <?=  $form->field($model, 'activi_id')->widget(Widget::className(), [
        'options' => [
            'placeholder' => 'Elija una opcion ...',
            'multiple' => false,
            'allowClear' => true,
            'prompt' => "",
            'onchange' => 'preciosActividadesEconomicasEspectaculos(); '
        ],
        'settings' => [ 'width' => '100%',],
        'items' => $listaActividades,
    ])->label("Actividad Economica de Espectaculos");
    ?>

    
    <div class="row">            
            <div  class="col-md-7 col-sm-7">            
            <?php  echo $form->field($model, 'rango_fechas', ['addon' => ['prepend' => ['content' => '<i class="glyphicon glyphicon-calendar"></i>']],
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
            $form->field($model, 'eventual_cantidad_dia')->textInput([
                'value'=>1,
                'readonly' => true
                
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
    
    function sindicatoComprador(idContribuyente){                         
        if( idContribuyente> 0){ 
            $.post("index.php?r=sindicatos/ajax-sindicato&id="+idContribuyente,
                function(data){ 
                    $("#txt_sindicato").text(data);
                }
            );
        }      
        
    }
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
        $("#<?= Html::getInputId($model, 'eventual_cantidad_dia') ?> ").val(dias);
        console.log('dias es : ', dias);
        preciosActividadesEconomicasEspectaculos();  
    }    

    function preciosActividadesEconomicasEspectaculos(){
        // funcion para mostrar precios de actividades economicas
        var id = $("#<?= Html::getInputId($model, 'activi_id')?>").val();               
        if( id > 0){             
            var cantidadDia = $("#<?= Html::getInputId($model, 'eventual_cantidad_dia')?>").val();  
            var comprobante = $("#<?= Html::getInputId($model, 'eventual_costo_comprobante')?>").val(); 
            
            $.post("index.php?r=actividades-economicas/ajax-actividad-precios&id="+id,
                function(data){ 
                    lista = data.split(" - ");
                    patente = lista[0];
                    sentaje = lista[1];
                    aseo = lista[2]; // tasa de aseo por sitio
                                
                    $("#txt_patente").text("Patente Bs.:" + patente);
                    $("#txt_sentaje").text("Sentaje Bs.:" + sentaje);
                    $("#txt_aseo").text("Tasa de aseo Bs.:" + aseo);  
                    $("#precio_patente").val(patente);                               
                    $("#precio_aseo").val(aseo);  
                
                    if(cantidadDia > 0){
                        totalPatente = cantidadDia * patente;
                        totalAseo = cantidadDia * aseo;
                        totalImporte = parseFloat(totalPatente) + parseFloat(totalAseo) + parseFloat(comprobante);
                        totalImporte = totalImporte.toFixed(2);   
                        
                        $("#<?=Html::getInputId($model, 'eventual_importe_patente') ?>").val(totalPatente);  
                        $("#<?= Html::getInputId($model, 'eventual_costo_aseo') ?>").val(totalAseo); 
                        $("#<?= Html::getInputId($model, 'eventual_importe_total') ?> ").val(totalImporte);   
                    }
                }
            );
        }else{
            $("#<?=Html::getInputId($model, 'eventual_importe_patente') ?>").val(null);  
            $("#<?= Html::getInputId($model, 'eventual_costo_aseo') ?>").val(null); 
            $("#<?= Html::getInputId($model, 'eventual_importe_total') ?> ").val(null);  
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

