<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\SitiosEventuales;

/**
 * SearchSitiosEventuales represents the model behind the search form about `app\models\SitiosEventuales`.
 */
class SearchSitiosEventuales extends SitiosEventuales
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sitios_id', 'sitios_numero_sitio', 'sitios_vendido', 'sitios_estado'], 'integer'],
            [['sitios_codigo', 'sitios_descripcion', 'sitios_es_alasita'], 'safe'],
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
        $query = SitiosEventuales::find();

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
            'sitios_id' => $this->sitios_id,
            'sitios_numero_sitio' => $this->sitios_numero_sitio,
            'sitios_vendido' => $this->sitios_vendido,
            'sitios_estado' => $this->sitios_estado,
            'sitios_es_alasita'=> $this->sitios_es_alasita
        ]);

        $query->andFilterWhere(['ilike', 'sitios_codigo', $this->sitios_codigo])
            ->andFilterWhere(['ilike', 'sitios_descripcion', $this->sitios_descripcion]);

        return $dataProvider;
    }
}
