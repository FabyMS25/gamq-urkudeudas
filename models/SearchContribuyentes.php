<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Contribuyentes;

/**
 * SearchContribuyentes represents the model behind the search form about `app\models\Contribuyentes`.
 */
class SearchContribuyentes extends Contribuyentes
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contri_id', 'ext_id', 'sindi_id', 'contri_telefono', 'contri_nit', 'contri_estado'], 'integer'],
            [[
                'contri_nombres', 'contri_paterno', 'contri_materno', 'contri_apellidocasada', 'contri_ci',
                'contri_direccion', 'contri_fecharegistro', 'contri_codigo_ruat', 'contri_tipo_contribuyente_ruat',
                'contri_tipo_documento_ruat', 'contri_estado_ruat', 'contri_ruat_sync_at'
            ], 'safe'],
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
        $query = Contribuyentes::find();

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
            'contri_id' => $this->contri_id,
            'ext_id' => $this->ext_id,
            'sindi_id' => $this->sindi_id,
            'contri_telefono' => $this->contri_telefono,
            'contri_nit' => $this->contri_nit,
            'contri_fecharegistro' => $this->contri_fecharegistro,
            'contri_estado' => $this->contri_estado,
        ]);

        $query->andFilterWhere(['ilike', 'contri_nombres', $this->contri_nombres])
            ->andFilterWhere(['ilike', 'contri_paterno', $this->contri_paterno])
            ->andFilterWhere(['ilike', 'contri_materno', $this->contri_materno])
            ->andFilterWhere(['ilike', 'contri_apellidocasada', $this->contri_apellidocasada])
            ->andFilterWhere(['ilike', 'contri_ci', $this->contri_ci])
            ->andFilterWhere(['ilike', 'contri_direccion', $this->contri_direccion])
            ->andFilterWhere(['ilike', 'contri_codigo_ruat', $this->contri_codigo_ruat]);

        return $dataProvider;
    }
}
