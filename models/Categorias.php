<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "categorias".
 *
 * @property integer $categ_id
 * @property string $categ_nombre
 * @property string $categ_codigo
 * @property integer $categ_estado
 *
 * @property ActividadesEconomicas[] $actividadesEconomicas
 */
class Categorias extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'categorias';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['categ_nombre', 'categ_codigo', 'categ_estado'], 'required'],
            [['categ_estado'], 'integer'],
            [['categ_nombre'], 'string', 'max' => 150],
            [['categ_nombre'], 'filter', 'filter' => 'strtoupper'],
            [['categ_codigo'], 'filter', 'filter' => 'strtoupper'],
            [['categ_nombre', 'categ_codigo'], 'trim'],
            [['categ_nombre'], 'unique', 'targetAttribute' => ['categ_nombre'], 'message'=>'Nombre de la categoria ya existe. Por favor prueba con otro.'],
            [['categ_codigo'], 'string', 'min' => 5,'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'categ_id' => 'Categ ID',
            'categ_nombre' => 'Nombre categoria',
            'categ_codigo' => 'Codigo',
            'categ_estado' => 'Estado',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActividadesEconomicas()
    {
        return $this->hasMany(ActividadesEconomicas::className(), ['categ_id' => 'categ_id']);
    }
    
    // funciones personalizadas
    public function listaCategoriasModel(){        
        return $this->find()->where(['categ_estado' =>1])->orderBy('categ_nombre ASC')->all();        
    }
    
    // listar categorias de acuerdo al codigo
    public function listaCategoriasModelCodigo($codigo){
    
        return $this->find()->where(['categ_estado' =>1, 'categ_codigo' => strtoupper($codigo)])->orderBy('categ_nombre ASC')->all();        
    }
}

