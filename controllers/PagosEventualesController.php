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
use app\models\ActividadesEconomicas;
use app\models\Contribuyentes;
use app\models\SitiosEventuales;
use yii\helpers\VarDumper;

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
     *  @return mixed
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
        //$siteUrl = 'http://proyecto-urkupina.test/index.php?r=pagos%2Fview&id='.$id;
        $siteUrl = 'http://181.177.143.186/proyecto-urkupina/web/index.php?r=pagos-eventuales%2Fview&id='.$id;

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
                $model->eventual_cobrado=1;
                $dir=$model->eventual_nro_comprobante;
                //$codigos= (new QrCode())-> Generar($dir,$model->pago_nro_comprobante);
                $llamada=Yii::$app->generadorQR->TEXT($siteUrl);
                $llamada=Yii::$app->generadorQR->QRCODE(400,$dir);

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

        $idUsuarioAutenticado = Yii::$app->user->id;
        $datos = Usuario::findOne($idUsuarioAutenticado);
        $ci_usuarioAutenticado = $datos->usua_cuenta;

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
                $model->eventual_fecha_inicio=$porciones[0];   //aqui partimos la fecha
                $model->eventual_fecha_limite=$porciones[1];

   
                $montoTotal = $model->eventual_importe_total;
                $token = Yii::$app->ruatServices->login('SWTRAMITESURKUPINIAQUI', 'S1234567');
                if($token) { 
                    $id = $model->contri_id;
                    $contri = Contribuyentes::findOne($id);
                    $ci_contribuyente = $contri->contri_ci;
                    $contribuyente = Yii::$app->ruatServices->getContribuyentePorCi($token, $ci_contribuyente);  
                    if ($contribuyente) {
                      $tieneDeudas = Yii::$app->ruatServices->getTieneDeudaContribuyente($token, $contribuyente);
                      if ($tieneDeudas) {
                        return [
                            'forceReload' => '#crud-datatable-pjax',
                            'title' => $titulo,
                            'content' => '<span class="text-warning">' . 'El contribuyente seleccionado tiene deudas pendientes, no es posible registrar la TASA. </span>',
                        ];
                      }  
                      else {
                        $ideventual = $model->eventual_id;
                        
                        $idactividad = $model->activi_id;
                        $acti = ActividadesEconomicas::findOne($idactividad);

                        $idsitio = $model->sitios_id;
                        $sitio = SitiosEventuales::findOne($idsitio);
                        
                        $obs = 'Actividades economicas eventuales'.
                               ' actividad economica '. $acti->activi_descripcion . 
                               ' Ubicacion '. $sitio->sitios_descripcion .
                               ' Nro de Puesto '. $sitio->sitios_numero_sitio ;
                              // ' fecha inicio '. $event->eventual_fecha_inicio .
                              // ' fecha fin '. $event->eventual_fecha_limite;
                              // ' CodigoSitio: '. $sitio->sitios_codigo .
                         
                        $tasa = Yii::$app->ruatServices->createTasa($token, $ci_usuarioAutenticado, $contribuyente, '24977', $montoTotal, $obs);     
                        if ($tasa) {
                            if($model->save()):
                                $id=$model->eventual_id;
                                // VarDumper::dump($tasa);    
                                $sql = 'UPDATE pagos_eventuales SET eventual_tasa=:tasa WHERE eventual_id=:id';
                                $command = Yii::$app->db->createCommand($sql)
                                    ->bindValue(':id', $id)
                                    ->bindValue(':tasa', $tasa)
                                    ->queryOne();
   
                                return [
                                'forceReload'=>'#crud-datatable-pjax',
                                'title'=> $titulo,
                                'content'=>'<span class="text-success">Se registro con exito los datos de la act. economica  eventual</span>',
                                'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                        Html::a('Recibo preliquidacion',['preliquidacion-sitios', 'id'=>$model->eventual_id],['class'=>'btn btn-primary','role'=>'modal-remote'])
                            ];   
                            endif;
                        }
                        else {
                            return [
                                'forceReload' => '#crud-datatable-pjax',
                                'title' => $titulo,
                                'content' => '<span class="text-warning">' . 'no se ha registrado la tasa en RUAT. Verifique por favor </span>',
                                'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"])
                            ];
                        }
                    }
                } else {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $titulo,
                        'content' => '<span class="text-warning">' . 'El contribuyente seleccionado no se encuentra registrado en RUAT. Verifique por favor </span>',
                        'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"])
                    ];
                 }
                }
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
        $model->eventual_cobrado = 0;  
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
                $model->eventual_fecha_inicio=$porciones[0];//aqui partimos las fechas
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
        $mensaje = '';
        $resultado = false;

        /**Get CI auth user */
        $request = Yii::$app->request;
        $idUsuarioAutenticado = Yii::$app->user->id;
        $usuarioAutenticado = Usuario::findOne($idUsuarioAutenticado);
        $ci_usuarioAutenticado = $usuarioAutenticado->usua_cuenta;

        $request = Yii::$app->request;
        $model = new PagosEventuales();    
        $model -> scenario = "crear_espectaculos_liquidacion";
        $model -> eventual_preliquidacion = 1;
        $model -> eventual_fecha_hora_liquidacion = date('Y-m-d H:m:s');
        $model -> eventual_user_id_preliquidacion = Yii::$app->user->id;
        $model -> eventual_costo_comprobante=$model::COMPROBANTE;        
        $model ->  eventual_cantidad_sitio = 0;
        $model -> eventual_costo_sentaje = 0;
        
        $titulo = "Preliquidacion patente por espectaculos, exposicion y funcion";

        if($request->isAjax){
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
                $token = Yii::$app->ruatServices->login('SWTRAMITESURKUPINIAQUI', 'S1234567');
                if ($token) {
                    $id = $model->contri_id;
                    $contri = Contribuyentes::findOne($id);
                    $ci_contribuyente = $contri->contri_ci;
                    $codigoContribuyente = Yii::$app->ruatServices->getContribuyentePorCi($token, $ci_contribuyente);
                    if ($codigoContribuyente) {
                        $tieneDeudas = Yii::$app->ruatServices->getTieneDeudaContribuyente($token, $ci_contribuyente);
                        if ($tieneDeudas) {
                            $mensaje = 'El contribuyente seleccionado tiene deudas pendientes, no podemos registrar la preliquidación';
                        } else {
                            $montoTotal = $model->eventual_importe_total;
                            if ($model->save()) {
                                $pagoEventual = PagosEventuales::findOne($model->eventual_id);
                                $obs = 'Datos espectáculo => ' .
                                    ' Nro liquidación: ' . $pagoEventual->eventual_nro_liquidacion .
                                    ' Nro tasa RUAT: ' . $pagoEventual->eventual_importe_total;
                                $obsTest = 'Datos espectaculo ';
                                $nroTasa = Yii::$app->ruatServices->createTasa($token, $ci_usuarioAutenticado, $codigoContribuyente, '24976', $montoTotal, $obsTest);
                                if ($nroTasa) {
                                    $resultado = true;
                                    $id = $model->eventual_id;
                                    $sql = 'UPDATE pagos_eventuales SET eventual_tasa=:tasa WHERE eventual_id=:id';
                                    $command = Yii::$app->db->createCommand($sql)
                                    ->bindValue(':id', $id)
                                    ->bindValue(':tasa', $nroTasa)
                                    ->queryOne();
                                    $mensaje = 'Se registro los datos de la preliquidación con exito en RUAT';
                                } else {
                                    $mensaje = 'No se pudo registrar la preliquidacion en RUAT';
                                }
                            } else {
                                $mensaje = 'No se pudo registrar la preliquidacion';
                            }
                        }
                    } else {
                        $mensaje = 'No se pudo registrar la tasa, porque el contribuyente seleccionado no se encuentra registrado en RUAT, por favor registrar contribuyente.';
                    }
                } else{
                    $mensaje = 'No se pudo iniciar sesión en RUAT';
                }        
                if ($resultado) {
                    return [
                        'forceReload'=>'#crud-datatable-pjax',
                        'title'=> $titulo,
                        'content'=>'<span class="text-success">'.$mensaje.'</span>',
                        'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::a('Recibo preliquidacion',['preliquidacion-actividades', 'id'=>$model->eventual_id],['class'=>'btn btn-primary','role'=>'modal-remote'])
                    ];
                }else{
                    return [
                        'forceReload'=>'#crud-datatable-pjax',
                        'title'=> $titulo,
                        'content'=>'<span class="text-danger">'.$mensaje.'</span>',
                        'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"])
                    ];
                }            
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
        $idUsuario = Yii::$app->user->id;
        
        $datos = Usuario::findOne($idUsuario);
        $username = $datos->usua_cuenta;

        $model = new PagosEventuales(); 
        $model->scenario = "crear_publicidad_liquidacion";
        //////////Auth
        $token = Yii::$app->ruatServices->login('SWTRAMITESURKUPINIAQUI', 'S1234567');
        /////////
        $model->eventual_preliquidacion = 1;
        $model->eventual_fecha_hora_liquidacion = date('Y-m-d H:m:s');
        $model->eventual_costo_comprobante=$model::COMPROBANTE; 
        $model->eventual_user_id_preliquidacion = Yii::$app->user->id;
        $model->eventual_cantidad_dia = 1;
        $model->eventual_costo_sentaje= 0;
        //$model->usua_id = $idUsuario;
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
               //////////////////////////////////AUTH Inicio///////////////////////////////
              
                if ($token) {
                    $id = $model->contri_id;
                    $contri = Contribuyentes::findOne($id);
                    $ci_contribuyente = $contri->contri_ci;
                    $acti= ActividadesEconomicas::findOne($model->activi_id);
                    $contribuyente = Yii::$app->ruatServices->getContribuyentePorCi($token, $ci_contribuyente);
                    //VarDumper::dump($contribuyente);
                    if ($contribuyente) {
                        $tieneDeudas = false; //Yii::$app->ruatServices->getTieneDeudaContribuyente($token, $ci_contribuyente);
                        
                        if ($tieneDeudas) {
                            return [
                                'forceReload' => '#crud-datatable-pjax',
                                'title' => $titulo,
                                'content' => '<span class="text-warning">' . 'El contribuyente seleccionado tiene deudad pendientes, no podemos registrar la tasa. </span>',
                            ];
                       
                        }
                    }
                }
               
               
               ////////////////////////////////////FIn de Autht////////////////////////////
                if($model->save()):
                    
                    $pagoE = PagosEventuales::findOne($model->eventual_id);
                    $id=$pagoE->eventual_id;
                    $montoTotal=$pagoE->eventual_importe_total;
                    $plq= $pagoE->eventual_nro_liquidacion;
                    $obs = 'Publicidad Actividad Economica ' .$acti->activi_descripcion .' NroPreliquidacion ' .$plq;
                    ///$obs = $obs  .' Por ' .$pagoE->eventual_cantidad_dia  .' Dias EN FECHAS ' .$pagoE->eventual_fecha_inicio;
                    //$obs = $obs  .' a ' .$pagoE->eventual_fecha_limite .' Para ' .$pagoE->eventual_cantidad_sitio .' Puestos';

                    $tasa = Yii::$app->ruatServices->createTasa($token, $username, $contribuyente, '24976', $montoTotal, $obs);
                     if($tasa){
                            $sql = 'UPDATE pagos_eventuales SET eventual_tasa=:tasa WHERE eventual_id=:id';
                            $command = Yii::$app->db->createCommand($sql)
                                ->bindValue(':id', $id)
                                ->bindValue(':tasa',$tasa)
                                ->queryOne();
                    }    
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
        //$model->eventual_anulado= 1;
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
        //$model->eventual_cobrado=1;
        $montoLiteral = $model->montoTotalLiteral();
        $titulo = "COMPROBANTE DE PAGO";
        $url = "";
        //$qrImagePath = realpath($_SERVER['DOCUMENT_ROOT']);
        //$qrImagePath = "C:\laragon\www\proyecto-urkupina\web";

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            // jasper init
            $archivo = "comprobante_eventuales2";
            //$parametros = ['id_pago' => $id, 'monto_literal' => '"'.$montoLiteral.'"', 'image_path' => '"'.$qrImagePath.'"'];
            $parametros = ['id_pago' => $id, 'monto_literal' => '"'.$montoLiteral.'"'];
            $url = $this->generarURLReportePdf('reportes', $archivo, $parametros);
            //end jasper
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('preliquidacion-sitios', [
                    'url' => $url,
                    'size' => 'modal-lg',
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
        $model = $this->findModel($id);
        $montoLiteral = $model->montoTotalLiteral();
        $titulo = "RECIBO DE LIQUIDACION  DE SITIOS ";
        $url = "";

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            // jasper init
            $archivo = "preliquidacion_sitios3";
            $carpeta =  "reportes";
            $parametros = ['id_pago' => $id, 'monto_literal' => '"'.$montoLiteral.'"'];
            $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);
            //end jasper
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('preliquidacion-sitios', [
                    'url' => $url,
                    'size' => 'modal-lg',
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
        $model = $this->findModel($id);
        $montoLiteral = $model->montoTotalLiteral();
        $titulo = "RECIBO DE PRELIQUIDACION DE ACTIV. ECONOMICAS ";
        $url = "";

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            // jasper init
            $archivo = "preliquidacion_actividades_economicas";
            $carpeta = "reportes";
            $parametros = ['id_pago' => $id, 'monto_literal' => '"'.$montoLiteral.'"'];
            $url = $this -> generarURLReportePdf($carpeta,$archivo,$parametros);
            //end jasper
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('preliquidacion-actividades', [
                    'url' => $url,
                    'size' => 'modal-lg',
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

    public function actionReporteEventualesPagados() {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DE SITIOS EVENTUALES PAGADOS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_eventuales_pagos";
        $carpeta = "reportes/eventuales";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);
        
        $parametros = ['logo_path' => '"'.$logoImagePath.'"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('reporte-eventuales-pagados', ['url' => $url]);
    }

    public function actionReporteEventualesNopagados() {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DE SITIOS EVENTUALES NO PAGADOS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_eventuales_sin_pagos";
        $carpeta = "reportes/eventuales";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);
        
        $parametros = ['logo_path' => '"'.$logoImagePath.'"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('reporte-eventuales-nopagados', ['url' => $url]);
    }

    public function actionReporteAlasitasPagados() {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DE ALASITAS PAGADOS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_alasitas_pagos";
        $carpeta = "reportes/eventuales";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);
        
        $parametros = ['logo_path' => '"'.$logoImagePath.'"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('reporte-alasitas-pagados', ['url' => $url]);
    }

    public function actionReporteAlasitasNopagados() {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DE ALASITAS NO PAGADOS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_alasitas_sin_pagos";
        $carpeta = "reportes/eventuales";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);
        
        $parametros = ['logo_path' => '"'.$logoImagePath.'"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('reporte-alasitas-nopagados', ['url' => $url]);
    }

    protected function generarURLReportePdf($carpeta, $file, $parametros = []) {

        $archivo = $file;
        Yii::setAlias('@ruta', $carpeta);

        $jasper = Yii::$app->jasper;
        $jasper->compile(Yii::getAlias('@ruta') . '/' . $archivo . '.jrxml')->execute();
        $jasper->process(
                Yii::getAlias('@ruta') . '/' . $archivo . '.jasper', $parametros, ['pdf'], false)->execute();
        $url = \Yii::getAlias('@ruta') . '/' . $archivo . '.pdf';
        return $url;
    }

}
