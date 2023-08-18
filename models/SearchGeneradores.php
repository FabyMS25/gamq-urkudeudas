<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Descargos;

/**
 * SearchDescargos represents the model behind the search form about `app\models\Descargos`.
 */
class SearchGeneradores extends GeneradorDescargos
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['detalle_id', 'desc_id', 'detalle_precio', 'detalle_nro_inicio', 'detalle_nro_limite', 'detalle_cantidad', 'detalle_fecha_entrega','detalle_importe_bs','detalle_estado','detalle_tasa'], 'integer'],
            //[['desc_responsable', 'desc_fecha_hora', 'desc_anulado_justificacion', 'desc_anulado_fecha_hora'], 'safe'],
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
        $query = GeneradorDescargos::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'detalle_id' => $this->detalle_id,
            'desc_id' => $this->desc_id,
            'detalle_precio' => $this->detalle_precio,
            'detalle_nro_inicio' => $this->detalle_nro_inicio,
            'detalle_nro_limite' => $this->detalle_nro_limite,
            'detalle_cantidad' => $this->detalle_cantidad,
            'detalle_fecha_entrega' => $this->detalle_fecha_entrega,
            'detalle_importe_bs' => $this->detalle_importe_bs,
            'detalle_tasa' => $this->detalle_tasa,
            'detalle_estado' => 1,//$this->detalle_estado,
        ]);

        $query->andFilterWhere(['like', 'detalle_precio', $this->detalle_precio])
            ->andFilterWhere(['like', 'detalle_cantidad', $this->detalle_cantidad]);

        return $dataProvider;
    }
}
