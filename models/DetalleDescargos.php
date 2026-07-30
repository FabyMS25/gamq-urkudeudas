<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "detalle_descargos".
 *
 * @property integer $detalle_id
 * @property integer $desc_id
 * @property string $detalle_precio
 * @property integer $detalle_nro_inicio
 * @property integer $detalle_nro_limite
 * @property integer $detalle_cantidad
 * @property integer|null $detalle_cantidad_anulado
 * @property string $detalle_fecha_entrega
 * @property string $detalle_importe_bs
 * @property integer $detalle_estado
 * @property integer|null $detalle_estado_pago
 * @property string|integer|null $detalle_tasa
 * @property string|integer|null $nro_comprobante
 * @property string|null $detalle_observacion
 * @property string|null $detalle_feria
 * @property string|null $codigo_clasificador
 *
 * @property Descargos $desc
 */
class DetalleDescargos extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'detalle_descargos';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'desc_id',
                    'detalle_precio',
                    'detalle_nro_inicio',
                    'detalle_nro_limite',
                    'detalle_cantidad',
                    'detalle_fecha_entrega',
                    'detalle_importe_bs',
                    'detalle_estado',
                ],
                'required',
            ],

            [
                [
                    'desc_id',
                    'detalle_nro_inicio',
                    'detalle_nro_limite',
                    'detalle_cantidad',
                    'detalle_cantidad_anulado',
                    'detalle_estado',
                    'detalle_estado_pago',
                ],
                'integer',
            ],

            [
                [
                    'detalle_precio',
                    'detalle_importe_bs',
                ],
                'number',
            ],

            [
                [
                    'detalle_fecha_entrega',
                    'detalle_tasa',
                    'nro_comprobante',
                    'detalle_observacion',
                ],
                'safe',
            ],

            [
                ['detalle_feria'],
                'string',
                'max' => 255,
            ],

            [
                ['codigo_clasificador'],
                'string',
                'max' => 64,
            ],

            [
                ['desc_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Descargos::className(),
                'targetAttribute' => ['desc_id' => 'desc_id'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'detalle_id' => 'Detalle ID',
            'desc_id' => 'Descargo',
            'detalle_precio' => 'Precio Bs.',
            'detalle_nro_inicio' => 'Nro inicio',
            'detalle_nro_limite' => 'Nro límite',
            'detalle_cantidad' => 'Cantidad',
            'detalle_cantidad_anulado' => 'Cantidad anulados',
            'detalle_fecha_entrega' => 'Fecha entrega',
            'detalle_importe_bs' => 'Importe total Bs',
            'detalle_estado' => 'Estado',
            'detalle_estado_pago' => 'Estado de pago',
            'detalle_tasa' => 'Detalle tasa',
            'nro_comprobante' => 'Comprobante',
            'detalle_observacion' => 'Observación',
            'detalle_feria' => 'Feria',
            'codigo_clasificador' => 'Código clasificador',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDesc()
    {
        return $this->hasOne(
            Descargos::className(),
            ['desc_id' => 'desc_id']
        );
    }
}