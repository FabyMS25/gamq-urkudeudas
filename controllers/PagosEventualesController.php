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

/**
 * PagosEventualesController implements the CRUD actions for PagosEventuales model.
 */
class PagosEventualesController extends Controller
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
     * Lists all PagosEventuales models.
     * @return mixed
     */
    public function actionIndex()
    {   
        $this->verificarSesion();
        
        $searchModel = new SearchPagosEventuales();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
         $dataProvider->query->andWhere(['IS ', 'eventual_fecha_hora_pago',  NULL]);
        $dataProvider->query->andFilterWhere(['eventual_estado'=>1]);
       

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
     public function actionPagados()
    {   
        $this->verificarSesion();
        
        $searchModel = new SearchPagosEventuales();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['IS NOT ', 'eventual_fecha_hora_pago',  NULL]);
        $dataProvider->query->andFilterWhere(['eventual_estado'=>1]);
        
        if(Usuario::getRolCajero()){
            $dataProvider->query->andFilterWhere(['usua_id' => \Yii::$app->user->id]);
        }
       

        return $this->render('pagados', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
     public function actionAnulados()
    {   
        $this->verificarSesion();
        
        $searchModel = new SearchPagosEventuales();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['IS NOT ', 'eventual_fecha_hora_pago',  NULL]);
        $dataProvider->query->andWhere(['eventual_anulado' => 1]);
        $dataProvider->query->andFilterWhere(['eventual_estado'=>1]); 
        if(Usuario::getRolCajero()){
            $dataProvider->query->andFilterWhere(['usua_id' => \Yii::$app->user->id]);
        }

        return $this->render('anulados', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Displays a single PagosEventuales model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {   
        $this->verificarSesion();
        $request = Yii::$app->request;
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                    'title'=> "Datos de la actividad economica ",
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
    
    
    // listado de sitios eventuales  para la preliquidacion en alasitas y urkupina
     public function actionEventualesAlasitas()
    {   
        $this->verificarSesion();
        
        $searchModel = new \app\models\SearchSitiosEventuales();
        $listaSitios = (new PagosEventuales())->listaIdsSitiosEventuales();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);        
        $dataProvider->query->andFilterWhere(['sitios_estado' => 1]);
        $dataProvider->query->andFilterWhere(['NOT IN', 'sitios_id', $listaSitios]);

        return $this->render('eventuales-alasitas', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new PagosEventuales model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCobrarLiquidacion($id)
    {
        $this->verificarSesion();
        
        $request = Yii::$app->request;
        $model = $this->findModel($id);  
        $model->scenario = "cobrar_liquidacion";
        $titulo = "Cobrar liquidacion nro. <strong>".$model->eventual_nro_liquidacion."</strong>";

        if($request->isAjax){            
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('cobrar-liquidacion', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Guardar',['class'=>'btn btn-primary','type'=>"submit"])        
                ];         
            }else if($model->load($request->post()) && $model->validate()){
                $model->eventual_fecha_hora_pago = date('Y-m-d H:m:s');
                $model->usua_id = Yii::$app->user->id;
                if($model->save()){
                   return [
                        'forceReload'=>'#crud-datatable-pjax',
                        'title'=> $titulo,
                        'content'=>'<span class="text-success">Se registro el pago.</span>',
                        'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::a('Comprobante',['comprobante-pago', 'id'=>$model->eventual_id],['class'=>'btn btn-primary','role'=>'modal-remote'])
                    ];    
                } 
                      
            }else{           
                return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('cobrar-liquidacion', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                               Html::button('Guardar',['class'=>'btn btn-primary','type'=>"submit"])
        
                ];         
            }
        }else{
            /*       *   Process for non-ajax request            */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['index']);
            } else {
                return $this->render('cobrar-liquidacion', [
                    'model' => $model,
                ]);
            }
        }       
    }
    
    // liquidacion de act. economicas eventuales
    public function actionCreateEventual($id)
    {
         $this->verificarSesion();
        $request = Yii::$app->request;
        $model = new PagosEventuales(); 
       
        $model->scenario = "crear_eventual_liquidacion";
        $model->patente =0;
        $model->sentaje =0;
        $model->aseo =0;
        $model->sitios_id = $id;
        $model->eventual_preliquidacion = 1;
        $model->eventual_fecha_hora_liquidacion = date('Y-m-d H:m:s');
        $model->eventual_costo_comprobante=$model::COMPROBANTE; 
        $model->eventual_user_id_preliquidacion = Yii::$app->user->id;
        $titulo = "Preliquidacion de actividades economicas eventuales";

        if($request->isAjax){
            //$this->verificarSesion();
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('create-eventual', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Guardar',['class'=>'btn btn-primary','type'=>"submit"])
        
                ];         
            }else if($model->load($request->post()) && $model->validate()){
               
                $porciones = explode(" a ", $model->rango_fechas);
                $model->eventual_fecha_inicio=$porciones[0];
                $model->eventual_fecha_limite=$porciones[1];
                if($model->save()):
                    return [
                    'forceReload'=>'#crud-datatable-pjax',
                    'title'=> $titulo,
                    'content'=>'<span class="text-success">Se registro con exito los datos de la act. economica  eventual</span>',
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                            Html::a('Recibo preliquidacion',['preliquidacion-sitios', 'id'=>$model->eventual_id],['class'=>'btn btn-primary','role'=>'modal-remote'])
                ];   
                endif;
                      
            }else{           
                return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('create-eventual', [
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
                return $this->redirect(['view', 'id' => $model->eventual_id]);
            } else {
                return $this->render('create-eventual', [
                    'model' => $model,
                ]);
            }
        }
       
    }
    
 

        // liquidacion de act. economicas eventuales
    public function actionCreateAlasitas($id)
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $model = new PagosEventuales(); 
        $model->scenario = "crear_alasitas_liquidacion";        
        $model->sitios_id = $id;
        $model->patente =0;
        $model->sentaje =0;
        $model->aseo =0;        
        $model->eventual_preliquidacion = 1;
        $model->eventual_fecha_hora_liquidacion = date('Y-m-d H:m:s');
        $model->eventual_costo_comprobante=$model::COMPROBANTE; 
        $model->eventual_user_id_preliquidacion = Yii::$app->user->id;
        $model-> eventual_cantidad_dia = 0;
        $model->eventual_costo_sentaje = 0;   
        $titulo = "Preliquidacion  alasitas";

        if($request->isAjax){
            
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('create-alasitas', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Guardar',['class'=>'btn btn-primary','type'=>"submit"])
        
                ];         
            }else if($model->load($request->post()) && $model->validate()){               
                $porciones = explode(" a ", $model->rango_fechas);
                $model->eventual_fecha_inicio=$porciones[0];
                $model->eventual_fecha_limite=$porciones[1];
                if($model->save()):
                    return [
                    'forceReload'=>'#crud-datatable-pjax',
                    'title'=> $titulo,
                    'content'=>'<span class="text-success">Se registro con exito los datos de la act. economica  eventual</span>',
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                            Html::a('Recibo preliquidacion',['preliquidacion-actividades', 'id'=>$model->eventual_id],['class'=>'btn btn-primary','role'=>'modal-remote'])
                ];   
                endif;
                      
            }else{           
                return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('create-alasitas', [
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
                return $this->redirect(['view', 'id' => $model->eventual_id]);
            } else {
                return $this->render('create-eventual', [
                    'model' => $model,
                ]);
            }
        }
       
    }
    
     // liquidacion de act. economicas eventuales
    public function actionCreateEspectaculo()
    {
         $this->verificarSesion();
        $request = Yii::$app->request;
        $model = new PagosEventuales();    
        $model->scenario = "crear_espectaculos_liquidacion";
        $model->eventual_preliquidacion = 1;
        $model->eventual_fecha_hora_liquidacion = date('Y-m-d H:m:s');
        $model->eventual_user_id_preliquidacion = Yii::$app->user->id;
        $model->eventual_costo_comprobante=$model::COMPROBANTE;        
        $model-> eventual_cantidad_sitio = 0;
        $model->eventual_costo_sentaje = 0;
        
        $titulo = "Preliquidacion patente por espectaculos, exposicion y funcion";

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('create-espectaculo', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Guardar',['class'=>'btn btn-primary','type'=>"submit"])
        
                ];         
            }else if($model->load($request->post()) && $model->validate()){               
                $porciones = explode(" a ", $model->rango_fechas);
                $model->eventual_fecha_inicio=$porciones[0];
                $model->eventual_fecha_limite=$porciones[1];
                
                if($model->save()):
                    return [
                        'forceReload'=>'#crud-datatable-pjax',
                        'title'=> $titulo,
                        'content'=>'<span class="text-success">Se registro con exito los datos de la act. economica  eventual</span>',
                        'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::a('Recibo preliquidacion',['preliquidacion-actividades', 'id'=>$model->eventual_id],['class'=>'btn btn-primary','role'=>'modal-remote'])
                    ];   
                endif;
                      
            }else{           
                return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('create-espectaculo', [
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
                return $this->redirect(['view', 'id' => $model->eventual_id]);
            } else {
                return $this->render('create-espectaculo', [
                    'model' => $model,
                ]);
            }
        }
       
    }
    
     // liquidacion de act. economicas eventuales
    public function actionCreatePublicidad()
    {
         $this->verificarSesion();
        $request = Yii::$app->request;
        $model = new PagosEventuales(); 
        $model->scenario = "crear_publicidad_liquidacion";
        
        $model->eventual_preliquidacion = 1;
        $model->eventual_fecha_hora_liquidacion = date('Y-m-d H:m:s');
        $model->eventual_costo_comprobante=$model::COMPROBANTE; 
        $model->eventual_user_id_preliquidacion = Yii::$app->user->id;
        $model->eventual_cantidad_dia = 1;
        $model->eventual_costo_sentaje= 0;
        
        $titulo = "Preliquidacion de publicidad";

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('create-publicidad', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Guardar',['class'=>'btn btn-primary','type'=>"submit"])
        
                ];         
            }else if($model->load($request->post()) && $model->validate()){
               
                $porciones = explode(" a ", $model->rango_fechas);
                $model->eventual_fecha_inicio=$porciones[0];
                $model->eventual_fecha_limite=$porciones[1];
                if($model->save()):
                    return [
                    'forceReload'=>'#crud-datatable-pjax',
                    'title'=> $titulo,
                    'content'=>'<span class="text-success">Se registro con exito los datos de la act. economica  eventual</span>',
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                            Html::a('Recibo preliquidacion',['preliquidacion-actividades', 'id'=>$model->eventual_id],['class'=>'btn btn-primary','role'=>'modal-remote'])
                ];   
                endif;
                      
            }else{           
                return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('create-publicidad', [
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
                return $this->redirect(['view', 'id' => $model->eventual_id]);
            } else {
                return $this->render('create-publicidad', [
                    'model' => $model,
                ]);
            }
        }
       
    }


   

    /**
     * Delete an existing PagosEventuales model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    //eventual_estado
    
    public function actionAnularLiquidacion($id)
    {
         
       $this->verificarSesion();
       
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $model->eventual_estado = 0;
        $resultado = $model->save(false);
        $mensaje = $resultado? "<span class='text-success'>Se anulo la liquidacion con exito.</span>": "<span class='text-danger'>Error no se pudo anular la liquidacion.</span>";
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                    'forceReload'=>'#crud-datatable-pjax',
                    'title'=> "Anular liquidacion nro. ".$model->eventual_nro_liquidacion,
                    'content'=> $mensaje,
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"])
                ];    
        }else{
            return $this->render('index');
        }

    }
    public function actionDelete($id)
    {
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

     /**
     * Delete multiple existing PagosEventuales model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionBulkDelete()
    {        
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
            return ['forceCerrar'=>true,'forceReload'=>'#crud-datatable-pjax'];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }
       
    }
    
    /************************************************/
    // reportes jasper
    
     public function actionComprobantePago($id) {
        $this->verificarSesion();
        
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $titulo = "COMPROBANTE DE PAGO";
        $url = "";

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            // jasper init
            $archivo = "comprobante_eventuales2";
            Yii::setAlias('@ruta', 'reportes');

            $jasper = Yii::$app->jasper;
            $jasper->compile(Yii::getAlias('@ruta') . '/' . $archivo . '.jrxml')->execute();
            $jasper->process(
                            Yii::getAlias('@ruta') . '/' . $archivo . '.jasper', ['id_pago' => $id], ['pdf'], false)
                    ->execute();
            $url = \Yii::getAlias('@ruta') . '/' . $archivo . '.pdf';

            //end jasper
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('preliquidacion-sitios', [
                    'url' => $url,
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('preliquidacion-sitios', [
                        'url' => $url,
            ]);
        }
    }
     
    public function actionPreliquidacionSitios($id) {
        $this->verificarSesion();
        
        $request = Yii::$app->request;
        //$model = $this->findModel($id);
        $titulo = "RECIBO DE LIQUIDACION  DE SITIOS ";
        $url = "";

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            // jasper init
            $archivo = "preliquidacion_sitios3";
            Yii::setAlias('@ruta', 'reportes');

            $jasper = Yii::$app->jasper;
            
            $jasper->compile(Yii::getAlias('@ruta') . '/' . $archivo . '.jrxml')->execute();
            $jasper->process(
                            Yii::getAlias('@ruta') . '/' . $archivo . '.jasper', ['id_pago' => $id], ['pdf'], false)
                    ->execute();

            $url = \Yii::getAlias('@ruta') . '/' . $archivo . '.pdf';

            //end jasper
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('preliquidacion-sitios', [
                    'url' => $url,
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('preliquidacion-sitios', [
                        'url' => $url,
            ]);
        }
    }
    
    public function actionPreliquidacionActividades($id) {
        $this->verificarSesion();
        
        $request = Yii::$app->request;
        //$model = $this->findModel($id);
        $titulo = "RECIBO DE PRELIQUIDACION DE ACTIV. ECONOMICAS ";
        $url = "";

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            // jasper init
            $archivo = "preliquidacion_actividades_economicas";
            Yii::setAlias('@ruta', 'reportes');

            $jasper = Yii::$app->jasper;
            $jasper->compile(Yii::getAlias('@ruta') . '/' . $archivo . '.jrxml')->execute();
            $jasper->process(
                            Yii::getAlias('@ruta').'/'.$archivo.'.jasper', ['id_pago' => $id], ['pdf'], false)
                    ->execute();
            $url = \Yii::getAlias('@ruta').'/'.$archivo.'.pdf';

            //end jasper
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('preliquidacion-actividades', [
                    'url' => $url,
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('preliquidacion-actividades', [
                        'url' => $url,
            ]);
        }
    }

    /**
     * Finds the PagosEventuales model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PagosEventuales the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PagosEventuales::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    // funcion que verifica la existencia de una sesion activa
    public function verificarSesion() {
        if (Yii::$app->user->isGuest) {
            Yii::$app->user->logout(true);
            return $this->goHome();
        }
    }
}
