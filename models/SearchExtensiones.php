<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Extensiones;

/**
 * SearchExtensiones represents the model behind the search form about `app\models\Extensiones`.
 */
class SearchExtensiones extends Extensiones
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ext_id', 'ext_estado'], 'integer'],
            [['ext_nombre', 'ext_abreviado'], 'safe'],
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
        $query = Extensiones::find();

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
            'ext_id' => $this->ext_id,
            'ext_estado' => $this->ext_estado,
        ]);

        $query->andFilterWhere(['like', 'ext_nombre', $this->ext_nombre])
            ->andFilterWhere(['like', 'ext_abreviado', $this->ext_abreviado]);

        return $dataProvider;
    }
}
