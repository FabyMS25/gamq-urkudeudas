<?php

use yii\widgets\DetailView;
use yii\widgets\ActiveForm;

/****************************/
if (Yii::$app->user->isGuest) {
    Yii::$app->user->logout(true);
    Yii::$app()->session->clear();
    return $this->goHome();
}

$idSitio = $model->sitios_id;

?>
<div class="anular-view">

    <div class="row">
        <div class="col-sm-4 col-md-4">
            <label>N° liquidacion: </label> <?=$model->eventual_nro_liquidacion ?>
        </div>
        <div class="col-sm-4 col-md-4">
            <label>Codigo puesto: </label> <?=($idSitio>0)? $model->sitio->sitios_codigo: " 0 " ?>
        </div>
        <div class="col-sm-4 col-md-4">
            <label>N° puesto: </label> <?=($idSitio>0)? $model->sitio->sitios_numero_sitio: " 0 " ?> 
        </div>
    </div>
    <?=  DetailView::widget([
        'model' => $model,
        'attributes' => [          
           
            [
               
                'label' => 'Actividad',
                'value' => $model->actividad->activi_descripcion ,
            ],
            [
                'attribute' => 'contri_id',
                'value' => $model->contribuyente->nombreCompletoContribuyente
            ],
            
            [
                'attribute' => 'rango_fechas',
                'value' => $model->eventual_fecha_inicio ." a ".$model->eventual_fecha_limite
            ], 
            
            [
                'attribute' => 'eventual_cantidad_dia dias ',
                'value' => $model->eventual_cantidad_dia ,
                
            ], 
             [
                'attribute' => 'eventual_cantidad_sitio ',
                'value' => $model->eventual_cantidad_sitio,
                
            ], 
            
           
            //'eventual_id',
            // 'usua_id',
           // 'contri_id',
           // 'sitios_id',
           // 'activi_id',
            //'eventual_fecha_hora_pago',
           // 'eventual_nro_comprobante',
            //'eventual_importe_patente',           
            //'eventual_costo_sentaje',
            //'eventual_costo_aseo',
             //'eventual_costo_comprobante',
            'eventual_importe_total',
            /*'eventual_anulado',
            'eventual_anulado_detalle',
            'eventual_anulado_fecha_hora',*/
           // 'eventual_preliquidacion',
            ['attribute'=>'eventual_user_id_preliquidacion',
                'value' => (new app\models\Usuario())->nombreCompletoUsuario($model->eventual_user_id_preliquidacion)
            ],
           // 'eventual_estado',
            
           
        ],
    ])
    ?>

</div>
    <div class="alert alert-info">
        <?php $form = ActiveForm::begin(); ?> 

            <?= $form->field($model, 'eventual_anulado_detalle')->dropDownList(['A SOLICITUD DEL CONTRIBUYENTE'=>'A SOLICITUD DEL CONTRIBUYENTE','NO SE EFECTUO EL PAGO EN EL DIA DEL REGISTRO'=>'NO SE EFECTUO EL PAGO EN EL DIA DEL REGISTRO','REGISTRO DE DATOS INCONSISTENTES'=>'REGISTRO DE DATOS INCONSISTENTES']) ?>     

            <?= $form->field($model, 'eventual_descripcion')->textInput(['maxlength' => true , 'oninput' => 'processInput(this)' ]) ?>   

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

    function processInput(inputElement) {
        var sanitizedValue = inputElement.value.replace(/[^A-Za-z0-9,.\-: ]/g, '');
        // var sanitizedValue = inputElement.value.replace(/[^\w\s]/gi, '');
        inputElement.value = sanitizedValue.toUpperCase();
    }
</script>

