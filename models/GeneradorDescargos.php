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
 * @property string $detalle_fecha_entrega
 * @property string $detalle_importe_bs
 * @property integer $detalle_estado
 * @property integer $detalle_cantidad_anulado
 * @property integer $detalle_feria
 *
 * @property Descargos $desc
 */
class GeneradorDescargos extends \yii\db\ActiveRecord
{

    //public $rango_fechas;
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
            [['desc_id', 'detalle_precio', 'detalle_nro_inicio', 'detalle_nro_limite', 'detalle_cantidad',  'detalle_cantidad_anulado','detalle_fecha_entrega', 'detalle_importe_bs', 'detalle_estado'], 'required'],
            [['desc_id', 'detalle_nro_inicio', 'detalle_nro_limite', 'detalle_cantidad', 'detalle_estado', 'nro_comprobante', 'detalle_estado_pago','detalle_cantidad_anulado'], 'integer'],
            [['detalle_precio', 'detalle_importe_bs', 'nro_comprobante', 'detalle_estado_pago'], 'number'],
            [['detalle_fecha_entrega'], 'safe'],
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
            'detalle_nro_limite' => 'Nro limite',
            'detalle_cantidad' => 'Cantidad',
            'detalle_cantidad_anulado' => 'Cantidad anulados',
            'detalle_fecha_entrega' => 'Fecha entrega',
            'detalle_importe_bs' => 'Importe total Bs',
            'detalle_estado' => 'Estado',
            'nro_comprobante' => 'Comprobante',
            'detalle_feria' => 'Feria',
        ];
    }

    public function getGeneradores()
    {
        return $this->hasOne(Descargos::className(), ['desc_id' => 'desc_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    /* public function getDesc()
    {
        return $this->hasOne(Descargos::className(), ['desc_id' => 'desc_id']);
    } */
    
    public function montoTotalLiteral(){
        $montoTotal = $this->detalle_importe_bs;
        $modelAux = new NumeroALetras();
        return $modelAux->convertir($montoTotal);
    }
    
}
