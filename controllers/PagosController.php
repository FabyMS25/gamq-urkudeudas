<?php

namespace app\controllers;

use Yii;
use Exception;

use yii\web\Response;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

use app\models\Zonas;
use app\models\Pagos;
use app\models\Usuario;
use app\models\SearchPagos;
use app\models\TipoArmados;
use app\models\Contribuyentes;
use app\models\GraderiasSillas;

use yii\helpers\Html;
use yii\helpers\VarDumper;
use yii\filters\VerbFilter;

/**
 * PagosController implements the CRUD actions for Pagos model.
 */
class PagosController extends Controller
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
     * Lists all Pagos models.
     * @return mixed
     */
    public function actionIndex()
    {
        $this->verificarSesion();
        $listaGraderia = (new Pagos())->listaIdGraderiasSillasPreliquidados();
        $searchModel = new \app\models\SearchGraderiasSillas();
        $dataProvider = $searchModel->searchPreliquidaciones(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['grad_estado' => 1]);
        $dataProvider->query->andFilterWhere(['grad_vendido' => 0]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionGeneral()
    {
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

    public function actionPreliquidaciones()
    {
        $this->verificarSesion();

        //
        $searchModel = new SearchPagos();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['pago_estado' => 1, 'pago_preliquidacion' => 1, 'pago_cobrado' => 0]);
        if (Usuario::getRolPreli()) {
            $dataProvider->query->andFilterWhere(['pago_id_user_preliquidacion' => \Yii::$app->user->id]);
        }

        return $this->render('preliquidaciones', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionPagados()
    {
        $this->verificarSesion();
        $searchModel = new SearchPagos();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['pago_estado' => 1, 'pago_cobrado' => 1, 'pago_anulado' => 0]);
        if (Usuario::getRolCajero()) {
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
        $searchModel = new SearchPagos();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['pago_estado' => 1, 'pago_preliquidacion' => 1, 'pago_anulado' => 1,]);
        if (Usuario::getRolCajero()) {
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
    public function actionCobrar($id)
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $model->scenario = "cobrar_graderias_sillas";
        $titulo = "Cobrar preliquidacion de " . $model->graderiaSilla->grad_codigo;

        //$siteUrl = 'http://proyecto-urkupina.test/index.php?r=pagos%2Fview&id='.$id;
        $siteUrl = 'http://192.168.7.4/proyecto-urkupina/web/index.php?r=pagos%2Fview&id=' . $id;

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
                $model->pago_cobrado = 1;
                $token = Yii::$app->ruatServices->login('SWTRAMITESURKUPINIAQUI', 'Gam#1209');
                if ($token) {
                    $nroTasa = $model->pago_tasa;
                    $response = Yii::$app->ruatServices->buscarPagadoPorNroTasa($token, $nroTasa);
                    if ($response == true) {
                        $llamada = Yii::$app->generadorQR->TEXT($siteUrl);
                        $llamada = Yii::$app->generadorQR->QRCODE(400, $nroTasa);

                        $pagoTasa = Yii::$app->ruatServices->buscarPagadoPorNroTasas($token, $nroTasa);
                        $observacion = 'Folio: ' . $pagoTasa->folio . ', Fecha Pago: ' . $pagoTasa->fechaPago . ', Entidad Financiera: ' . $pagoTasa->entidadFinanciera . ', Monto Pagado: ' . $pagoTasa->montoPago;
                        $model->pago_observaciones = $model->pago_observaciones . '->' . $observacion;
                        $model->pago_nro_comprobante = $nroTasa;
                        if ($model->save())
                            $resultado = true;
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
                    } else
                        return [
                            'forceReload' => '#crud-datatable-pjax',
                            'title' => $titulo,
                            'content' => '<span class="text-danger">' . 'No se realizo ningun Pago!!',
                            'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])

                        ];
                } else {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $titulo,
                        'content' => '<span class="text-success">' . 'No se puede Autenticar en RUAT',
                        'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])

                    ];
                }
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

    public function actionView($id)
    {
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
    public function actionPreliquidar($id)
    {
        $mensaje = '';
        $resultado = false;

        $this->verificarSesion();
        $request = Yii::$app->request;
        $idUsuarioAutenticado = Yii::$app->user->id;
        $usuarioAutenticado = Usuario::findOne($idUsuarioAutenticado);
        $codigoUsuario = $usuarioAutenticado->usua_cuenta;

        /**Pagos */
        $model = new Pagos();
        $model->grad_id = $id;
        $model->pago_reposicion = $model::COMPROBANTE;
        $model->pago_id_user_preliquidacion = Yii::$app->user->id; // id user sesion
        $model->pago_estado = 1;
        $model->pago_fecha_hora_preliquidacion = date('Y-m-d H:m:s');
        $model->pago_preliquidacion = 1;
        $modelSitio = GraderiasSillas::findOne($id);
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
                $codigo = $model->grad_id;
                $resto = $modelSitio->grad_longitud - $model->pago_longitud_modificada;
                $model->pago_cobrado = 0;
                $val = 0;
                if ($resto == 0) {
                    $val = 1;
                }

                $token = Yii::$app->ruatServices->login('SWTRAMITESURKUPINIAQUI', 'Gam#1209');
                if ($token) {
                    $id = $model->contri_id;
                    $contri = Contribuyentes::findOne($id);
                    $ci_contribuyente = $contri->contri_ci;
                    $tipo_id = $contri->ext_id;
                    $tipo_doc = "CI";
                    if ($tipo_id == 12) {
                        $tipo_doc = "CE";
                    }
                    $codigoContribuyente = Yii::$app->ruatServices->getContribuyentePorCi($token, $ci_contribuyente, $tipo_doc);
                    if ($codigoContribuyente != null) {
                        $tieneDeudas = Yii::$app->ruatServices->getTieneDeudaContribuyente($token, $ci_contribuyente);
                        if ($tieneDeudas) {
                            $mensaje = 'El contribuyente seleccionado tiene deudas pendientes, no podemos registrar la tasa';
                        } else {
                            $montoTotal = $model->pago_importe_total;
                            $zona = Zonas::findOne($modelSitio->zona_id);
                            $tipoArmado = TipoArmados::findOne($model->tip_arm_id);
                            $obs = 'Datos graderia silla: ' .
                                ' Zona: ' . $zona->zona_nombre .
                                ', Codigo: ' . $modelSitio->grad_codigo .
                                ', Direccion: ' . $modelSitio->grad_direccion  .
                                ', Tipo sitio: ' . $modelSitio->grad_tipo_sitio .
                                ', Tipo armado: ' . $tipoArmado->tip_arm_descricpion .
                                ', Armado Especifico: ' . $modelSitio->grad_tipo_armado;
                            $obsCut = mb_substr($obs, 0, 250);
                            $cleanedString = iconv('UTF-8', 'ASCII//TRANSLIT', $obsCut);
                            $obs = preg_replace('/[^a-zA-Z0-9\s.\-,.:]/u', '', $cleanedString);

                            $response = Yii::$app->ruatServices->createTasa($token, $codigoUsuario, $codigoContribuyente, '22976', $montoTotal, $obs);
                            if ($response->continuarFlujo) {
                                $nroTasa = $response->numeroTasa;
                                $model->pago_tasa = $nroTasa;

                                if ($model->save()) {
                                    $sql = 'UPDATE graderias_sillas SET grad_vendido=:val, grad_longitud=:rest WHERE grad_id=:id';
                                    $command = Yii::$app->db->createCommand($sql)
                                        ->bindValue(':id', $codigo)
                                        ->bindValue(':val', $val)
                                        ->bindValue(':rest', $resto)
                                        ->queryOne();
                                    $resultado = true;
                                    $mensaje = 'Se registro los datos de la preliquidación con exito';
                                } else {
                                    $resultado = false;
                                    $mensaje = 'No se pudo registrar registrar los datos de la preliquidación';
                                }
                            } else {
                                $mensaje = 'No se pudo registrar la tasa en RUAT <br>';
                                if (is_array($response->mensaje)) {
                                    $messages = $response->mensaje;
                                    foreach ($messages as $message) {
                                        foreach ($message as $key => $errorMessages) {
                                            $mensaje = $mensaje . "Error en: $key, ";
                                            foreach ($errorMessages as $errorMessage) {
                                                $mensaje = $mensaje . $errorMessage;
                                            }
                                        }
                                    }
                                } else {
                                    $mensaje = $mensaje . $response->mensaje;
                                }
                            }
                        }
                    } else {
                        $mensaje = 'No se pudo registrar la preliquidación, porque el contribuyente seleccionado no se encuentra registrado en RUAT, por favor registrar contribuyente.';
                    }
                } else {
                    $mensaje = 'No se pudo iniciar sesión en RUAT';
                }
                if ($resultado) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $titulo,
                        'content' => '<span class="text-success text-bold">' . $mensaje . '</span>',
                        'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            !$resultado ? Html::a('recibo preliquidacion', ['recibo-liquidacion', 'id' => $model->pago_id], ['class' => 'btn btn-primary', 'role' => 'modal-remote']) : ''
                    ];
                } else {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $titulo,
                        'content' => '<span class="text-danger text-bold">' . $mensaje . '</span>',
                        'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
                    ];
                }
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
            /*Process for non-ajax request*/
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->pago_id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
    }

    public function actionUpdatePagados()
    {
        $sql = 'SELECT * FROM pagos WHERE pago_cobrado=:pago_cobrado AND pago_estado=:pago_estado';
        $listaPagos = Yii::$app->db->createCommand($sql)
            ->bindValue(':pago_cobrado', 0)
            ->bindValue(':pago_estado', 1)
            ->queryAll();

        $token = Yii::$app->ruatServices->login('SWTRAMITESURKUPINIAQUI', 'Gam#1209');
        if ($token) {
            for ($i = 0; $i < count($listaPagos); $i++) {
                $pago = $listaPagos[$i];
                $id = $pago['pago_id'];
                $nroTasa = $pago['pago_tasa'];
                $response = Yii::$app->ruatServices->buscarPagadoPorNroTasa($token, $nroTasa);
                //$response = true;
                if ($response == true) {
                    $pagoTasa = Yii::$app->ruatServices->buscarPagadoPorNroTasas($token, $nroTasa);
                    $usua_id = Yii::$app->user->id;
                    $pago_fecha_hora_cobro = date('Y-m-d H:m:s');
                    /*$observacion = 'Folio de prueba pagos';
                    $sql = 'UPDATE pagos SET pago_cobrado=:cobr, usua_id=:id_user, pago_fecha_hora_cobro=:pago_fecha, pago_nro_comprobante=:comprob, pago_observaciones=:obs WHERE pago_id=:id';
                    $command = Yii::$app->db->createCommand($sql)
                        ->bindValue(':id', $id)
                        ->bindValue(':cobr', 1)
                        ->bindValue(':id_user', $usua_id)
                        ->bindValue(':pago_fecha', $pago_fecha_hora_cobro)
                        ->bindValue(':comprob', $nroTasa)
                        ->bindValue(':obs', $observacion)
                        ->queryOne();*/
                    if ($pagoTasa != null) {
                        $usua_id = Yii::$app->user->id;
                        $pago_fecha_hora_cobro = date('Y-m-d H:m:s');
                        $observacion = 'Folio: ' . $pagoTasa->folio . ', Fecha Pago: ' . $pagoTasa->fechaPago . ', Entidad Financiera: ' . $pagoTasa->entidadFinanciera . ', Monto Pagado: ' . $pagoTasa->montoPago;
                        $sql = 'UPDATE pagos SET pago_cobrado=:cobr, usua_id=:id_user, pago_fecha_hora_cobro=:pago_fecha, pago_nro_comprobante=:comprob, pago_observaciones=:obs WHERE pago_id=:id';
                        $command = Yii::$app->db->createCommand($sql)
                            ->bindValue(':id', $id)
                            ->bindValue(':cobr', 1)
                            ->bindValue(':id_user', $usua_id)
                            ->bindValue(':pago_fecha', $pago_fecha_hora_cobro)
                            ->bindValue(':comprob', $nroTasa)
                            ->bindValue(':obs', $observacion)
                            ->queryOne();
                    }
                }
            }
            $this->actionPreliquidaciones();
        } else {
        }
    }



    public function actionCreate()
    {
        $titulo = '';
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
    public function actionAnularPago($id)
    {
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
    protected function habilitarDatosPreliquidacion($model)
    {

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
        //$transaction = Yii::$app->db->beginTransaction();
        try {
            if ($modelAux->save(false) && $model->save(false)) {
                //$transaction->commit();
                $resultado = true;
            } else {
                // $transaction->rollBack();
            }
        } catch (Exception $e) {
            //$transaction->rollBack();
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
    public function actionAnularPreliquidacion($id)
    {
        $result = false;
        $mensaje = "";
        $this->verificarSesion();
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $pago_longitud_modificada = $model->pago_longitud_modificada;
        $nro_preliquidacion = $model->pago_nro_liquidacion;
        $model->pago_estado = 0;
        // modelo graderias y sillas
        $modelGraderia = \app\models\GraderiasSillas::findOne($model->grad_id);
        $modelGraderia->grad_vendido = 0;
        $modelGraderia->grad_longitud = $modelGraderia->grad_longitud + $pago_longitud_modificada;
        $resultado = false;
        $titulo = "Anular preliquidacion de <strong>" . $model->graderiaSilla->grad_codigo . "</strong>";

        if ($request->isAjax) {
            /*           Process for ajax request            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('anular', ['model' => $model,]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else if ($model->load($request->post()) && $model->validate()) {
                $idUsuario = $model->pago_id_user_preliquidacion;
                $datos = Usuario::findOne($idUsuario);
                $username = $datos->usua_cuenta;
                //$model->pago_fecha_hora_cobro = date('Y-m-d H:m:s');

                $token = Yii::$app->ruatServices->login('SWTRAMITESURKUPINIAQUI', 'Gam#1209');
                if ($token) {
                    $motivo = $model->pago_anulado_detalle;
                    $observacion = $model->pago_observaciones;
                    $nrotasa = $model->pago_tasa;
                    $response = Yii::$app->ruatServices->anularTasa($token, $username, $nrotasa, $motivo, $observacion);
                    if ($response->continuarFlujo) {
                        $model->pago_estado = 0;
                        $mensajeConfirmacion = $response->mensajeConfirmacion;
                        if ($model->save()) {
                            if ($modelGraderia->save()) {
                                $mensaje = "Se elimino la preliquidacion y  \n " . $mensajeConfirmacion;
                                $result = true;
                            } else {
                                $mensaje = 'No se pudo actualizar graderias';
                            }
                        } else {
                            $mensaje = $mensajeConfirmacion . " pero no se pudo eliminar la Preliquidacion.";
                            $result = false;
                        }
                    } else {
                        if (is_array($response->mensaje)) {
                            $messages = $response->mensaje;
                            foreach ($messages as $message) {
                                foreach ($message as $key => $errorMessages) {
                                    $mensaje = "Error en: $key\n";
                                    foreach ($errorMessages as $errorMessage) {
                                        $mensaje = " Error en $key, \n $errorMessage\n";
                                    }
                                }
                            }
                        } else {
                            $mensaje = $response->mensaje;
                        }
                    }
                } else {
                    $mensaje =  'No se pudo autentificar en RUAT.';
                }
                $mensaje = $result ? "<span class='text-success'>  $mensaje </span>" : "<span class='text-danger'>$mensaje </span>";
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => $titulo,
                    'content' => $mensaje . '<br> Nro. preliquidacion : ' . $model->pago_nro_liquidacion .
                        ' <br> Importe total Bs.: ' . $model->pago_importe_total . '</span>',
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
                ];
            } else {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('anular', ['model' => $model,]),
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
                return $this->render('anular', [
                    'model' => $model,
                ]);
            }
        }
    }

    public function actionReciboLiquidacion($id)
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $montoLiteral = $model->montoTotalLiteral();
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
                Yii::getAlias('@ruta') . '/' . $archivo . '.jasper',
                ['id_pago' => $id, 'monto_literal' => '"' . $montoLiteral . '"'],
                ['pdf'],
                false
            )
                ->execute();
            $url = \Yii::getAlias('@ruta') . '/' . $archivo . '.pdf';

            //end jasper
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('recibo-liquidacion', [
                    'url' => $url,
                    'size' => 'modal-lg',
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('recibo-liquidacion', [
                'url' => $url,
            ]);
        }
    }

    public function actualizarDatosCobro($model)
    {
        $modelGraderia = new \app\models\GraderiasSillas();

        $auxGraderia = $modelGraderia->findOne($model->grad_id);
        $auxGraderia->grad_vendido = 1; //modifica estado de vendido
        $model->usua_id = Yii::$app->user->id;
        $model->pago_fecha_hora_cobro = date('Y-m-d H:m:s');
        $model->pago_cobrado = 1;
        $resultado = false;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($auxGraderia->save(false)) {
                $resultado = true;
            } else {
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
    public function actionComprobantePago($id)
    {

        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "COMPROBANTE DE PAGO - GRADERIA O SILLAS";
        $url = "";
        $model = $this->findModel($id);
        $montoLiteral = $model->montoTotalLiteral();
        //$qrImagePath = realpath($_SERVER['DOCUMENT_ROOT']);
        $qrImagePath = "C:\laragon\www\proyecto-urkupina\web";

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            // jasper init
            $archivo = "comprobante_graderia_silla";
            $parametros = ['id_pago' => $id, 'monto_literal' => '"' . $montoLiteral . '"', 'image_path' => '"' . $qrImagePath . '"'];
            $url = $this->generarURLReportePdf('reportes', $archivo, $parametros);
            //end jasper
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('comprobante-pago', [
                    'url' => $url,
                    'size' => 'modal-lg',
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('comprobante-pago', [
                'url' => $url,
            ]);
        }
    }

    public function actionReportePagoCajero()
    {
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

    public function actionReporteAnuladoCajero()
    {
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
    /* public function actionReporteFormPago() {
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
        
    }
    */

    public function actionReporteGeneral()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE COMPROBANTES PAGADOS Y ANULADOS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_general_graderias_sillas";
        $carpeta = "reportes/graderias_sillas";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        /*if ($request->isAjax) {
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
                'size' => 'modal-xl',
            ]);
        }*/
        return $this->render('reporte-general', ['url' => $url]);
    }

    public function actionReporteGeneralZonas()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE COMPROBANTES PAGADOS POR ZONAS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_general_graderias_zonas";
        $carpeta = "reportes/graderias_sillas";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);
        return $this->render('reporte-general-zonas', ['url' => $url]);
    }

    public function actionResumenGeneral()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "RESUMEN GENERAL DE RECAUDACIONES - FECHA " . date("d/m/Y H:m");
        $archivo = "resumen_general_importes";
        $carpeta = "reportes/resumen";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('resumen-general', ['url' => $url]);
    }

    public function actionReportePreliquidacionNopagados()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE PRELIQUIDACIONES NO PAGADOS (GRADERIAS/SILLAS) - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_graderias_no_pagados";
        $carpeta = "reportes/graderias_sillas";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('reporte-preliquidacion-nopagados', ['url' => $url]);
    }

    /*
    public function actionReporteGeneralSentajes() {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE GENERAL SENTAJES - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_descargo_sentajes;
        $carpeta = "reportes/sentajes";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);
        
        $parametros = ['logo_path' => '"'.$logoImagePath.'"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);
        return $this->render('reporte-sentajes', ['url' => $url]);
    }
*/
    public function actionReporteEspaciosDisponibles()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE ESPACIOS DISPONIBLES - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_espacios_vendidos_disponibles";
        $carpeta = "reportes/graderias_sillas";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        /*if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('reporte-espacios-disponibles', [
                    'url' => $url,
                    'size' => 'modal-xl',
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('reporte-espacios-disponibles', [
                'url' => $url,
                'size' => 'modal-xl',
            ]);
        }*/
        return $this->render('reporte-espacios-disponibles', ['url' => $url]);
    }

    public function actionReporteDiferencias()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DIFERENCIA COBROS GRADERIAS SILLAS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_diferencias_graderias_sillas";
        $carpeta = "reportes/graderias_sillas";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        /*if ($request->isAjax) {
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
        }*/
        return $this->render('reporte-diferencias', ['url' => $url]);
    }

    public function actionReporteDiferenciasEventuales()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DIFERENCIA COBROS EVENTUALES - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_diferencias_eventuales";
        $carpeta = "reportes/eventuales";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        /*if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('reporte-diferencias-eventuales', [
                    'url' => $url,
                    'size' => 'modal-lg',
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('reporte-diferencias-eventuales', [
                'url' => $url,
                'size' => 'modal-lg',
            ]);
        }*/
        return $this->render('reporte-diferencias-eventuales', ['url' => $url]);
    }

    public function actionReportePagosGraderiasCajeros()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE GRADERIAS PAGADAS POR CAJERO - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_pagos_graderias_cajeros";
        $carpeta = "reportes/cajeros";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        /*if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('reporte-pagos-graderias-cajeros', [
                    'url' => $url,
                    'size' => 'modal-lg',
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('reporte-pagos-graderias-cajeros', [
                'url' => $url,
                'size' => 'modal-lg',
            ]);
        }*/
        return $this->render('reporte-pagos-graderias-cajeros', ['url' => $url]);
    }

    public function actionReporteAnuladosGraderiasCajeros()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE GRADERIAS ANULADAS POR CAJERO - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_anulados_graderias_cajeros";
        $carpeta = "reportes/cajeros";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        /*if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $titulo,
                'content' => $this->renderAjax('reporte-anulados-graderias-cajeros', [
                    'url' => $url,
                    'size' => 'modal-lg',
                ]),
                'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
            ];
        } else {
            return $this->render('reporte-anulados-graderias-cajeros', [
                'url' => $url,
                'size' => 'modal-lg',
            ]);
        }*/
        return $this->render('reporte-anulados-graderias-cajeros', ['url' => $url]);
    }

    public function actionReportePreliquidacionSentajes()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE PRELIQUIDACION DE SENTAJES - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_preliquidacion_sentajes";
        $carpeta = "reportes/sentajes";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('reporte-preliquidacion-sentajes', ['url' => $url]);
    }

    public function actionReporteSentajesPagados()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DE SENTAJES PAGADOS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_sentajes_pagados";
        $carpeta = "reportes/sentajes";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('reporte-sentajes-pagados', ['url' => $url]);
    }

    public function actionReporteEfectividadGraderias()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DE SUFICIENCIA DE GRADERIAS O SILLAS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_efectividad_graderias";
        $carpeta = "reportes/suficiencia";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('reporte-efectividad-graderias', ['url' => $url]);
    }

    public function actionReporteEfectividadEventuales()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DE SUFICIENCIA DE GRADERIAS O SILLAS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_efectividad_eventuales";
        $carpeta = "reportes/suficiencia";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('reporte-efectividad-eventuales', ['url' => $url]);
    }

    public function actionReporteEfectividadAlasitas()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DE SUFICIENCIA DE GRADERIAS O SILLAS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_efectividad_alasitas";
        $carpeta = "reportes/suficiencia";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('reporte-efectividad-alasitas', ['url' => $url]);
    }

    public function actionReporteEfectividadSentajes()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DE SUFICIENCIA DE GRADERIAS O SILLAS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_efectividad_sentajes";
        $carpeta = "reportes/suficiencia";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('reporte-efectividad-sentajes', ['url' => $url]);
    }

    public function actionReporteMingitoriosPagados()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DE MINGITIOS PAGADOS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_mingitorios_pagados";
        $carpeta = "reportes/sentajes";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('reporte-mingitorios-pagados', ['url' => $url]);
    }

    protected function generarURLReportePdf($carpeta, $file, $parametros = [])
    {
        $archivo = $file;
        Yii::setAlias('@ruta', $carpeta);

        $jasper = Yii::$app->jasper;
        $jasper->compile(Yii::getAlias('@ruta') . '/' . $archivo . '.jrxml')->execute();
        $jasper->process(
            Yii::getAlias('@ruta') . '/' . $archivo . '.jasper',
            $parametros,
            ['pdf'],
            false
        )->execute();
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
    protected function findModel($id)
    {
        if (($model = Pagos::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    // funcion que verifica la existencia de una sesion activa
    public function verificarSesion()
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->user->logout(true);
            return $this->goHome();
        }
    }
}
