<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PagosEventuales;

/**
 * SearchPagosEventuales represents the model behind the search form about `app\models\PagosEventuales`.
 */
class SearchPagosEventuales extends PagosEventuales
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['eventual_id', 'usua_id', 'contri_id', 'sitios_id', 'activi_id', 'eventual_cantidad_dia', 'eventual_nro_comprobante', 'eventual_anulado', 'eventual_preliquidacion', 'eventual_user_id_preliquidacion', 'eventual_estado', 'eventual_cantidad_sitio', 'eventual_nro_liquidacion'], 'integer'],
            [['eventual_fecha_hora_pago', 'eventual_fecha_inicio', 'eventual_fecha_limite', 'eventual_anulado_detalle', 'eventual_anulado_fecha_hora', 'eventual_fecha_hora_liquidacion'], 'safe'],
            [['eventual_importe_patente', 'eventual_costo_comprobante', 'eventual_costo_sentaje', 'eventual_costo_aseo', 'eventual_importe_total'], 'number'],
            [['codigo','nombre',  'ci', ], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = PagosEventuales::find()->innerJoinWith(['contribuyente']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
                       
        $dataProvider->sort->attributes['nombre'] = [
                    'asc' => ['(contri_nombres ||\' \'|| contri_paterno ||\' \'||   contri_materno)' => SORT_ASC],
                    'desc' =>['(contri_nombres ||\' \'|| contri_paterno ||\' \'||   contri_materno)' => SORT_DESC],
        ];
       
        $dataProvider->sort->attributes['ci'] = [
                    'asc' => ['contribuyentes.contri_ci' => SORT_ASC],
                    'desc' => ['contribuyentes.contri_ci' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'eventual_id' => $this->eventual_id,
            'usua_id' => $this->usua_id,
            'contri_id' => $this->contri_id,
            'sitios_id' => $this->sitios_id,
            'activi_id' => $this->activi_id,
           // 'eventual_fecha_hora_pago' => $this->eventual_fecha_hora_pago,
            'eventual_fecha_inicio' => $this->eventual_fecha_inicio,
            'eventual_fecha_limite' => $this->eventual_fecha_limite,
            'eventual_cantidad_dia' => $this->eventual_cantidad_dia,
            'eventual_nro_comprobante' => $this->eventual_nro_comprobante,
            'eventual_importe_patente' => $this->eventual_importe_patente,
            'eventual_costo_comprobante' => $this->eventual_costo_comprobante,
            'eventual_costo_sentaje' => $this->eventual_costo_sentaje,
            'eventual_costo_aseo' => $this->eventual_costo_aseo,
            'eventual_importe_total' => $this->eventual_importe_total,
            'eventual_anulado' => $this->eventual_anulado,
            'eventual_anulado_fecha_hora' => $this->eventual_anulado_fecha_hora,
            'eventual_preliquidacion' => $this->eventual_preliquidacion,
            'eventual_user_id_preliquidacion' => $this->eventual_user_id_preliquidacion,
            'eventual_estado' => $this->eventual_estado,
            'eventual_cantidad_sitio' => $this->eventual_cantidad_sitio,
            'eventual_nro_liquidacion' => $this->eventual_nro_liquidacion,
        ]);
        
        // FECHA DE PAGO
         if(isset($this->eventual_fecha_hora_pago)&& strlen($this->eventual_fecha_hora_pago)>5){ //you dont need the if function if yourse sure you have a not null date
              $date_explode=explode(" a ",$this->eventual_fecha_hora_pago);
              $date1= substr(trim($date_explode[0]), 0,10);
              $date2= substr(trim($date_explode[1]), 0,10);
              $query->andFilterWhere(['between','eventual_fecha_hora_pago',$date1,$date2]);
          }
        

        $query->andFilterWhere(['like', 'eventual_anulado_detalle', $this->eventual_anulado_detalle]);
        $query->andFilterWhere(['ilike', '(contribuyentes.contri_nombres ||\' \'|| contribuyentes.contri_paterno ||\' \'||contribuyentes.contri_materno)', $this->nombre]);      
        $query->andFilterWhere(['ilike', 'contribuyentes.contri_ci', $this->ci]);

        return $dataProvider;
    }
}
