<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "razon_sociales".
 *
 * @property integer $razon_id
 * @property string $razon_nombre
 * @property integer $razon_estado
 *
 * @property Descargos[] $descargos
 */
class RazonSociales extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'razon_sociales';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['razon_nombre', 'razon_estado'], 'required'],
            [['razon_estado'], 'integer'],
            [['razon_nombre'], 'string', 'max' => 300],
            [['razon_nombre'], 'unique', 'targetAttribute' => ['razon_nombre'], 'message'=>'Nombre de razon ya existe. Por favor ingrese otro.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'razon_id' => 'Razon ID',
            'razon_nombre' => 'Razon social',
            'razon_estado' => 'Estado',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDescargos()
    {
        return $this->hasMany(Descargos::className(), ['razon_id' => 'razon_id']);
    }
    
    // model class
    public function listaRazonesSocialesModel()
    {
        
        return $this->find()->where(['razon_estado'=>1])
                ->orderBy('razon_nombre ASC')->all();
    }
        
    
}
