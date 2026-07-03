<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
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
$datoModel = $modelSitiosEventuales->findOne($model->sitios_id);

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
$comprobanteCosto = Yii::$app->params['costos']['comprobante'];
$fieldIds = [
    'activiId' => Html::getInputId($model, 'activi_id'),
    'aseo' => Html::getInputId($model, 'aseo'),
    'categoria' => Html::getInputId($model, 'categoria'),
    'costoAseo' => Html::getInputId($model, 'eventual_costo_aseo'),
    'costoComprobante' => Html::getInputId($model, 'eventual_costo_comprobante'),
    'costoSentaje' => Html::getInputId($model, 'eventual_costo_sentaje'),
    'cantidadDia' => Html::getInputId($model, 'eventual_cantidad_dia'),
    'cantidadSitio' => Html::getInputId($model, 'eventual_cantidad_sitio'),
    'importePatente' => Html::getInputId($model, 'eventual_importe_patente'),
    'importeTotal' => Html::getInputId($model, 'eventual_importe_total'),
    'patente' => Html::getInputId($model, 'patente'),
    'rangoFechas' => Html::getInputId($model, 'rango_fechas'),
    'sentaje' => Html::getInputId($model, 'sentaje'),
];
$ajaxUrls = [
    'actividadPrecios' => Url::to(['actividades-economicas/ajax-actividad-precios']),
    'actividadTipos' => Url::to(['actividades-economicas/ajax-tipos']),
    'sindicato' => Url::to(['sindicatos/ajax-sindicato']),
];

//echo count($listaSitiosLibresModel);
?>


