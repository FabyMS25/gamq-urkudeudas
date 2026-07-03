<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Pagos;

/**
 * SearchPagos represents the model behind the search form about `app\models\Pagos`.
 */
class SearchPagos extends Pagos
{
    public $pago_estado_filter; // for estado filtering
    
    public function rules()
    {
        return [
            [['pago_id', 'grad_id', 'usua_id', 'contri_id', 'pago_nro_comprobante', 'pago_anulado', 'pago_preliquidacion', 'pago_id_user_preliquidacion', 'pago_estado'], 'integer'],
            [['pago_nro_liquidacion', 'pago_fecha_hora_cobro', 'pago_anulado_detalle', 'pago_anulado_fecha_hora', 'pago_estado_filter'], 'safe'],
            [['pago_longitud_modificada', 'pago_descuento_porcentaje', 'pago_descuento_monto', 'pago_importe_patente', 'pago_aseo', 'pago_reposicion', 'pago_importe_total'], 'number'],
            [['codigo','nombre',  'ci', 'pago_cobrado', 'pago_con_exencion'], 'safe']            
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
        $query = Pagos::find()
            ->leftJoin('contribuyentes', 'pagos.contri_id = contribuyentes.contri_id')
            ->leftJoin('graderias_sillas', 'pagos.grad_id = graderias_sillas.grad_id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $dataProvider->sort->attributes['codigo'] = [
                    'asc' => ['graderias_sillas.grad_codigo' => SORT_ASC],
                    'desc' => ['graderias_sillas.grad_codigo' => SORT_DESC],
        ];
        
        $dataProvider->sort->attributes['nombre'] = [
                    'asc' => ['(contri_nombres ||\' \'|| contri_paterno ||\' \'||   contri_materno)' => SORT_ASC],
                    'desc' =>['(contri_nombres ||\' \'|| contri_paterno ||\' \'||   contri_materno)' => SORT_DESC],
        ];
       
        $dataProvider->sort->attributes['ci'] = [
                    'asc' => ['contribuyentes.contri_ci' => SORT_ASC],
                    'desc' => ['contribuyentes.contri_ci' => SORT_DESC],
        ];
        
         /*
        $dataProvider->sort->attributes['paterno'] = [
                    'asc' => ['contribuyentes.contri_paterno' => SORT_ASC],
                    'desc' => ['contribuyentes.contri_paterno' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['materno'] = [
                    'asc' => ['contribuyentes.contri_materno' => SORT_ASC],
                    'desc' => ['contribuyentes.contri_materno' => SORT_DESC],
        ];*/
        

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'pago_id' => $this->pago_id,
            'grad_id' => $this->grad_id,
            'usua_id' => $this->usua_id,
            'contri_id' => $this->contri_id,
            'pago_longitud_modificada' => $this->pago_longitud_modificada,
            'pago_nro_comprobante' => $this->pago_nro_comprobante,
            'pago_descuento_porcentaje' => $this->pago_descuento_porcentaje,
            'pago_descuento_monto' => $this->pago_descuento_monto,
            'pago_importe_patente' => $this->pago_importe_patente,
            'pago_aseo' => $this->pago_aseo,
            'pago_reposicion' => $this->pago_reposicion,
            'pago_importe_total' => $this->pago_importe_total,
            'pago_fecha_hora_cobro' => $this->pago_fecha_hora_cobro,
            'pago_anulado' => $this->pago_anulado,
            'pago_anulado_fecha_hora' => $this->pago_anulado_fecha_hora,
            'pago_preliquidacion' => $this->pago_preliquidacion,
            'pago_id_user_preliquidacion' => $this->pago_id_user_preliquidacion,
            'pago_estado' => $this->pago_estado,
            'pago_cobrado' => $this->pago_cobrado,
            'pago_con_exencion' => $this->pago_con_exencion
        ]);

        $query->andFilterWhere(['ilike', 'pago_nro_liquidacion', $this->pago_nro_liquidacion])
            ->andFilterWhere(['ilike', 'pago_anulado_detalle', $this->pago_anulado_detalle]);        
        $query->andFilterWhere(['ilike', 'graderias_sillas.grad_codigo', $this->codigo]);
         // $query->andFilterWhere(['ilike', 'contribuyentes.contri_materno', $this->paterno]);
        //$query->andFilterWhere(['ilike', 'contribuyentes.contri_paterno', $this->materno]);
        $query->andFilterWhere(['ilike', '(contribuyentes.contri_nombres ||\' \'|| contribuyentes.contri_paterno ||\' \'||contribuyentes.contri_materno)', $this->nombre]);      
        $query->andFilterWhere(['ilike', 'contribuyentes.contri_ci', $this->ci]);

        return $dataProvider;
    }
}
