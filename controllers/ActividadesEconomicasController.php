<?php

namespace app\controllers;

use Yii;
use app\models\ActividadesEconomicas;
use app\models\SearchActividadesEconomicas;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;

/**
 * ActividadesEconomicasController implements the CRUD actions for ActividadesEconomicas model.
 */
class ActividadesEconomicasController extends Controller
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
     * Lists all ActividadesEconomicas models.
     * @return mixed
     */
    public function actionIndex()
    {    
        $this->verificarSesion();
        $searchModel = new SearchActividadesEconomicas();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Displays a single ActividadesEconomicas model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {   
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "Activididad economica eventual";
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('view', [
                        'model' => $this->findModel($id),
                    ]),
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"])
                ];    
        }else{
            return $this->render('view', [
                'model' => $this->findModel($id),
            ]);
        }
    }

    /**
     * Creates a new ActividadesEconomicas model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $model = new ActividadesEconomicas();  
        $model->activi_estado = 1;
        $titulo = "Crear actividad economica eventual";

        if($request->isAjax){
            /*   Process for ajax request       */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Guardar',['class'=>'btn btn-primary','type'=>"submit"])
        
                ];         
            }else if($model->load($request->post()) && $model->save()){
                return [
                    'forceReload'=>'#crud-datatable-pjax',
                    'title'=> $titulo,
                    'content'=>'<span class="text-success">Se creo los datos de la actividad economica.</span>',
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                            Html::a('Crear mas',['create'],['class'=>'btn btn-primary','role'=>'modal-remote'])
        
                ];         
            }else{           
                return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Guardar',['class'=>'btn btn-primary','type'=>"submit"])
        
                ];         
            }
        }else{
            /*
            *   Process for non-ajax request
            */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->activi_id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
       
    }

    /**
     * Updates an existing ActividadesEconomicas model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $model = $this->findModel($id);  
        $titulo = "Actualizar actividad economica"; 
       

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Guardar',['class'=>'btn btn-primary','type'=>"submit"])
                ];         
            }else if($model->load($request->post()) && $model->save()){
                return [
                    'forceReload'=>'#crud-datatable-pjax',
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('view', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                            Html::a('Actualizar',['update','id'=>$id],['class'=>'btn btn-primary','role'=>'modal-remote'])
                ];    
            }else{
                 return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Guardar',['class'=>'btn btn-primary','type'=>"submit"])
                ];        
            }
        }else{
            /*
            *   Process for non-ajax request
            */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->activi_id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }
    }

    /**
     * Delete an existing ActividadesEconomicas model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $this->findModel($id)->delete();

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceCerrar'=>true,'forceReload'=>'#crud-datatable-pjax'];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }


    }

    // funciones AJAX
     public function actionAjaxTipos($id){
        $model = ActividadesEconomicas::find()->where(['categ_id' =>$id, 'activi_estado'=>1])
                ->orderBy('activi_descripcion ASC')
                ->all();
        
        $resultado = "";
        if(!empty($model)): 
            echo "<option value=''> * Seleccione la actividad economica *</option>";
            foreach ($model as $fila) {
                echo  "<option value='".$fila->activi_id."'> ".$fila->activi_descripcion."</option>";
            }
        else:
            echo "<option value=''> * No existen datos de activ. economicas *</option>";
        endif;
                
    }
    
    public function actionAjaxActividadPrecios($id){
        $model = $this->findModel($id);
        // patente - sentaje - aseo dia - aseo por sitio - cobro_por dia
        $resultado = "0 - 0 - 0 - 0-false";
        if(!empty($model)): 
            $resultado = $model->activi_costo_patente." - ".$model->activi_costo_sentaje_dia." - ".
                $model->activi_costo_aseo_por_dia ." - ".$model->activi_costo_aseo_por_sitio
                ." - ".$model->activi_cobro_por_dia;
        
        endif;
        
        return $resultado;       
    }
     
    /**
     * Finds the ActividadesEconomicas model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ActividadesEconomicas the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ActividadesEconomicas::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
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