<div class="pagos-eventuales-form">


    <?php $form = ActiveForm::begin(); ?>

    <div class="row alert-info">
        <div class="col-md-3 col-sm-3">
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
    ])
    ?>

    <?=
    $form->field($model, 'activi_id')->dropDownList($listaActividades, [
        'prompt' => '* Seleccione una opcion *',
    ])
    ?>


    <div class="row">
        <div class="col-md-6 col-sm-6">
            <?= $form->field($model, 'rango_fechas', [
                'addon' => [
                    'prepend' => [
                        'content' => '<i class="glyphicon glyphicon-calendar"></i>',
                    ],
                ],
                'options' => ['class' => 'drp-container mb-2'],
            ])->widget(DateRangePicker::class, [
                'useWithAddon' => true,
                'convertFormat' => true,
                'readonly' => true,
                'pluginOptions' => [
                    'locale' => [
                        'format' => 'Y-m-d',
                        'separator' => ' a ',
                    ],
                ],
                'options' => [
                    'class' => 'form-control',
                    'readonly' => true,
                    'onchange' => 'calcDia();',
                ],
            ]) ?>
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
    var eventualFieldIds = <?= Json::htmlEncode($fieldIds) ?>;
    var eventualAjaxUrls = <?= Json::htmlEncode($ajaxUrls) ?>;
    var comprobanteCosto = <?= Json::htmlEncode((float)$comprobanteCosto) ?>;
    var key = 0;

    function eventualField(name) {
        return $("#" + eventualFieldIds[name]);
    }

    function eventualNumber(name) {
        var value = parseFloat(eventualField(name).val());
        return isNaN(value) ? 0 : value;
    }

    function setEventualField(name, value) {
        eventualField(name).val(value);
    }

    function eventualUrlWithId(url, id) {
        var separator = url.indexOf('?') === -1 ? '?' : '&';
        return url + separator + 'id=' + encodeURIComponent(id);
    }

    function habilitarInhabilitarActividadEconomica(id) {
        if (id > 0) {
            $.post(eventualUrlWithId(eventualAjaxUrls.actividadTipos, id),
                function(data) {
                    eventualField('activiId').html(data);
                    limpiarCalculosEventuales();
                }
            );
        } else {
            eventualField('activiId').html("<option value=''>* Seleccione una opcion *</option>");
            limpiarCalculosEventuales();
        };
    }

    function limpiarCalculosEventuales() {
        setEventualField('patente', 0);
        setEventualField('sentaje', 0);
        setEventualField('aseo', 0);
        setEventualField('cantidadDia', '');
        setEventualField('cantidadSitio', '');
        setEventualField('importePatente', '');
        setEventualField('costoSentaje', '');
        setEventualField('costoAseo', '');
        setEventualField('importeTotal', '');
    }

    function cargarPreciosActividad(id) {
        setEventualField('cantidadSitio', 1);
        setEventualField('cantidadDia', 3);

        if (!(id > 0)) {
            limpiarCalculosEventuales();
            return;
        }

        $.post(eventualUrlWithId(eventualAjaxUrls.actividadPrecios, id), function(data) {
            var lista = String(data).split(" - ");
            var patente = parseFloat(lista[0]) || 0;
            var sentaje = parseFloat(lista[1]) || 0;
            var aseo = parseFloat(lista[2]) || 0;

            setEventualField('patente', patente);
            setEventualField('sentaje', sentaje);
            setEventualField('aseo', aseo);
            recalcularEventual();
        });
    }

    function recalcularEventual() {
        var cantidadSitio = eventualNumber('cantidadSitio') || 1;
        var cantidadDias = eventualNumber('cantidadDia') || 3;
        var patente = eventualNumber('patente');
        var sentaje = eventualNumber('sentaje');
        var aseo = eventualNumber('aseo');
        var importePatente = cantidadSitio * patente;
        var totalSentaje = sentaje;
        var totalAseo = aseo * cantidadDias;
        var totalImporte = importePatente + totalSentaje + totalAseo + comprobanteCosto;

        setEventualField('importePatente', importePatente.toFixed(2));
        setEventualField('costoSentaje', totalSentaje.toFixed(2));
        setEventualField('costoAseo', totalAseo.toFixed(2));
        setEventualField('importeTotal', totalImporte.toFixed(2));
    }

    function anular(e) {
        if ((e.keyCode == 38) || (e.keyCode == 40)) {
            key = e.keyCode;
            //calcPuestos();
            console.log('Flechas', e);
        } else {
            console.log('Otras', e);
            key = e.keyCode;
            e.key = '';
        }
        return e;
    }

    function bloquear(e) {
        console.log('evento entrar =>', e);
        if ((e.keyCode == 38) || (e.keyCode == 40)) {
            return true;
        } else {
            return false;
        }
    }

    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        console.log('Otras', charCode);
        if (charCode == 38 || charCode == 40) {
            return true;
        }
        return false;
    }

    function calcPuestos() {
        recalcularEventual();
    }


    function calcDia() {
        var cad = eventualField('rangoFechas').val();
        var arre = cad.split(' a ');

        if (arre.length !== 2) {
            alert('debe Elegir la actividad Economica y  ')
            return;
        }

        var f1 = new Date(arre[0].trim());
        var f2 = new Date(arre[1].trim());
        var dif = f2 - f1;
        var dias = Math.floor(dif / 86400000) + 1;

        if (dias > 0) {
            setEventualField('cantidadDia', dias);
            recalcularEventual();
        }
    }

    function sindicatoComprador(idContribuyente) {
        if (idContribuyente > 0) {
            $.post(eventualUrlWithId(eventualAjaxUrls.sindicato, idContribuyente),
                function(data) {
                    $("#txt_sindicato").text(data);
                }
            );
        }

    }

    $(document).ready(function() {
        eventualField('categoria')
            .off('change.eventual')
            .on('change.eventual', function() {
                habilitarInhabilitarActividadEconomica($(this).val());
            });

        eventualField('activiId')
            .off('change.eventual')
            .on('change.eventual', function() {
                cargarPreciosActividad($(this).val());
            });

        $("form").keypress(function(e) {
            var codigoTecla = parseInt(e.keyCode);
            if (codigoTecla === 13) {
                return false;
            }
        });
    });
</script>
