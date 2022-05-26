<?php

namespace app\controllers;

use Yii;
use app\models\PagosEventuales;
use app\models\SearchPagosEventuales;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;
use app\models\Usuario;

/*
 *
 */
class ListasEventualesController extends Controller {

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
        
        $searchModel = new SearchPagosEventuales();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
         //$dataProvider->query->andWhere(['IS ', 'eventual_fecha_hora_pago',  NULL]);
        //$dataProvider->query->andFilterWhere(['eventual_estado'=>1]);
       

        return $this->render('index' , [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
   }

    
 
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
