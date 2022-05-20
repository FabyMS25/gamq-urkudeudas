<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Categorias;

/**
 * SearchCategorias represents the model behind the search form about `app\models\Categorias`.
 */
class SearchCategorias extends Categorias
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['categ_id', 'categ_estado'], 'integer'],
            [['categ_nombre', 'categ_codigo'], 'safe'],
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
        $query = Categorias::find();

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
            'categ_id' => $this->categ_id,
            'categ_estado' => $this->categ_estado,
        ]);

        $query->andFilterWhere(['like', 'categ_nombre', $this->categ_nombre])
            ->andFilterWhere(['like', 'categ_codigo', $this->categ_codigo]);

        return $dataProvider;
    }
}
