<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Usuario;

/**
 * SearchUsuario represents the model behind the search form about `app\models\Usuario`.
 */
class SearchUsuario extends Usuario
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['usua_id', 'usua_estado'], 'integer'],
            [['usua_nombres', 'usua_apellidos', 'usua_ci', 'usua_cuenta', 'usua_password', 'usua_rol'], 'safe'],
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
        $query = Usuario::find();

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
            'usua_id' => $this->usua_id,
            'usua_estado' => $this->usua_estado,
        ]);

        $query->andFilterWhere(['like', 'usua_nombres', $this->usua_nombres])
            ->andFilterWhere(['like', 'usua_apellidos', $this->usua_apellidos])
            ->andFilterWhere(['like', 'usua_ci', $this->usua_ci])
            ->andFilterWhere(['like', 'usua_cuenta', $this->usua_cuenta])
            ->andFilterWhere(['like', 'usua_password', $this->usua_password])
            ->andFilterWhere(['like', 'usua_rol', $this->usua_rol]);

        return $dataProvider;
    }
}
