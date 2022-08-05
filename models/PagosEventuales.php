<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pagos_eventuales".
 *
 * @property integer $eventual_id
 * @property integer $usua_id
 * @property integer $contri_id
 * @property integer $sitios_id
 * @property integer $activi_id
 * @property string $eventual_fecha_hora_pago
 * @property string $eventual_fecha_inicio
 * @property string $eventual_fecha_limite
 * @property integer $eventual_cantidad_dia
 * @property integer $eventual_nro_comprobante
 * @property string $eventual_importe_patente
 * @property string $eventual_costo_comprobante
 * @property string $eventual_costo_sentaje
 * @property string $eventual_costo_aseo
 * @property string $eventual_importe_total
 * @property integer $eventual_anulado
 * @property string $eventual_anulado_detalle
 * @property string $eventual_anulado_fecha_hora
 * @property integer $eventual_preliquidacion
 * @property integer $eventual_user_id_preliquidacion
 * @property integer $eventual_estado
 * @property integer $eventual_cantidad_sitio
 * @property integer $eventual_nro_liquidacion
 *
 * @property ActividadesEconomicas $activi
 * @property Contribuyentes $contri
 * @property SitiosEventuales $sitios
 * @property Usuario $usua
 */
class PagosEventuales extends \yii\db\ActiveRecord
{
    
    public $categoria, $rango_fechas;
    public $estado_cobro_dia;
    public $nombre, $codigo, $ci;
    public $patente, $sentaje, $aseo;
    const COMPROBANTE = 10;

    public static function tableName()
    {
        return 'pagos_eventuales';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['usua_id', 'contri_id', 'sitios_id', 'activi_id', 'eventual_cantidad_dia', 'eventual_nro_comprobante', 'eventual_anulado', 'eventual_preliquidacion',
                'eventual_user_id_preliquidacion', 'eventual_estado'], 'integer'],
            
            [['contri_id', 'activi_id',   'eventual_cantidad_dia', 
             'eventual_importe_patente', 'eventual_costo_comprobante', 'eventual_importe_total'], 'required'],
            
            [['eventual_fecha_hora_pago', 'eventual_fecha_inicio', 'eventual_fecha_limite', 'eventual_anulado_fecha_hora'], 'safe'],
            [['eventual_importe_patente', 'eventual_costo_comprobante', 'eventual_costo_sentaje', 'eventual_costo_aseo', 'eventual_importe_total'], 'number'],
            [['eventual_anulado_detalle'], 'string', 'max' => 250],
            // personalizados
            [['categoria'  ], 'required', 'on'=>'crear_eventual_liquidacion' ],
            [['eventual_cantidad_sitio'], 'required', ],
            [['eventual_cantidad_sitio'], 'integer', 'min'=>0],
            [['eventual_cantidad_sitio', 'rango_fechas', 'sitios_id'], 'required', 
                'on'=>['crear_alasitas_liquidacion', 'crear_eventual_liquidacion'] ],
            
            [['eventual_cantidad_dia'],'integer','min'=>1, 'on'=>[ 'crear_publicidad_liquidacion']],//
            [['rango_fechas'],'required', 'on'=>['crear_espectaculos_liquidacion', 'crear_publicidad_liquidacion']],//
            //eventual_fecha_hora_pago     'eventual_nro_comprobante',
            // 'eventual_fecha_inicio', 'eventual_fecha_limite',
            [['eventual_nro_comprobante'], 'required', 'on'=>['cobrar_liquidacion']],
            [['eventual_nro_comprobante'], 'validateComprobante', 'on'=>['cobrar_liquidacion']],
            
