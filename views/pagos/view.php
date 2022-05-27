<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Pagos */
?>
<div class="pagos-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
           // 'pago_id',
             'pago_nro_liquidacion',
             'pago_nro_comprobante',
           
            'graderiaSilla.grad_codigo',
           // 'grad_id',
            //'contri_id',
            [
                'attribute' => 'contri_id',
                'value' => $model->contribuyente->nombreCompletoContribuyente
            ],
                        
            'pago_longitud_modificada',            
            'pago_importe_patente',
            'pago_aseo',
            'pago_reposicion',           
            'pago_importe_total',
            'pago_observaciones',
            [           
                 'attribute'=>'pago_con_exencion',
                 'value'=> ($model->pago_con_exencion)?"Si": "No"                
             ],
            //'pago_fecha_hora',
            //'pago_anulado',
            'pago_anulado_detalle',
            'pago_anulado_fecha_hora',
            //'pago_preliquidacion',
            //'pago_id_user_preliquidacion',
            [
                'attribute' => 'pago_id_user_preliquidacion',
                'value' => (new app\models\Usuario())->nombreCompletoUsuario($model->pago_id_user_preliquidacion)
            ],
            'pago_fecha_hora_preliquidacion',
            
            [
                'attribute' => 'usua_id',
                'value' => (new app\models\Usuario())->nombreCompletoUsuario($model->usua_id)
            ],
            'pago_fecha_hora_cobro',
           // 'pago_estado',
        ],
    ]) ?>

</div>
