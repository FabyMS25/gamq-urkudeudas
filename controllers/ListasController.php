<?php

namespace app\controllers;

use Yii;
use app\models\PagosEventuales;
use app\models\GraderiasSillas;
use app\models\SearchPagosEventuales;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;
use app\models\Usuario;

/**
 * PagosEventualesController implements the CRUD actions for PagosEventuales model.
 */
class ListasController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
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
     * Lists all GraderiasSillas models.
     * @return mixed
     */
    public function actionIndex()
    {  
        $msj='hola';
        $this->verificarSesion();
        //$searchModel = new GraderiasSillas();
        //$dataProvider = $searchModel->search(Yii::$app->request->queryParams);        
        //$dataProvider->query->andWhere(['grad_estado'=>1]);

        return $this->render('graderias', ['msj'=> $msj]
            //'searchModel' => $searchModel,
            //'dataProvider' => $dataProvider,
            //'zona'=>$id
        //]
        );
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

    // funcion que verifica la existencia de una sesion activa
    public function verificarSesion(){
        if(Yii::$app->user->isGuest){
           Yii::$app->user->logout(true);           
           return $this->goHome();
       }
   }
}