<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\ActividadesEconomicas;

/**
 * SearchActividadesEconomicas represents the model behind the search form about `app\models\ActividadesEconomicas`.
 */
class SearchActividadesEconomicas extends ActividadesEconomicas
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activi_id', 'categ_id'], 'integer'],
            [['activi_descripcion'], 'safe'],
            [['activi_largo_mts', 'activi_ancho_mts', 'activi_superficie', 'activi_costo_patente', 'activi_costo_sentaje_dia', 'activi_costo_aseo_por_dia', 'activi_costo_aseo_por_sitio'], 'number'],
            [['activi_descripcion'], 'unique', 'targetAttribute' => ['activi_descripcion'], 'message'=>'La descripcion de la actividad ya existe. Por favor ingrese otro.'],
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
        $query = ActividadesEconomicas::find();

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
            'activi_id' => $this->activi_id,
            'categ_id' => $this->categ_id,
            'activi_largo_mts' => $this->activi_largo_mts,
            'activi_ancho_mts' => $this->activi_ancho_mts,
            'activi_superficie' => $this->activi_superficie,
            'activi_costo_patente' => $this->activi_costo_patente,
            'activi_costo_sentaje_dia' => $this->activi_costo_sentaje_dia,
            'activi_costo_aseo_por_dia' => $this->activi_costo_aseo_por_dia,
            'activi_costo_aseo_por_sitio' => $this->activi_costo_aseo_por_sitio,
        ]);

        $query->andFilterWhere(['like', 'activi_descripcion', $this->activi_descripcion]);

        return $dataProvider;
    }
}