            [['activi_id'], 'exist', 'skipOnError' => true, 'targetClass' => ActividadesEconomicas::className(), 'targetAttribute' => ['activi_id' => 'activi_id']],
            [['contri_id'], 'exist', 'skipOnError' => true, 'targetClass' => Contribuyentes::className(), 'targetAttribute' => ['contri_id' => 'contri_id']],
            [['sitios_id'], 'exist', 'skipOnError' => true, 'targetClass' => SitiosEventuales::className(), 'targetAttribute' => ['sitios_id' => 'sitios_id']],
            [['usua_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::className(), 'targetAttribute' => ['usua_id' => 'usua_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'eventual_id' => 'Eventual ID',
            'usua_id' => 'Usua ID',
            'contri_id' => 'Contribuyente',
            'sitios_id' => 'Sitio eventual',
            'activi_id' => 'Actividad Economica',
            
            'eventual_fecha_hora_pago' => 'Fecha pago',
            'eventual_fecha_inicio' => 'Fecha inicio',
            'eventual_fecha_limite' => 'Fecha limite',
            'eventual_cantidad_dia' => 'Cantidad dias',
            'eventual_nro_comprobante' => 'Nº comprobante',
            'eventual_importe_patente' => 'Patente',
            'eventual_costo_comprobante' => 'Comprobante',
            'eventual_costo_sentaje' => 'Sentaje',
            'eventual_costo_aseo' => 'Tasa aseo',
            'eventual_importe_total' => 'Importe/Bs.',
            'eventual_anulado' => 'Anulado',
            'eventual_anulado_detalle' => 'Justificacion de anulacion',
            'eventual_anulado_fecha_hora' => 'Fecha anulacion',
            'eventual_preliquidacion' => 'Preliquidacion',
            'eventual_user_id_preliquidacion' => 'Preliquidador', //cajero
            'eventual_fecha_hora_liquidacion'=>'Fecha preliquidacion',
            'eventual_estado' => 'Estado',
            'eventual_cantidad_sitio' => 'Cantidad puestos',
            'eventual_nro_liquidacion' => 'N° preliquidacion',
            
            //PERSONALIZADO
            'categoria' =>'Categoria',
            'rango_fechas' => 'Rango de fechas',
            'estado_cobro_dia' => 'ESTADO PAGO DIA', 
            'codigo' => 'Codigo',
            'nombre' => 'Nombres',            
            'ci' => 'Doc. identidad',
            // auxiliares
            'patente' => 'Patente Bs.:',
            'sentaje' => 'Sentaje Bs.:',
            'aseo' => 'Tasa de aseo Bs.:',
            
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActividad()
    {
        return $this->hasOne(ActividadesEconomicas::className(), ['activi_id' => 'activi_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContribuyente()
    {
        return $this->hasOne(Contribuyentes::className(), ['contri_id' => 'contri_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSitio()
    {
        return $this->hasOne(SitiosEventuales::className(), ['sitios_id' => 'sitios_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuario::className(), ['usua_id' => 'usua_id']);
    }
    
     // validaciones personalizados
    public function validateComprobante($attribute, $params, $validator)
    {
        $comprobante = $this->eventual_nro_comprobante;
        $total = $this->find()->where(['eventual_estado'=>1, 'eventual_nro_comprobante' => $comprobante])->count();
        
        if ($total > 0) {
            $this->addError($attribute, 'El comprobante "'.$comprobante.'" ya existe.');
        }
    }    
    
    // funciones 
    //lista de sitios eventuales ocupados
    public function listaIdsSitiosEventuales(){
        return $this->find()
                ->select('sitios_id')
                ->where(['eventual_estado'=>1, 'eventual_preliquidacion'=>1])
                ->andWhere(['>', 'sitios_id', 0])
                ->column();
    }
    
    public function linkReciboPreliquidacion(){
        $url = "preliquidacion-actividades";
        if($this->sitios_id > 0){
            $url = "preliquidacion-sitios";
        }
        return $url;
    }

    // funcion para convertir numeral a literal
    public function montoTotalLiteral(){
        $montoTotal = $this->eventual_importe_total;
        $modelAux = new NumeroALetras();
        return $modelAux->convertir($montoTotal);
    }

}
