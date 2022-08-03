<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Usuario;

/**
 * SearchUsuario represents the model behind the search form about `app\models\Usuario`.
 */
class SearchReset extends Usuario
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
            'usua_nombres' => Yii::$app->user->identity->usua_nombres,
            'usua_ci' => Yii::$app->user->identity->usua_ci,
            'usua_estado' => $this->usua_estado,
        ]);
        //var_dump(Yii::$app->user->identity);
        return $dataProvider;
    }
}
