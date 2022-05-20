<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\PagosEventuales */
?>
<div class="pagos-eventuales-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
          //  'eventual_id',
           'eventual_nro_liquidacion',
            'eventual_nro_comprobante',
            [
                'attribute' => 'contri_id',
                'value' => $model->contribuyente->nombreCompletoContribuyente
            ],
            [
                'attribute' => 'sitios_id',
                'value' => ($model->sitios_id>0)? $model->sitio->sitios_codigo ." (N° puesto: ".$model->sitio->sitios_numero_sitio.")":null
            ],
           // 'sitios_id',
           [
                'attribute' => 'activi_id',
                'value' => $model->actividad->activi_descripcion
            ], 
             [
                'label' => 'Rango de fechas',
                'value' => $model->eventual_fecha_inicio . " a ". $model->eventual_fecha_limite
            ],
            //'eventual_fecha_inicio',
            //'eventual_fecha_limite',
            'eventual_cantidad_dia',
            'eventual_cantidad_sitio',
            'eventual_importe_patente',
            'eventual_costo_comprobante',
            'eventual_costo_sentaje',
            'eventual_costo_aseo',
            'eventual_importe_total',
            //'eventual_anulado',
            'eventual_anulado_detalle',
            'eventual_anulado_fecha_hora',
            //'eventual_preliquidacion',
            //'eventual_user_id_preliquidacion',
             [
                'attribute' => 'eventual_user_id_preliquidacion',
                'value' => (new app\models\Usuario())->nombreCompletoUsuario($model->eventual_user_id_preliquidacion)
            ],
            //'usua_id',
             [
                'attribute' => 'usua_id',
                'value' => (new app\models\Usuario())->nombreCompletoUsuario($model->usua_id)
            ],
            'eventual_fecha_hora_pago',
            'eventual_estado',
            
            
        ],
    ]) ?>

</div>
