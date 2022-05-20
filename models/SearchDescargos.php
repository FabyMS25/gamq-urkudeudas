<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Descargos;

/**
 * SearchDescargos represents the model behind the search form about `app\models\Descargos`.
 */
class SearchDescargos extends Descargos
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['desc_id', 'usua_id', 'razon_id', 'desc_nro_comprobante', 'desc_anulado', 'desc_impreso', 'desc_estado'], 'integer'],
            [['desc_responsable', 'desc_fecha_hora', 'desc_anulado_justificacion', 'desc_anulado_fecha_hora'], 'safe'],
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
        $query = Descargos::find();

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
            'desc_id' => $this->desc_id,
            'usua_id' => $this->usua_id,
            'razon_id' => $this->razon_id,
            'desc_nro_comprobante' => $this->desc_nro_comprobante,
            'desc_fecha_hora' => $this->desc_fecha_hora,
            'desc_anulado' => $this->desc_anulado,
            'desc_anulado_fecha_hora' => $this->desc_anulado_fecha_hora,
            'desc_impreso' => $this->desc_impreso,
            'desc_estado' => $this->desc_estado,
        ]);

        $query->andFilterWhere(['like', 'desc_responsable', $this->desc_responsable])
            ->andFilterWhere(['like', 'desc_anulado_justificacion', $this->desc_anulado_justificacion]);

        return $dataProvider;
    }
}
