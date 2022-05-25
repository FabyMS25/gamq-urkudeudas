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
                    'delete' => ['post'],
                    'bulk-delete' => ['post'],
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
        $this->verificarSesion();
        $searchModel = new GraderiasSillas();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);        
        $dataProvider->query->andWhere(['grad_estado'=>1]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'zona'=>$id
        ]);
    }

}