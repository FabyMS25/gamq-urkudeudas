<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "zonas".
 *
 * @property integer $zona_id
 * @property integer $gest_id
 * @property string $zona_nombre
 * @property string $zona_color
 * @property string $zona_color_hexadecimal
 * @property string $zona_descripcion
 * @property integer $zona_estado
 *
 * @property GraderiasSillas[] $graderiasSillas
 * @property TipoArmados[] $tipoArmados
 * @property Gestiones $gest
 */
class Zonas extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'zonas';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['gest_id', 'zona_nombre', 'zona_color', 'zona_color_hexadecimal', 'zona_descripcion', 'zona_estado'], 'required'],
            [['gest_id', 'zona_estado'], 'integer'],
            [['zona_nombre', 'zona_color', 'zona_color_hexadecimal'], 'string', 'max' => 10],
            [['zona_descripcion'], 'string', 'max' => 350],
            [['zona_nombre'], 'unique', ],
            [['gest_id'], 'exist', 'skipOnError' => true, 'targetClass' => Gestiones::className(), 'targetAttribute' => ['gest_id' => 'gest_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'zona_id' => 'Zona ID',
            'gest_id' => 'Gestion',
            'zona_nombre' => 'Zona nombre',
            'zona_color' => 'Color',
            'zona_color_hexadecimal' => 'Hexadecimal',
            'zona_descripcion' => 'Descripcion',
            'zona_estado' => 'Estado',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGraderiasSillas()
    {
        return $this->hasMany(GraderiasSillas::className(), ['zona_id' => 'zona_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTipoArmados()
    {
        return $this->hasMany(TipoArmados::className(), ['zona_id' => 'zona_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGestion()
    {
        return $this->hasOne(Gestiones::className(), ['gest_id' => 'gest_id']);
    }
}
