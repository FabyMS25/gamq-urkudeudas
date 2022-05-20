<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\TipoArmados;

/**
 * SearchTipoArmados represents the model behind the search form about `app\models\TipoArmados`.
 */
class SearchTipoArmados extends TipoArmados
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tip_arm_id', 'zona_id', 'gest_id', 'tip_arm_estado'], 'integer'],
            [['tip_arm_descricpion', 'tip_arm_unidad_medida'], 'safe'],
            [['tip_arm_patente', 'tip_arm_tasa_aseo'], 'number'],
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
        $query = TipoArmados::find();

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
            'tip_arm_id' => $this->tip_arm_id,
            'zona_id' => $this->zona_id,
            'gest_id' => $this->gest_id,
            'tip_arm_patente' => $this->tip_arm_patente,
            'tip_arm_tasa_aseo' => $this->tip_arm_tasa_aseo,
            'tip_arm_estado' => $this->tip_arm_estado,
        ]);

        $query->andFilterWhere(['ilike', 'tip_arm_descricpion', $this->tip_arm_descricpion])
            ->andFilterWhere(['ilike', 'tip_arm_unidad_medida', $this->tip_arm_unidad_medida]);

        return $dataProvider;
    }
}
