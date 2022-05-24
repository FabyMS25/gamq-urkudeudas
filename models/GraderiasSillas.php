<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "graderias_sillas".
 *
 * @property integer $grad_id
 * @property integer $zona_id
 * @property integer $gest_id
 * @property string $grad_codigo
 * @property string $grad_direccion
 * @property string $grad_longitud
 * @property string $grad_acera
 * @property string $grad_tipo_armado
 * @property string $grad_tipo_sitio
 * @property integer $grad_vendido
 * @property integer $grad_estado
 *
 * @property Gestiones $gest
 * @property Zonas $zona
 * @property Pagos[] $pagos
 */
class GraderiasSillas extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'graderias_sillas';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['zona_id', 'gest_id', 'grad_vendido', 'grad_estado', 'grad_reservado'], 'integer'],
            [['grad_codigo', 'grad_direccion', 'grad_longitud', 'grad_acera', 'grad_tipo_armado', 'grad_tipo_sitio', 'grad_estado'], 'required'],
            [['grad_longitud'], 'number', 'min'=>0],
            [['grad_codigo', 'grad_tipo_sitio'], 'string', 'max' => 10],
            [['grad_direccion'], 'string', 'max' => 300],
          //  [['grad_codigo', 'gest_id'], 'unique'],
            [['grad_codigo'], 'trim'],
            [['grad_codigo'], 'filter', 'filter' => 'strtoupper'],
            [['grad_codigo'], 'unique','targetAttribute' => ['grad_codigo'], 'message'=>'El Codigo de la graderia ya existe. Por favor ingrese otro.'],          
            [['grad_acera'], 'string', 'max' => 20],
            [['grad_tipo_armado'], 'string', 'max' => 15],
            [['gest_id'], 'exist', 'skipOnError' => true, 'targetClass' => Gestiones::className(), 'targetAttribute' => ['gest_id' => 'gest_id']],
            [['zona_id'], 'exist', 'skipOnError' => true, 'targetClass' => Zonas::className(), 'targetAttribute' => ['zona_id' => 'zona_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'grad_id' => 'Grad ID',
            'zona_id' => 'Zona ', //ID
            'gest_id' => 'Gestion',//ID
            'grad_codigo' => 'Codigo',
            'grad_direccion' => 'Direccion',
            'grad_longitud' => 'Longitud',
            'grad_acera' => ' Acera',
            'grad_tipo_armado' => 'Tipo armado',
            'grad_tipo_sitio' => 'Tipo sitio',
            'grad_vendido' => 'Vendido',
            'grad_reservado' => 'Reservado',
            'grad_estado' => 'Estado',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGestion()
    {
        return $this->hasOne(Gestiones::className(), ['gest_id' => 'gest_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZona()
    {
        return $this->hasOne(Zonas::className(), ['zona_id' => 'zona_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPagos()
    {
        return $this->hasMany(Pagos::className(), ['grad_id' => 'grad_id']);
    }
}
