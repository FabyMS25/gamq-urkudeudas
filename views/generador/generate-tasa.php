<?php

use yii\helpers\Html;
//use yii\widgets\ActiveForm;
use app\models\SitiosEventuales;
use yii\helpers\ArrayHelper;
use vova07\select2\Widget;
use kartik\daterange\DateRangePicker;
use kartik\form\ActiveForm;

/* * ************************* */
if (Yii::$app->user->isGuest) {
    Yii::$app->user->logout(true);
    Yii::$app()->session->clear();
    return $this->goHome();
}
//datos del sitio
$modelSitiosEventuales = new SitiosEventuales();

// contribuyentes
$modelContribuyente = new app\models\Contribuyentes();
$listaModelContri = $modelContribuyente->ListaContribuyentesModel();
$listaContribuyentes = ArrayHelper::map($listaModelContri, 'contri_id', 'nombreCompletoCiContribuyente');
// Categoria de eventuales
$modelCategoria = (new app\models\Categorias());
$listaCategorias = ArrayHelper::map($modelCategoria->listaCategoriasModelCodigo('EVENTUAL'), 'categ_id', 'categ_nombre');
// actividades economicas

$modelActividadesEconomicas = new \app\models\ActividadesEconomicas();
$listaActividades = []; // ArrayHelper::map($modelActividadesEconomicas->listaActividadesEconomicasModel(), 'activi_id', 'activi_descripcion');

//echo count($listaSitiosLibresModel);
?>


<div class="">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-sm-4">
            <?= $form->field($model, 'patente')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($model, 'sentaje')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($model, 'aseo')->textInput(['readonly' => true]) ?>
        </div>
    </div>

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
        </div>
        <div class="col-md-4 col-sm-4">
            <label>Sindicato:</label>
            <div id="txt_sindicato"></div>
        </div>

    </div>


    <?=
    $form->field($model, 'categoria')->dropDownList($listaCategorias, [
        'prompt' => "*** Seleccione la categoria ***",
        'onchange' => 'habilitarInhabilitarActividadEconomica(this.value)'
    ])
    ?>

    <?=
    $form->field($model, 'activi_id')->dropDownList($listaActividades, [
        'prompt' => '* Seleccione una opcion *',
        //AjaxActividadPrecios
        'onchange' => '
            var id = $(this).val();            
            var a=1;
            var b=3;
            $("#' . Html::getInputId($model, 'eventual_cantidad_sitio') . '").val(a);
            $("#' . Html::getInputId($model, 'eventual_cantidad_dia') . '").val(b);                 
            if( id > 0){            
                var cantidadSitio = $("#' . Html::getInputId($model, 'eventual_cantidad_sitio') . '").val(); 
                $.post("index.php?r=actividades-economicas/ajax-actividad-precios&id="+id,
                            function(data){ 
                                lista = data.split(" - ");
                                patente = lista[0];
                                sentaje = lista[1];
                                aseo = lista[2];                               
                               
                               $("#' . Html::getInputId($model, 'patente') . '").val(patente);
                               $("#' . Html::getInputId($model, 'sentaje') . '").val(sentaje);
                               $("#' . Html::getInputId($model, 'aseo') . '").val(aseo); 
                               var impTotal=0     
                                if(cantidadSitio > 0){
                                    importeTotalPatente = cantidadSitio * patente;
                                    $("#' . Html::getInputId($model, 'eventual_importe_patente') . '").val(importeTotalPatente);  
                                    $("#' . Html::getInputId($model, 'eventual_costo_sentaje') . '").val(sentaje);
                                    $("#' . Html::getInputId($model, 'eventual_costo_aseo') . '").val(aseo*b); 
                                    impTotal= parseFloat(importeTotalPatente)+parseFloat(sentaje)+parseFloat(aseo*b)+10.5;
                                    $("#' . Html::getInputId($model, 'eventual_importe_total') . '").val(impTotal); 
                                }
                            }
                        );
                    }'
    ])
    ?>


    <div class="row">
        <div class="col-md-6 col-sm-6">
            <?php
            echo $form->field($model, 'rango_fechas', [
                'addon' => ['prepend' => ['content' => '<i class="glyphicon glyphicon-calendar"></i>']],
                'options' => ['class' => 'drp-container mb-2'],

            ])->widget(
                DateRangePicker::classname(),
                [
                    'useWithAddon' => true,
                    'value' => '2023-08-14 a 2023-08-16',
                    'convertFormat' => true,
                    'readonly' => true,
                    //'disabled' => true, 
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d',
                            'separator' => ' a ',
                        ]
                    ],
                    'options' => [
                        'class' => 'form-control',
                        'onchange' => 'calcDia();'
                    ]

                ]
            );
            ?>
        </div>

        <div class="col-md-3 col-sm-3">
            <?=
            $form->field($model, 'eventual_cantidad_dia')->textInput(
                [
                    'readonly' => true,
                ]
            )
            ?>
        </div>

        <div class="col-md-3 col-sm-3">
            <?=
            $form->field($model, 'eventual_cantidad_sitio')->textInput([
                'readonly' => true,
                'type'    => 'number',
                'min'     => 1,
                'max'     => 100,
                'step'    => 1,
                'onchange' => 'calcPuestos();',
                'onkeypress' => 'return isNumber(event)',

            ])
            ?>
        </div>
    </div>
    <div class="row">

        <div class="col-md-3 col-sm-3">
            <?= $form->field($model, 'eventual_importe_patente')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col-md-3 col-sm-3">
            <?= $form->field($model, 'eventual_costo_sentaje')->textInput(['readonly' => true]) ?>
        </div>
        <div class="col-md-2 col-sm-2">
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
    $(document).ready(function() {
        $("form").keypress(function(e) {
            var codigoTecla = parseInt(e.keyCode);
            if (codigoTecla === 13) {
                return false;
            }
        });
    });
</script>
