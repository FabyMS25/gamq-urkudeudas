<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "gestiones".
 *
 * @property integer $gest_id
 * @property integer $gest_nombre
 * @property string $gest_ordenanza
 * @property integer $gest_vigente
 * @property integer $gest_estado
 *
 * @property GraderiasSillas[] $graderiasSillas
 * @property TipoArmados[] $tipoArmados
 * @property Zonas[] $zonas
 */
class Gestiones extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'gestiones';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['gest_nombre', 'gest_vigente', 'gest_estado'], 'required'],
            [['gest_nombre', 'gest_vigente', 'gest_estado'], 'integer'],
            [['gest_ordenanza'], 'string', 'max' => 30],
            [['gest_nombre'], 'unique', 'targetAttribute' => ['gest_nombre'], 'message'=>'Nombre gestion ya existe. Por favor ingrese otra gestion.'],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'gest_id' => 'Gest ID',
            'gest_nombre' => 'Gest Nombre',
            'gest_ordenanza' => 'Gest Ordenanza',
            'gest_vigente' => 'Gest Vigente',
            'gest_estado' => 'Gest Estado',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGraderiasSillas()
    {
        return $this->hasMany(GraderiasSillas::className(), ['gest_id' => 'gest_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTipoArmados()
    {
        return $this->hasMany(TipoArmados::className(), ['gest_id' => 'gest_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZonas()
    {
        return $this->hasMany(Zonas::className(), ['gest_id' => 'gest_id']);
    }
    
    public function  gestionVigente(){
        return $this->find()->where(['gest_vigente'=>1, 'gest_estado'=>1])->one();
    }
}
