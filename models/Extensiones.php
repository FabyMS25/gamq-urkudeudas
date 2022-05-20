<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "extensiones".
 *
 * @property integer $ext_id
 * @property string $ext_nombre
 * @property string $ext_abreviado
 * @property integer $ext_estado
 *
 * @property Contribuyentes[] $contribuyentes
 */
class Extensiones extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'extensiones';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ext_nombre', 'ext_abreviado', 'ext_estado'], 'required'],
            [['ext_estado'], 'integer'],
            [['ext_nombre'], 'string', 'max' => 12],
            [['ext_abreviado'], 'string', 'max' => 5],
            [['ext_nombre'], 'unique', 'targetAttribute' => ['ext_nombre'], 'message'=>'Nombre ya existe. Por favor ingrese otro.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ext_id' => 'Ext ID',
            'ext_nombre' => 'Departamento',
            'ext_abreviado' => 'Abreviado',
            'ext_estado' => 'Estado',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContribuyentes()
    {
        return $this->hasMany(Contribuyentes::className(), ['ext_id' => 'ext_id']);
    }
}
