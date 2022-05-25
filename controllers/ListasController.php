<?php

namespace app\controllers;

use Yii;
use app\models\Pagos;
use app\models\SearchPagos;
use app\models\SearchGraderiasSillas;
;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;
use app\models\Usuario;
/**
 * PagosController implements the CRUD actions for Pagos model.
 */
class ListasController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'delete' => ['post'],
                    //'bulk-delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Pagos models.
     * @return mixed
     */
    public function actionIndex() {
        $this->verificarSesion();
        $listaGraderia = (new Pagos())->listaIdGraderiasSillasPreliquidados();
        $searchModel = new \app\models\SearchGraderiasSillas();
        $dataProvider = $searchModel->searchPreliquidaciones(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['grad_estado' => 1]);
        $dataProvider->query->andFilterWhere(['NOT IN', 'grad_id', $listaGraderia]);

        return $this->render('graderias', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /* public function actionGeneral() {
        $this->verificarSesion();
        $searchModel = new \app\models\SearchGraderiasSillas();
        $dataProvider = $searchModel->searchPreliquidaciones(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['grad_estado' => 1]);
        $dataProvider->query->andFilterWhere(['NOT IN', 'grad_id', $listaGraderia]);
        //'pago_cobrado' => 1, 'pago_anulado' => 0

        return $this->render('graderias', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }
 */
public function actionBulkDelete()
    {       
        $this->verificarSesion();
        $request = Yii::$app->request;
        $pks = explode(',', $request->post( 'pks' )); // Array or selected records primary keys
        foreach ( $pks as $pk ) {
            $model = $this->findModel($pk);
            $model->delete();
        }

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
       
    }


public function verificarSesion() {
    if (Yii::$app->user->isGuest) {
        Yii::$app->user->logout(true);
        return $this->goHome();
    }
}
}