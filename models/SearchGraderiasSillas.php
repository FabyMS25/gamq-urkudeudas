<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\GraderiasSillas;

/**
 * SearchGraderiasSillas represents the model behind the search form about `app\models\GraderiasSillas`.
 */
class SearchGraderiasSillas extends GraderiasSillas {

    public $zona;

    public function rules() {
        return [
                [['grad_id', 'zona_id', 'gest_id', 'grad_vendido', 'grad_estado'], 'integer'],
                [['grad_codigo', 'grad_direccion', 'grad_acera', 'grad_tipo_armado', 'grad_tipo_sitio'], 'safe'],
                [['grad_longitud'], 'number'],
                [['zona', 'grad_reservado'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
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
    public function search($params) {
        $query = GraderiasSillas::find()->innerJoinWith('zona');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->attributes['zona'] = [
                    'asc' => ['zonas.zona_nombre' => SORT_ASC],
                    'desc' => ['zonas.zona_nombre' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'grad_id' => $this->grad_id,
            'zona_id' => $this->zona_id,
            'gest_id' => $this->gest_id,
            'grad_longitud' => $this->grad_longitud,
            'grad_vendido' => $this->grad_vendido,
            'grad_estado' => $this->grad_estado,
            'grad_reservado'=> $this->grad_reservado
        ]);

        $query->andFilterWhere(['ilike', 'grad_codigo', $this->grad_codigo])
                ->andFilterWhere(['ilike', 'grad_direccion', $this->grad_direccion])
                ->andFilterWhere(['ilike', 'grad_acera', $this->grad_acera])
                ->andFilterWhere(['ilike', 'grad_tipo_armado', $this->grad_tipo_armado])
                ->andFilterWhere(['ilike', 'grad_tipo_sitio', $this->grad_tipo_sitio]);
        $query->andFilterWhere(['ilike', 'zonas.zona_nombre', $this->zona]);

        return $dataProvider;
    }
    
     public function searchPreliquidaciones($params) {
        $modelGestion = (new \app\models\Gestiones())->gestionVigente();          
        $query = GraderiasSillas::find()
                ->where(['graderias_sillas.gest_id'=>$modelGestion->gest_id])                
                //
                ->innerJoinWith('zona');
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->attributes['zona'] = [
                    'asc' => ['zonas.zona_nombre' => SORT_ASC],
                    'desc' => ['zonas.zona_nombre' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'grad_id' => $this->grad_id,
            'zona_id' => $this->zona_id,
            'gest_id' => $this->gest_id,
            'grad_longitud' => $this->grad_longitud,
            'grad_vendido' => $this->grad_vendido,
            'grad_estado' => $this->grad_estado,
             'grad_reservado'=> $this->grad_reservado
        ]);

        $query->andFilterWhere(['ilike', 'grad_codigo', $this->grad_codigo])
                ->andFilterWhere(['ilike', 'grad_direccion', $this->grad_direccion])
                ->andFilterWhere(['ilike', 'grad_acera', $this->grad_acera])
                ->andFilterWhere(['ilike', 'grad_tipo_armado', $this->grad_tipo_armado])
                ->andFilterWhere(['ilike', 'grad_tipo_sitio', $this->grad_tipo_sitio]);
        $query->andFilterWhere(['ilike', 'zonas.zona_nombre', $this->zona]);

        return $dataProvider;
    }
    
    

}
