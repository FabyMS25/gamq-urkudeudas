<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tipo_armados".
 *
 * @property integer $tip_arm_id
 * @property integer $zona_id
 * @property integer $gest_id
 * @property string $tip_arm_descricpion
 * @property string $tip_arm_patente
 * @property string $tip_arm_tasa_aseo
 * @property string $tip_arm_unidad_medida
 * @property integer $tip_arm_estado
 *
 * @property Pagos[] $pagos
 * @property Gestiones $gest
 * @property Zonas $zona
 */
class TipoArmados extends \yii\db\ActiveRecord
{
    const MEDIDA = "Metros lineales";

    public static function tableName()
    {
        return 'tipo_armados';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['zona_id', 'gest_id', 'tip_arm_descricpion', 'tip_arm_patente', 'tip_arm_tasa_aseo', 'tip_arm_unidad_medida', 'tip_arm_estado'], 'required'],
            [['zona_id', 'gest_id', 'tip_arm_estado'], 'integer'],
            [['tip_arm_patente', 'tip_arm_tasa_aseo'], 'number'],
            [['tip_arm_descricpion'], 'string', 'max' => 150],
            [['tip_arm_unidad_medida'], 'string', 'max' => 15],
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
            'tip_arm_id' => 'Tip Arm ID',
            'zona_id' => 'Zona',
            'gest_id' => 'Gestion',
            'tip_arm_descricpion' => 'Detalle',
            'tip_arm_patente' => 'Precio patente ',
            'tip_arm_tasa_aseo' => 'Tasa aseo',
            'tip_arm_unidad_medida' => 'Unidad medida',
            'tip_arm_estado' => 'Estado',
        
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPagos()
    {
        return $this->hasMany(Pagos::className(), ['tip_arm_id' => 'tip_arm_id']);
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
    
    // funciones personalizadas
    public function listaTipoArmadoPorZona($idzona){
        return $this->find()->where(['zona_id'=>$idzona, 'tip_arm_estado'=>1])->all();
    }
}
