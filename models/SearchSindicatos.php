<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Sindicatos;

/**
 * SearchSindicatos represents the model behind the search form about `app\models\Sindicatos`.
 */
class SearchSindicatos extends Sindicatos
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sindi_id', 'sindi_estado'], 'integer'],
            [['sindi_nombre', 'sindi_descripcion'], 'safe'],
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
        $query = Sindicatos::find();

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
            'sindi_id' => $this->sindi_id,
            'sindi_estado' => $this->sindi_estado,
        ]);

        $query->andFilterWhere(['like', 'sindi_nombre', $this->sindi_nombre])
            ->andFilterWhere(['like', 'sindi_descripcion', $this->sindi_descripcion]);

        return $dataProvider;
    }
}
