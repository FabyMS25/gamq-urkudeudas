<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Gestiones;

/**
 * SearchGestiones represents the model behind the search form about `app\models\Gestiones`.
 */
class SearchGestiones extends Gestiones
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['gest_id', 'gest_nombre', 'gest_vigente', 'gest_estado'], 'integer'],
            [['gest_ordenanza'], 'safe'],
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
        $query = Gestiones::find();

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
            'gest_id' => $this->gest_id,
            'gest_nombre' => $this->gest_nombre,
            'gest_vigente' => $this->gest_vigente,
            'gest_estado' => $this->gest_estado,
        ]);

        $query->andFilterWhere(['like', 'gest_ordenanza', $this->gest_ordenanza]);

        return $dataProvider;
    }
}
