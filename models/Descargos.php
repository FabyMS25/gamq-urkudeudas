<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "descargos".
 *
 * @property integer $desc_id
 * @property integer $usua_id
 * @property integer $razon_id
 * @property integer $desc_nro_comprobante
 * @property string $desc_responsable
 * @property string $desc_fecha_hora
 * @property integer $desc_anulado
 * @property string $desc_anulado_justificacion
 * @property string $desc_anulado_fecha_hora
 * @property integer $desc_impreso
 * @property integer $desc_estado
 *
 * @property RazonSociales $razon
 * @property Usuario $usua
 * @property DetalleDescargos[] $detalleDescargos
 */
class Descargos extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'descargos';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['usua_id', 'razon_id', 'desc_nro_comprobante', 'desc_responsable', 'desc_fecha_hora', 'desc_estado'], 'required'],
            [['usua_id', 'razon_id', 'desc_nro_comprobante', 'desc_anulado', 'desc_impreso', 'desc_estado'], 'integer'],
            [['desc_fecha_hora', 'desc_anulado_fecha_hora'], 'safe'],
            [['desc_responsable'], 'string', 'max' => 250],
            [['desc_anulado_justificacion'], 'string', 'max' => 120],
            [['razon_id'], 'exist', 'skipOnError' => true, 'targetClass' => RazonSociales::className(), 'targetAttribute' => ['razon_id' => 'razon_id']],
            [['usua_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::className(), 'targetAttribute' => ['usua_id' => 'usua_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'desc_id' => 'Desc ID',
            'usua_id' => 'Usuario',
            'razon_id' => 'Razon social',
            'desc_nro_comprobante' => 'Nro comprobante',
            'desc_responsable' => 'Responsable',
            'desc_fecha_hora' => 'Fecha hora descargo',
            'desc_anulado' => 'Anulado',
            'desc_anulado_justificacion' => 'Justificacion',
            'desc_anulado_fecha_hora' => 'Fecha hora anulado',
            'desc_impreso' => 'Cantidad de boletas',
            'desc_estado' => ' Estado',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRazon()
    {
        return $this->hasOne(RazonSociales::className(), ['razon_id' => 'razon_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsua()
    {
        return $this->hasOne(Usuario::className(), ['usua_id' => 'usua_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDetalleDescargos()
    {
        return $this->hasMany(DetalleDescargos::className(), ['desc_id' => 'desc_id']);
    }

    public function listaResponsables() {
        return $this->find()->where(['desc_estado'=>1])
            ->orderBy('desc_estado ASC')->all();
    }
    
}
