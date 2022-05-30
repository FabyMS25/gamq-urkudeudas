<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use vova07\select2\Widget;

/* @var $this yii\web\View */
/* @var $model app\models\Pagos */
/* @var $form yii\widgets\ActiveForm */
/* * ************************* */
if (Yii::$app->user->isGuest) {
    Yii::$app->user->logout(true);
    Yii::app()->session->clear();
    return $this->goHome();
}

$modelTipoArmado = new app\models\TipoArmados();
$listaModel = $modelTipoArmado->listaTipoArmadoPorZona($model->graderiaSilla->zona_id);
$listaTipoArmados = ArrayHelper::map($listaModel, 'tip_arm_id', 'tip_arm_descricpion');
// contribuyentes
$modelContribuyente = new app\models\Contribuyentes();
$listaModelContri = $modelContribuyente->ListaContribuyentesModel();
$listaContribuyentes = ArrayHelper::map($listaModelContri, 'contri_id', 'nombreCompletoCiContribuyente');
?>

<div class="preliquidar-form">
    <div class="row alert alert-info">
        <div class="col-sm-4"><label><?= $modelSitio->zona->zona_nombre; ?></label></div>
        <div class="col-sm-4" id="txt_patente">Patente Bs.: 0</div>
        <div class="col-sm-4" id="txt_aseo">Aseo Bs.: 0</div>       
    </div>    
    <?php $form = ActiveForm::begin(); ?>    

    <?=  $form->field($model, 'pago_con_exencion')->dropDownList([0 => 'No', 1 => 'Si'], [//'prompt'=>'*** Seleccione una opcion ***',
        'onchange' => 'precioGraderiasSilla(); '
    ]);
    ?>

    <div class="row">
        <div class="col-md-8 col-sm-8">
            <?=
            $form->field($model, 'contri_id')->widget(Widget::className(), [
                'options' => [
                    'prompt' => "",
                    'placeholder' => 'Elija el contribuyente...',
                    'multiple' => false,
                    'allowClear' => true,
                    'onchange' => 'sindicatoComprador($(this).val());'
                ],
                'settings' => ['width' => '100%',],
                'items' => $listaContribuyentes,
            ]);
            ?>            
        </div><div class="col-md-4 col-sm-4">
            <label>Sindicato:</label><div id="txt_sindicato"></div>                   
        </div>

    </div>


    <?=
    $form->field($model, 'tip_arm_id')->dropDownList($listaTipoArmados, [
        'prompt' => ' *** Seleccione una opcion ***',
        'onchange' => 'precioGraderiasSilla(); '
    ]);
    ?> 

    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'longitud')->textInput(['value' => $model->graderiaSilla->grad_longitud, 'readonly' => true]) ?>
        </div>
        <div class="col-sm-6">
            <?=
            $form->field($model, 'pago_longitud_modificada')->textInput([
                'value' => $model->graderiaSilla->grad_longitud,
                'type'    =>'number', 
                'min'     =>1, 
                'max'     =>10, 
                'step'    =>0.1,
                'onkeypress'=> 'return isNumber(event)',               
                'onkeyup' => 'precioGraderiasSilla()'
            ])
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-3">
            <?= $form->field($model, 'pago_importe_patente')->textInput(['readonly' => true]) ?>
        </div><div class="col-md-3 col-sm-3">
            <?= $form->field($model, 'pago_aseo')->textInput(['readonly' => true]) ?>
        </div><div class="col-md-3 col-sm-3">
<?= $form->field($model, 'pago_reposicion')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col-md-3 col-sm-3">
        <?= $form->field($model, 'pago_importe_total')->textInput(['readonly' => true]) ?>   
        </div>
    </div>
    <?php if (!Yii::$app->request->isAjax) { ?>
        <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
<?php } ?>
    
     <?= $form->field($model, 'pago_observaciones')->textarea(['rows' => 2, 'maxlength' => true,]) ?>  
   
    

<?php ActiveForm::end(); ?>
</div>






<script type="text/javascript">

function isNumber(evt) {

  evt = (evt) ? evt : window.event;

   var getNumCd = (evt.which) ? evt.which : evt.keyCode;

    if ((getNumCd==44)||(getNumCd==38)||(getNumCd==40) 
        || (getNumCd <=57 && getNumCd >= 48)){

      return true;

   }
   else
   {
    return false;
   } 
}

    function sindicatoComprador(idContribuyente) {
        if (idContribuyente > 0) {
            $.post("index.php?r=sindicatos/ajax-sindicato&id=" + idContribuyente,
                    function (data) {
                        $("#txt_sindicato").text(data);
                    }
            );
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

    function precioGraderiasSilla() {
        var longitud = $("#<?= Html::getInputId($model, 'pago_longitud_modificada') ?>").val();
        var comprobante = $("#<?= Html::getInputId($model, 'pago_reposicion') ?>").val();
        var exencion = parseInt($("#<?= Html::getInputId($model, 'pago_con_exencion') ?>").val());

        var totalPatente = 0;
        var totalAseo = 0;
        var id = $("#<?= Html::getInputId($model, 'tip_arm_id') ?>").val();
        if (longitud > 0 && id > 0 && exencion >= 0) {
            $.post("index.php?r=tipo-armados/ajax-tipo-precios&id=" + id,
                    function (data) {
                        lista = data.split(" - ");
                        patente = lista[0];
                        aseo = lista[1];

                        if (exencion === 0) {  // igual a NO
                            totalPatente = patente * longitud;                            
                            totalAseo = aseo * longitud;                            
                        }

                        totalPatente = totalPatente.toFixed(2);
                        totalAseo = totalAseo.toFixed(2);
  
                        total = parseFloat(totalPatente) + parseFloat(totalAseo) + parseFloat(comprobante);
                        total = total.toFixed(2);
                       // print( '$totalPatente - $totalAseo - $comprobante');
                        $("#txt_patente").text("Patente Bs.:" + patente);
                        $("#txt_aseo").text("Tasa de aseo Bs.:" + aseo);

                        $("#<?= Html::getInputId($model, 'pago_importe_patente') ?>").val(totalPatente);
                        $("#<?= Html::getInputId($model, 'pago_aseo') ?>").val(totalAseo);
                        $("#<?= Html::getInputId($model, 'pago_importe_total') ?>").val(total);

                    }
            );
        } else {
            $("#<?= Html::getInputId($model, 'pago_importe_patente') ?>").val(null);
            $("#<?= Html::getInputId($model, 'pago_aseo') ?>").val(null);
            $("#<?= Html::getInputId($model, 'pago_importe_total') ?>").val(null);
        }

    }


</script>
