<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "actividades_economicas".
 *
 * @property integer $activi_id
 * @property integer $categ_id
 * @property string $activi_descripcion
 * @property string $activi_largo_mts
 * @property string $activi_ancho_mts
 * @property string $activi_superficie
 * @property string $activi_costo_patente
 * @property string $activi_costo_sentaje_dia
 * @property string $activi_costo_aseo_por_dia
 * @property string $activi_costo_aseo_por_sitio
 * @property integer $activi_estado
 *
 * @property Categorias $categ
 * @property PagosEventuales[] $pagosEventuales
 */
class ActividadesEconomicas extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'actividades_economicas';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['categ_id', 'activi_descripcion', 'activi_costo_patente'], 'required'],
            [['categ_id', 'activi_estado'], 'integer'],
            [['activi_largo_mts', 'activi_ancho_mts', 'activi_superficie', 'activi_costo_patente',   
                'activi_costo_aseo_por_sitio'], 'number'],
            [['activi_descripcion'], 'string', 'max' => 500],
            //tasa de aseo diario
            [['activi_costo_aseo_por_dia', 'activi_costo_sentaje_dia'], 'number', 'min'=>0],
            [['activi_costo_aseo_por_dia', 'activi_costo_sentaje_dia'], 'required'],
            
            [['categ_id'], 'exist', 'skipOnError' => true, 'targetClass' => Categorias::className(), 'targetAttribute' => ['categ_id' => 'categ_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'activi_id' => 'Activi ID',
            'categ_id' => 'Categoria',
            'activi_descripcion' => 'Actividad economica',
            'activi_largo_mts' => 'Largo (m.)',
            'activi_ancho_mts' => 'Ancho (m.)',
            'activi_superficie' => ' Superficie m2',
            'activi_costo_patente' => ' Patente Bs.',
            'activi_costo_sentaje_dia' => 'Sentaje dia',
            'activi_costo_aseo_por_dia' => 'Tasa aseo dia',
            'activi_costo_aseo_por_sitio' => 'Tasa aseo por sitio',
            'activi_estado' => 'Estado',
            'activi_cobro_por_dia' => 'Cobro por dia?'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoria()
    {
        return $this->hasOne(Categorias::className(), ['categ_id' => 'categ_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPagosEventuales()
    {
        return $this->hasMany(PagosEventuales::className(), ['activi_id' => 'activi_id']);
    }
    
    //
    public function listaActividadesEconomicasModel(){
        return $this->find()->where(['activi_estado'=>1])->all();
    }
    
     public function listaActividadesEconomicasAlasitasModel(){
        return $this->find()
                ->where(['activi_estado'=>1, 'categorias.categ_codigo'=>'ALASITA'])
                ->innerJoinWith('categoria')
                ->all();
    }
    
    // espectaculos
    
    public function listaActividadesEconomicasEspectaculosModel(){
        return $this->find()
                ->where(['activi_estado'=>1, 'categorias.categ_codigo'=>'ESPECTACULO'])
                ->innerJoinWith('categoria')
                ->all();
    }
    
    //publicidad
    public function listaActividadesEconomicasPublicidadModel(){
        return $this->find()
                ->where(['activi_estado'=>1, 'categorias.categ_codigo'=>'PUBLICIDAD'])
                ->innerJoinWith('categoria')
                ->all();
    }
}
