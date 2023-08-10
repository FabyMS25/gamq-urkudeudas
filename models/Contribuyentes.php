<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contribuyentes".
 *
 * @property integer $contri_id
 * @property integer $ext_id
 * @property integer $sindi_id
 * @property string $contri_nombres
 * @property string $contri_paterno
 * @property string $contri_materno
 * @property string $contri_apellidocasada
 * @property string $contri_ci
 * @property string $contri_direccion
 * @property integer $contri_telefono
 * @property integer $contri_nit
 * @property string $contri_fecharegistro
 * @property integer $contri_estado
 *
 * @property Extensiones $ext
 * @property Sindicatos $sindi
 * @property Pagos[] $pagos
 * @property PagosEventuales[] $pagosEventuales
 */
class Contribuyentes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contribuyentes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ext_id', 'contri_nombres', 'contri_ci', 'contri_direccion', 'contri_fecharegistro', 'contri_estado','sindi_id'], 'required'],
            [['ext_id', 'sindi_id', 'contri_telefono', 'contri_nit', 'contri_estado'], 'integer'],
            [['contri_fecharegistro'], 'safe'],
            [['contri_nombres'], 'string', 'max' => 100],
            [['contri_paterno', 'contri_materno', 'contri_apellidocasada'], 'string', 'max' => 80],
            [['contri_ci','contri_nombres','contri_paterno','contri_materno','contri_apellidocasada'], 'trim'],
            [['contri_nombres','contri_paterno','contri_materno','contri_apellidocasada'], 'filter', 'filter' => 'strtoupper'],
            [['contri_ci'], 'string', 'max' => 15],
            [['contri_ci'], 'unique', 'targetAttribute' => ['contri_ci'], 'message'=>'El carnet de identidad del contribuyente ya existe. Por favor prueba con otro.'],
            [['contri_direccion'], 'string', 'max' => 250],
            [['ext_id'], 'exist', 'skipOnError' => true, 'targetClass' => Extensiones::className(), 'targetAttribute' => ['ext_id' => 'ext_id']],
            [['sindi_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sindicatos::className(), 'targetAttribute' => ['sindi_id' => 'sindi_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'contri_id' => 'Contri ID',
            'ext_id' => 'Ciudad',
            'sindi_id' => 'Sindicato',
            'contri_nombres' => 'Nombres',
            'contri_paterno' => 'Apellido paterno',
            'contri_materno' => 'Apellido materno',
            'contri_apellidocasada' => 'Apellido casada',
            'contri_ci' => 'Carnet',
            'contri_direccion' => 'Direccion',
            'contri_telefono' => 'Telefono',
            'contri_nit' => 'Nit',
            'contri_fecharegistro' => 'Fecha de registro',
            'contri_estado' => 'Estado',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpedido()
    {
        return $this->hasOne(Extensiones::className(), ['ext_id' => 'ext_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSindicato()
    {
        return $this->hasOne(Sindicatos::className(), ['sindi_id' => 'sindi_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPagos()
    {
        return $this->hasMany(Pagos::className(), ['contri_id' => 'contri_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPagosEventuales()
    {
        return $this->hasMany(PagosEventuales::className(), ['contri_id' => 'contri_id']);
    }
    
    // Listar contribuyentes vigentes
    public function ListaContribuyentesModel(){
        return $this->find()->where(['contri_estado' =>1])
                ->orderBy('contri_nombres ASC')->all();
    }
    
    public function getNombreCompletoCiContribuyente(){
        if($this->contri_id > 0):
            return $this->contri_nombres." ". $this->contri_paterno." ".$this->contri_materno." - CI: ".$this->contri_ci;
        else:
            return "Sin datos";
        endif;
        
    }
   
    
    public function getNombreCompletoContribuyente(){
        if($this->contri_id > 0):
            return $this->contri_nombres." ". $this->contri_paterno." ".$this->contri_materno;
        else:
            return "Sin datos";
        endif;
        
    }
}
