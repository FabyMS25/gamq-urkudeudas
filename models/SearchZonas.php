<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Zonas;

/**
 * SearchZonas represents the model behind the search form about `app\models\Zonas`.
 */
class SearchZonas extends Zonas
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['zona_id', 'gest_id', 'zona_estado'], 'integer'],
            [['zona_nombre', 'zona_color', 'zona_color_hexadecimal', 'zona_descripcion'], 'safe'],
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
        $query = Zonas::find();

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
            'zona_id' => $this->zona_id,
            'gest_id' => $this->gest_id,
            'zona_estado' => $this->zona_estado,
        ]);

        $query->andFilterWhere(['ilike', 'zona_nombre', $this->zona_nombre])
            ->andFilterWhere(['ilike', 'zona_color', $this->zona_color])
            ->andFilterWhere(['ilike', 'zona_color_hexadecimal', $this->zona_color_hexadecimal])
            ->andFilterWhere(['ilike', 'zona_descripcion', $this->zona_descripcion]);

        return $dataProvider;
    }
}
