<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sitios_eventuales".
 *
 * @property integer $sitios_id
 * @property string $sitios_codigo
 * @property string $sitios_descripcion
 * @property integer $sitios_numero_sitio
 * @property integer $sitios_vendido
 * @property integer $sitios_estado
 *
 * @property PagosEventuales[] $pagosEventuales
 */
class SitiosEventuales extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'sitios_eventuales';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                [['sitios_codigo', 'sitios_descripcion', 'sitios_numero_sitio', 'sitios_estado'], 'required'],
                [['sitios_numero_sitio', 'sitios_vendido', 'sitios_estado'], 'integer'],
                [['sitios_codigo'], 'string', 'max' => 10],
                [['sitios_descripcion'], 'string', 'max' => 350],
                [['sitios_es_alasita'], 'boolean'],
                [['sitios_codigo', 'sitios_descripcion', 'sitios_numero_sitio'], 'unique', 'targetAttribute' => ['sitios_codigo', 'sitios_descripcion', 'sitios_numero_sitio'], 'message' => 'Esta combinacion de Codigo, Descripcion y Numero de Sitio ya esta registrado.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'sitios_id' => 'Sitios ID',
            'sitios_codigo' => 'Codigo sitio',
            'sitios_descripcion' => 'Ubicacion ',
            'sitios_numero_sitio' => 'N° puesto',
            'sitios_vendido' => 'Vendido',
            'sitios_estado' => 'Estado',
            'sitios_es_alasita' => 'Sitio para alasita',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPagosEventuales() {
        return $this->hasMany(PagosEventuales::className(), ['sitios_id' => 'sitios_id']);
    }

    //lista de sitios eventuales para  --
    public function listaSitiosEventualesLibresModel($listaSitios) {
        $resultado = $this->find()
              //  ->select('sitios_id, sitios_codigo, sitios_descripcion, sitios_numero_sitio, sitios_vendido')
               ->select("sitios_id, (sitios_codigo||' - (N° puesto: '|| sitios_numero_sitio ||') - '||sitios_descripcion) AS  codigoDireccionSitio")
                ->where(['sitios_estado' => 1])
                ->andWhere(['NOT IN', 'sitios_id', $listaSitios])
                ->orderBy('sitios_codigo ASC')
                ->asArray()                
               // ->limit(5000)
                ->all()
                ;
                
              
       $listaSitioLibres = [];         
      
        foreach ($resultado as $row) {           
            $listaSitioLibres[$row['sitios_id']] = $row['codigodireccionsitio'] ;           
        }        
        return  $listaSitioLibres;
       
    }

    public function listaSitiosEventualesModel() {
        return $this->find()->where(['sitios_estado' => 1])
                        ->orderBy('sitios_codigo ASC')
                        ->all();
    }

    public function getCodigoDireccionSitio() {
        $codigo = $this->sitios_codigo;
        $nroSitio = $this->sitios_numero_sitio;
        $resultado = "No definido";
        if (strlen($codigo) > 0):
            $resultado = trim($codigo . " ( N° puesto: " . $nroSitio . ")"); //.$this->sitios_descripcion);
        endif;
        return $resultado;
    }
    
     public function getSitioParaAlasita() {
        $estado = $this->sitios_es_alasita;
       
        $resultado = "No";
        if ($estado):
           $resultado = "Si";
        endif;
        return $resultado;
    }

}
