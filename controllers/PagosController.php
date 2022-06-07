<?php

namespace app\controllers;
//require 'C:\laragon\www\proyecto-urkupina\web\phpqrcode\qrlib.php';
use Yii;
use app\models\Pagos;
//use app\models\QrCode;
use app\models\SearchPagos;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;
use app\models\Usuario;
use chrmorandi\jasper\Jasper;
/**
 * PagosController implements the CRUD actions for Pagos model.
 */
class PagosController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
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
     * Lists all Pagos models.
     * @return mixed
     */
    public function actionIndex() {
        $this->verificarSesion();
        $listaGraderia = (new Pagos())->listaIdGraderiasSillasPreliquidados();
        $searchModel = new \app\models\SearchGraderiasSillas();
        $dataProvider = $searchModel->searchPreliquidaciones(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['grad_estado' => 1]);
        $dataProvider->query->andFilterWhere(['grad_vendido'=> 0]);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    public function actionGeneral() {
        $this->verificarSesion();
        $searchModel = new SearchPagos();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['pago_estado' => 1]);

        //'pago_cobrado' => 1, 'pago_anulado' => 0

        return $this->render('general', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    public function actionPreliquidaciones() {
        $this->verificarSesion();
        
        //
        $searchModel = new SearchPagos();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['pago_estado'=> 1,'pago_preliquidacion' => 1, 'pago_cobrado' => 0]);
        if(Usuario::getRolPreli()){
            $dataProvider->query->andFilterWhere(['pago_id_user_preliquidacion' => \Yii::$app->user->id]);
        }

        return $this->render('preliquidaciones', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    public function actionPagados() {
        $this->verificarSesion();
        $searchModel = new SearchPagos();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['pago_estado' => 1, 'pago_cobrado' => 1, 'pago_anulado' => 0]);
        if(Usuario::getRolCajero()){
            $dataProvider->query->andFilterWhere(['usua_id' => \Yii::$app->user->id]);
        }

        return $this->render('pagados', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAnulados() {
        $this->verificarSesion();
        $searchModel = new SearchPagos();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['pago_estado' => 1,'pago_preliquidacion' => 1, 'pago_anulado' => 1,]);
        if(Usuario::getRolCajero()){
            $dataProvider->query->andFilterWhere(['usua_id' => \Yii::$app->user->id]);
        }

        return $this->render('anulados', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Pagos model.
     * @param integer $id
     * @return mixed
     */
    public function actionCobrar($id) {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $model->scenario = "cobrar_graderias_sillas";
        $titulo = "Cobrar preliquidacion de " . $model->graderiaSilla->grad_codigo;
        
        if ($request->isAjax) {
            /*           Process for ajax request            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('cobrar', ['model' => $model,]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else if ($model->load($request->post()) && $model->validate()) {
                $model->usua_id = Yii::$app->user->id;
                $model->pago_fecha_hora_cobro = date('Y-m-d H:m:s');
                $model->pago_cobrado=1;
                $dir=$model->pago_nro_comprobante;
                //$codigos= (new QrCode())-> Generar($dir,$model->pago_nro_comprobante);
                $llamada=Yii::$app->insertar->TEXT("URKUPIÑA");
                $llamada=Yii::$app->insertar->QRCODE(400,$dir);
                
                
                
                if ($model->save())
                   $resultado= true;
                   else
                  $resultado = false;
                //$resultado =$this->actualizarDatosCobro($model);
                $mensaje = ($resultado ? "Se realizo el cobro correctamente" : " Error al realizar el cobro");
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => $titulo,
                    'content' => '<span class="text-success">' . $mensaje . '<br> Nro. preliquidacion : ' . $model->pago_nro_liquidacion .
                    ' <br> Importe total Bs.: ' . $model->pago_importe_total . '  </span>',
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::a('Comprobante pago', ['comprobante-pago', 'id' => $id], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                ];
            } else {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('cobrar', ['model' => $model,]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            }
        } else {
            /*
             *   Process for non-ajax request
             */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->pago_id]);
            } else {
                return $this->render('cobrar', [
                            'model' => $model,
                ]);
            }
        }
    }

    public function actionView($id) {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "Detalle";
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('view', [
                    'model' => $this->findModel($id),
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('view', [
                        'model' => $this->findModel($id),
            ]);
        }
    }

    /**
     * Creates a new Pagos model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionPreliquidar($id) {
        $this->verificarSesion();
        $request = Yii::$app->request;

        $model = new Pagos();
        $model->grad_id = $id;
        $model->pago_reposicion = $model::COMPROBANTE;
        $model->pago_id_user_preliquidacion = Yii::$app->user->id; // id user sesion
        $model->pago_estado = 1;
        $model->pago_fecha_hora_preliquidacion = date('Y-m-d H:m:s');
        $model->pago_preliquidacion = 1;
        $modelSitio = \app\models\GraderiasSillas::findOne($id);
        $titulo = "Preliquidacion para el codigo <strong> " . $modelSitio->grad_codigo . "</strong>";

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($request->isGet) {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('preliquidar', [
                        'model' => $model,
                        'modelSitio' => $modelSitio,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else if ($model->load($request->post()) && $model->validate()) {
                $codigo=$model->grad_id;
                $resto=$modelSitio->grad_longitud - $model->pago_longitud_modificada;
                $model->pago_cobrado = 0;
                $val=0;
                if ($resto==0) 
                {
                    $val=1; 
                }
                $sql='UPDATE graderias_sillas SET grad_vendido=:val, grad_longitud=:rest WHERE grad_id=:id';
                $command= Yii::$app->db->createCommand($sql)
                                 ->bindValue(':id', $codigo)
                                 ->bindValue(':val', $val)
                                 ->bindValue(':rest',$resto)
                                 ->queryOne();

                if ($model->save())
                   $resultado= true;
                   else
                  $resultado = false;
                //$resultado =$this->actualizarDatosCobro($model);
                $mensaje = ($resultado ? "Transaccion Exitosa,  " : " Error al realizar el cobro NO");
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => $titulo,
                    'content' => '<span class="text-success">'. $mensaje . 'Se registro los datos de la preliquidacion. </span>',
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::a('recibo preliquidacion', ['recibo-liquidacion', 'id' => $model->pago_id], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                ];
            } else {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('preliquidar', [
                        'model' => $model,
                        'modelSitio' => $modelSitio,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            }
        } else {
            /*
             *   Process for non-ajax request
             */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->pago_id]);
            } else {
                return $this->render('create', [
                            'model' => $model,
                ]);
            }
        }
    }

    public function actionCreate() {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $model = new Pagos();

        if ($request->isAjax) {
            /*
             *   Process for ajax request
             */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => $titulo,
                    'content' => '<span class="text-success">Create Pagos success</span>',
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::a('Create More', ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                ];
            } else {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            }
        } else {
            /*
             *   Process for non-ajax request
             */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->pago_id]);
            } else {
                return $this->render('create', [
                            'model' => $model,
                ]);
            }
        }
    }

    /**
     * Updates an existing Pagos model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionAnularPago($id) {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $model->scenario = "anular-pago";
        $titulo = "Anular comprobante " . $model->pago_nro_comprobante;
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('anular-pago', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else if ($model->load($request->post()) && $model->validate()) {
                $res = $this->habilitarDatosPreliquidacion($model);
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => $titulo,
                    'content' => '<span class="text-success">' . ($res ? "Comprobante anulado " : "Error, el comprobante no se anulo.") . '</span>',
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
                ];
            } else {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('anular-pago', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                    Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            }
        } else {
            /*
             *   Process for non-ajax request
             */
            if ($model->load($request->post()) && $model->validate()) {
                $res = $model->habilitarDatosPreliquidacion($model);
                return $this->redirect(['pagados']);
            }
        }
    }

    // funcion para habilitar datos de preliquidacion porq se anulo su pago
    protected function habilitarDatosPreliquidacion($model) {

        $model->pago_anulado = 1;
        $model->pago_anulado_fecha_hora = date('Y-m-d H:m:s');

        $modelAux = new Pagos();
        $modelAux->grad_id = $model->grad_id;
        $modelAux->contri_id = $model->contri_id;
        $modelAux->tip_arm_id = $model->tip_arm_id;
        $modelAux->pago_nro_liquidacion = $model->pago_nro_liquidacion;

        $modelAux->pago_longitud_modificada = $model->pago_longitud_modificada;
        $modelAux->pago_importe_patente = $model->pago_importe_patente;
        $modelAux->pago_aseo = $model->pago_aseo;
        $modelAux->pago_reposicion = $model->pago_reposicion;
        $modelAux->pago_descuento_porcentaje = $model->pago_descuento_porcentaje;
        $modelAux->pago_descuento_monto = $model->pago_descuento_monto;
        $modelAux->pago_importe_total = $model->pago_importe_total;

        $modelAux->pago_id_user_preliquidacion = $model->pago_id_user_preliquidacion;
        $modelAux->pago_preliquidacion = $model->pago_preliquidacion;
        $modelAux->pago_fecha_hora_preliquidacion = $model->pago_fecha_hora_preliquidacion;
        $modelAux->pago_estado = 1;
        $modelAux->pago_con_exencion = $model->pago_con_exencion;

        $resultado = false;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($modelAux->save(false) && $model->save(false)) {
                $transaction->commit();
                $resultado = true;
            } else {
                $transaction->rollBack();
            }
        } catch (Exception $e) {
            $transaction->rollBack();
        }
        return $resultado;
    }

    /**
     * Delete an existing Pagos model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionAnularPreliquidacion($id) {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $nro_preliquidacion = $model->pago_nro_liquidacion;
        $model->pago_estado = 0;

        // modelo graderias y sillas
        $modelGraderia = \app\models\GraderiasSillas::findOne($model->grad_id);
        $modelGraderia->grad_vendido = 0;
        $resultado = false;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($modelGraderia->save(false) && $model->save(false)) {
                $transaction->commit();
                $resultado = true;
            } else {
                $transaction->rollBack();
            }
        } catch (Exception $e) {
            $transaction->rollBack();
        }

        $mensaje = ($resultado ? "Eliminado la preliquidacion " . $nro_preliquidacion : "Error, no se elimino la preliquidacion " . $nro_preliquidacion);


        if ($request->isAjax) {
            /*   Process for ajax request             */
            Yii::$app->response->format = Response::FORMAT_JSON;
            // return ['forceCerrar' => true, 'forceReload' => '#crud-datatable-pjax'];
            return [
                'forceReload' => '#crud-datatable-pjax',
                'title' => "Anular preliquidacion " . $model->pago_nro_liquidacion,
                'content' => '<span class="text-success">' . $mensaje . '</span>',
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->redirect(['preliquidaciones']);
        }
    }

    public function actionReciboLiquidacion($id) {
        $this->verificarSesion();
        $request = Yii::$app->request;
        //$model = $this->findModel($id);
        $titulo = "RECIBO DE LIQUIDACION  ";
        $url = "";

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            // jasper init
            $archivo = "recibo_preliquidacion";
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
                'content' => $this->renderAjax('recibo-liquidacion', [
                    'url' => $url,
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('recibo-liquidacion', [
                        'url' => $url,
            ]);
        }
    }

    public function actualizarDatosCobro($model) {
        $modelGraderia = new \app\models\GraderiasSillas();
        
        $auxGraderia = $modelGraderia->findOne($model->grad_id);
        $auxGraderia->grad_vendido = 1; //modifica estado de vendido
        $model->usua_id = Yii::$app->user->id;
        $model->pago_fecha_hora_cobro = date('Y-m-d H:m:s');
        $model->pago_cobrado = 1;
        $resultado = false;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($auxGraderia->save(false)) 
            {
                $resultado=true;
            }
            else
            {
                //echo "MODEL1 NOT SAVED";
                print_r($auxGraderia->getAttributes());
                print_r($auxGraderia->getErrors());
            }
            
            if ($model->save(false)) {
                $transaction->commit();
                $resultado = true;
            } else {
                //echo "MODEL2 NOT SAVED";
                print_r($model->getAttributes());
                print_r($model->getErrors());
                $transaction->rollBack();
            }
        } catch (Exception $e) {
            $transaction->rollBack();
        }
        return $resultado;
    }

    // reportes en jasper
    public function actionComprobantePago($id) {

        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "COMPROBANTE DE PAGO - GRADERIA O SILLAS";
        $url = "";
        $model = $this->findModel($id);
        $montoLiteral = $model->montoTotalLiteral();

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            // jasper init
            $archivo = "comprobante_graderia_silla";
            $parametros = ['id_pago' => $id, 'monto_literal' => '"'.$montoLiteral.'"'];
            $url = $this->generarURLReportePdf('reportes', $archivo, $parametros);
            //end jasper
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('comprobante-pago', [
                    'url' => $url,
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('comprobante-pago', [
                        'url' => $url,
            ]);
        }
    }

    public function actionReportePagoCajero() {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DE PAGO DEL CAJERO - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_pagos_graderias_sillas";
        $carpeta = "reportes/graderias_sillas";
        
        $parametros = ['id_cajero' => Yii::$app->user->id];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('reporte-pago-cajero', [
                    'url' => $url,
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('reporte-pago-cajero', [
                        'url' => $url,
            ]);
        }
    }
    
    public function actionReporteAnuladoCajero() {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DE COMPROBANTES ANULADOS DEL CAJERO - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_anulados_graderias_sillas";
        $carpeta = "reportes/graderias_sillas";
        
        $parametros = ['id_cajero' => Yii::$app->user->id];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('reporte-anulado-cajero', [
                    'url' => $url,
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('reporte-anulado-cajero', [
                        'url' => $url,
            ]);
        }
    }
    
    // funcion para generar reportes del cajero (individual)
   /* public function actionReporteFormPago(){
        $this->verificarSesion();
        
        $request = Yii::$app->request;
        $model = new Pagos();  
        $model->scenario = "reporte_pagos_anulados";
        $titulo = "REPORTE DE PAGOS O ANULADOS";
        
         if($request->isAjax){            
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('reporte-form-pago', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                                Html::button('Guardar',['class'=>'btn btn-primary','type'=>"submit"])        
                ];         
            }else if($model->load($request->post()) && $model->validate()){
                echo "entro";
                exit;
                $tipo = $model->tipo; // pagados o anulados               
                $porciones = explode(" a ", $model->fecha_rango);
                $fecha_inicio = $porciones[0];
                $fecha_limite = $porciones[1];   
                
                   return [
                        'forceReload'=>'#crud-datatable-pjax',
                        'title'=> $titulo,
                        'content' =>"Hola",                        
                        'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"])                                
                    ]; 
                      
            }else{           
                return [
                    'title'=> $titulo,
                    'content'=>$this->renderAjax('reporte-form-pago', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                               Html::button('Guardar 1',['class'=>'btn btn-primary','type'=>"submit"])        
                ];         
            }
        }else{
            
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['reporte-form-pago']);
            }
        }  
        
    }*/


    public function actionReporteGeneral() {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE COMPROBANTES PAGADOS Y ANULADOS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_general_graderias_sillas";
        $carpeta = "reportes/graderias_sillas";
        
        $parametros = [];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('reporte-general', [
                    'url' => $url,
                    'size' => 'modal-xl',
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('reporte-general', [
                'url' => $url,
            ]);
        }
    }

    public function actionReporteDiferencias() {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DIFERENCIA COBROS GRADERIAS SILLAS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_diferencias_graderias_sillas";
        $carpeta = "reportes/graderias_sillas";
        
        $parametros = [];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('reporte-diferencias', [
                    'url' => $url,
                    'size' => 'modal-xl',
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('reporte-diferencias', [
                'url' => $url,
            ]);
        }
    }

    public function actionReporteDiferenciasEventuales() {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DIFERENCIA COBROS EVENTUALES - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_diferencias_eventuales";
        $carpeta = "reportes/eventuales";
        
        $parametros = [];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('reporte-diferencias-eventuales', [
                    'url' => $url,
                    'size' => 'modal-xl',
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('reporte-diferencias-eventuales', [
                'url' => $url,
            ]);
        }    
    }

    public function actionReportePagadosCajeros() {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE GRADERIAS PAGADAS POR CAJERO - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_pagos_graderias_cajeros";
        $carpeta = "reportes/cajeros";
        
        $parametros = [];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('reporte-pagos-graderias-cajeros', [
                    'url' => $url
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('reporte-pagos-graderias-cajeros', [
                        'url' => $url,
            ]);
        }
    }

    public function actionReporteAnuladosCajeros() {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE GRADERIAS ANULADAS POR CAJERO - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_anulados_graderias_cajeros";
        $carpeta = "reportes/cajeros";
        
        $parametros = [];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('reporte-anulados-graderias-cajeros', [
                    'url' => $url,
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('reporte-anulados-graderias-cajeros', [
                        'url' => $url,
            ]);
        }
    }

    public function actionReporteDisponibles() {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE GRADERIAS VENDIDAS Y DISPONIBLES - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte-disponibles";
        $carpeta = "reportes";
        
        $parametros = [];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('graderias_disponibles', [
                    'url' => $url,
                    'size' => 'modal-xl',
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('graderias_disponibles', [
                'url' => $url,
            ]);
        }
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

    /**
     * Finds the Pagos model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Pagos the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Pagos::findOne($id)) !== null) {
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
