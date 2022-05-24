<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sindicatos".
 *
 * @property integer $sindi_id
 * @property string $sindi_nombre
 * @property string $sindi_descripcion
 * @property integer $sindi_estado
 *
 * @property Contribuyentes[] $contribuyentes
 */
class Sindicatos extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sindicatos';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sindi_nombre', 'sindi_descripcion', 'sindi_estado'], 'required'],
            [['sindi_estado'], 'integer'],
            [['sindi_nombre', 'sindi_descripcion'], 'string', 'max' => 250],
            [['sindi_nombre','sindi_descripcion'], 'filter', 'filter' => 'strtoupper'],
            [['sindi_nombre', 'sindi_descripcion'], 'trim'],
            [['sindi_nombre'], 'unique', 'targetAttribute' => ['sindi_nombre'], 'message'=>'Nombre del sindicato ya existe. Por favor ingrese otro.'],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'sindi_id' => 'Sindi ID',
            'sindi_nombre' => 'Nombre sindicato',
            'sindi_descripcion' => 'Descripcion',
            'sindi_estado' => 'Estado',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContribuyentes()
    {
        return $this->hasMany(Contribuyentes::className(), ['sindi_id' => 'sindi_id']);
    }
}
