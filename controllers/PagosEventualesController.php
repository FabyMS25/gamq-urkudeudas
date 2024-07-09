<?php

namespace app\controllers;

use Yii;

use \yii\web\Response;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

use app\models\Usuario;
use app\models\Categorias;
use app\models\Sindicatos;
use app\models\Contribuyentes;
use app\models\PagosEventuales;
use app\models\SitiosEventuales;
use app\models\SearchPagosEventuales;
use app\models\ActividadesEconomicas;

use yii\helpers\Html;
use yii\helpers\VarDumper;
use yii\filters\VerbFilter;

class PagosEventualesController extends Controller
{
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

    public function actionIndex()
    {
        $this->verificarSesion();
        $searchModel = new SearchPagosEventuales();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['IS ', 'eventual_fecha_hora_pago',  NULL]);
        $dataProvider->query->andFilterWhere(['eventual_estado' => 1]);
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
        $dataProvider->query->andFilterWhere(['eventual_estado' => 1]);

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
        $searchModel = new SearchPagosEventuales();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['IS NOT ', 'eventual_fecha_hora_pago',  NULL]);
        $dataProvider->query->andWhere(['eventual_anulado' => 1]);
        $dataProvider->query->andFilterWhere(['eventual_estado' => 1]);
        if (Usuario::getRolCajero()) {
            $dataProvider->query->andFilterWhere(['usua_id' => \Yii::$app->user->id]);
        }
        return $this->render('anulados', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => "Datos de la actividad economica ",
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

    /**Listado de sitios eventuales  para la preliquidacion en alasitas y urkupina */
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

    public function actionCobrarLiquidacion($id)
    {
        $this->verificarSesion();
        $mensaje = '';
        $resultado = false;
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $model->scenario = "cobrar_liquidacion";
        $titulo = "Cobrar liquidacion nro. <strong>" . $model->eventual_nro_liquidacion . "</strong>";
        //$siteUrl = 'http://proyecto-urkupina.test/index.php?r=pagos%2Fview&id='.$id;
        $siteUrl = 'http://192.168.7.4/proyecto-urkupina/web/index.php?r=pagos-eventuales%2Fview&id=' . $id;

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('cobrar-liquidacion', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else if ($model->load($request->post()) && $model->validate()) {
                $model->eventual_fecha_hora_pago = date('Y-m-d H:m:s');
                $model->usua_id = Yii::$app->user->id;
                $model->eventual_cobrado = 1;
                $dir = $model->eventual_tasa;

                $token = Yii::$app->ruatServices->login('SWTRAMITESURKUPINIAQUI', 'Gam#1209');
                if ($token) {
                    $nroTasa = $model->eventual_tasa;
                    $response = Yii::$app->ruatServices->buscarPagadoPorNroTasa($token, $nroTasa);
                    if ($response == true) {
                        $pagoTasa = Yii::$app->ruatServices->buscarPagadoPorNroTasas($token, $nroTasa);
                        $observacion = 'Folio: ' . $pagoTasa->folio . ', Fecha Pago: ' . $pagoTasa->fechaPago . ', Entidad Financiera: ' . $pagoTasa->entidadFinanciera . ', Monto Pagado: ' . $pagoTasa->montoPago;
                        $model->eventual_descripcion = $model->eventual_descripcion . '-> ' . $observacion;
                        $model->eventual_nro_comprobante = $nroTasa;
                        if ($model->save()) {
                            $llamada = Yii::$app->generadorQR->TEXT($siteUrl);
                            $llamada = Yii::$app->generadorQR->QRCODE(400, $dir);
                            $resultado = true;
                            $mensaje = 'Se realizo el cobro correctamente';
                        } else {
                            $mensaje = 'No se pudo guardar el cobro';
                        }
                    } else {
                        $mensaje = 'No se pudo guardar el pago, porque no se encuentra como pagada en RUAT';
                    }
                } else {
                    $mensaje = 'No se pudo iniciar sesion en RUAT';
                }

                if ($resultado == true) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $titulo,
                        'content' => '<span class="text-success">' . $mensaje . '</span>',
                        'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Comprobante', ['comprobante-pago', 'id' => $model->eventual_id], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                    ];
                } else {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $titulo,
                        'content' => '<span class="text-danger">' . $mensaje . '</span>',
                        'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
                    ];
                }
            } else {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('cobrar-liquidacion', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            }
        } else {
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['index']);
            } else {
                return $this->render('cobrar-liquidacion', [
                    'model' => $model,
                ]);
            }
        }
    }

    public function actionUpdatePagados()
    {
        $sql = 'SELECT * FROM pagos_eventuales WHERE eventual_cobrado=:ev_cobrado AND eventual_estado=:ev_estado';
        $listaPagosEventuales = Yii::$app->db->createCommand($sql)
            ->bindValue(':ev_cobrado', 0)
            ->bindValue(':ev_estado', 1)
            ->queryAll();

        $token = Yii::$app->ruatServices->login('SWTRAMITESURKUPINIAQUI', 'Gam#1209');
        if ($token) {
            for ($i = 0; $i < count($listaPagosEventuales); $i++) {
                $pago = $listaPagosEventuales[$i];
                $id = $pago['eventual_id'];
                $nroTasa = $pago['eventual_tasa'];
                $response = Yii::$app->ruatServices->buscarPagadoPorNroTasa($token, $nroTasa);
                //$response = true;
                if ($response == true) {
                    $pagoTasa = Yii::$app->ruatServices->buscarPagadoPorNroTasas($token, $nroTasa);
                    $usua_id = Yii::$app->user->id;
                    $eventual_fecha_hora_pago = date('Y-m-d H:m:s');
                    /*$observacion = 'Folio de prueba eventual';
                    $sql = 'UPDATE pagos_eventuales SET eventual_cobrado=:cobr, usua_id=:user_id, eventual_fecha_hora_pago=:pago_fecha, eventual_nro_comprobante=:comprob, eventual_descripcion=:obs WHERE eventual_id=:id';
                    $command = Yii::$app->db->createCommand($sql)
                        ->bindValue(':id', $id)
                        ->bindValue(':cobr', 1)
                        ->bindValue(':user_id', $usua_id)
                        ->bindValue(':pago_fecha', $eventual_fecha_hora_pago)
                        ->bindValue(':comprob', $nroTasa)
                        ->bindValue(':obs', $observacion)
                        ->queryOne();*/
                    if ($pagoTasa) {
                        $observacion = 'Folio: ' . $pagoTasa->folio . ', Fecha Pago: ' . $pagoTasa->fechaPago . ', Entidad Financiera: ' . $pagoTasa->entidadFinanciera . ', Monto Pagado: ' . $pagoTasa->montoPago;
                        $sql = 'UPDATE pagos_eventuales SET eventual_cobrado=:cobr, usua_id=:user_id, eventual_fecha_hora_pago=:pago_fecha, eventual_nro_comprobante=:comprob, eventual_descripcion=:obs WHERE eventual_id=:id';
                        $command = Yii::$app->db->createCommand($sql)
                            ->bindValue(':id', $id)
                            ->bindValue(':cobr', 1)
                            ->bindValue(':user_id', $usua_id)
                            ->bindValue(':pago_fecha', $eventual_fecha_hora_pago)
                            ->bindValue(':comprob', $nroTasa)
                            ->bindValue(':obs', $observacion)
                            ->queryOne();
                    }
                }
            }
            $this->actionIndex();
        } else {
        }
    }

    public function actionCreateEventual($id)
    {
        $codigoClasificador = '22977';
        $mensaje = '';
        $resultado = false;

        $this->verificarSesion();
        $request = Yii::$app->request;

        $idUsuarioAutenticado = Yii::$app->user->id;
        $datos = Usuario::findOne($idUsuarioAutenticado);
        $codigoUsuario = $datos->usua_cuenta;

        $model = new PagosEventuales();
        $model->scenario = "crear_eventual_liquidacion";
        $model->patente = 0;
        $model->sentaje = 0;
        $model->aseo = 0;
        $model->sitios_id = $id;
        $model->eventual_preliquidacion = 1;
        $model->eventual_fecha_hora_liquidacion = date('Y-m-d H:m:s');
        $model->eventual_costo_comprobante = $model::COMPROBANTE;
        $model->eventual_user_id_preliquidacion = Yii::$app->user->id;
        $model->eventual_cobrado = 0;
        $titulo = "Preliquidacion de actividades economicas eventuales";

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('create-eventual', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            } else if ($model->load($request->post()) && $model->validate()) {

                $porciones = explode(" a ", $model->rango_fechas);
                $model->eventual_fecha_inicio = $porciones[0];
                $model->eventual_fecha_limite = $porciones[1];
                $montoTotal = $model->eventual_importe_total;

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
                    if ($codigoContribuyente) {
                        $tieneDeudas = Yii::$app->ruatServices->getTieneDeudaContribuyente($token, $codigoContribuyente);
                        if ($tieneDeudas) {
                            $mensaje = 'El contribuyente seleccionado tiene deudas pendientes, no podemos registrar la preliquidación';
                        } else {
                            $idactividad = $model->activi_id;
                            $acti = ActividadesEconomicas::findOne($idactividad);
                            $idsitio = $model->sitios_id;
                            $sitio = SitiosEventuales::findOne($idsitio);
                            $categoria = Categorias::findOne($model->categoria);
                            $sindicato = Sindicatos::findOne($contri->sindi_id);
                            $obs = 'DATOS DE ACTIVIDAD: EVENTUALES  ' .
                                ', Sindicato: ' . $sindicato->sindi_nombre .
                                ', Categoria: ' . $categoria->categ_nombre .
                                ', Codigo Puesto: ' . $sitio->sitios_codigo .
                                ', Nro Puesto: ' . $sitio->sitios_numero_sitio .
                                ', Dir Puesto: ' . $sitio->sitios_descripcion .
                                ', Fecha inicio ' . $porciones[0] .
                                ', Fecha fin ' . $porciones[1] .
                                ', Actividad economica: ' . $acti->activi_descripcion;
                            $obsCut = mb_substr($obs, 0, 250);
                            $cleanedString = iconv('UTF-8', 'ASCII//TRANSLIT', $obsCut);
                            $obs = preg_replace('/[^a-zA-Z0-9\s.\-,.:]/u', '', $cleanedString);

                            $response = Yii::$app->ruatServices->createTasa($token, $codigoUsuario, $codigoContribuyente, $codigoClasificador, $montoTotal, $obs);
                            if ($response->continuarFlujo) {
                                $nroTasa = $response->numeroTasa;
                                $model->eventual_tasa = $nroTasa;
                                if ($model->save()) {
                                    $resultado = true;
                                    $mensaje = 'Se registro los datos de la preliquidación con exito';
                                } else {
                                    $mensaje = 'No se pudo registrar los datos de la preliquidación';
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
                        $codigoContrib = Yii::$app->ruatServices->registerContribuyente($token, $codigoUsuario, $contri);
                        if ($codigoContrib != null) {
                            $idactividad = $model->activi_id;
                            $acti = ActividadesEconomicas::findOne($idactividad);
                            $idsitio = $model->sitios_id;
                            $sitio = SitiosEventuales::findOne($idsitio);
                            $categoria = Categorias::findOne($model->categoria);
                            $sindicato = Sindicatos::findOne($contri->sindi_id);
                            $obs = 'DATOS DE ACTIVIDAD: EVENTUALES  ' .
                                ', Sindicato: ' . $sindicato->sindi_nombre .
                                ', Categoria: ' . $categoria->categ_nombre .
                                ', Codigo Puesto: ' . $sitio->sitios_codigo .
                                ', Nro Puesto: ' . $sitio->sitios_numero_sitio .
                                ', Dir Puesto: ' . $sitio->sitios_descripcion .
                                ', Fecha inicio ' . $porciones[0] .
                                ', Fecha fin ' . $porciones[1] .
                                ', Actividad economica: ' . $acti->activi_descripcion;

                            $obsCut = mb_substr($obs, 0, 250);
                            $cleanedString = iconv('UTF-8', 'ASCII//TRANSLIT', $obsCut);
                            $obs = preg_replace('/[^a-zA-Z0-9\s.\-,.:]/u', '', $cleanedString);

                            $response = Yii::$app->ruatServices->createTasa($token, $codigoUsuario, $codigoContrib, $codigoClasificador, $montoTotal, $obs);
                            if ($response->continuarFlujo) {
                                $nroTasa = $response->numeroTasa;
                                $model->eventual_tasa = $nroTasa;
                                if ($model->save()) {
                                    $resultado = true;
                                    $mensaje = 'CONTRIBUYENTE: Se registro los datos de la preliquidación con exito';
                                } else {
                                    $mensaje = 'CONTRIBUYENTE: No se pudo registrar los datos de la preliquidación';
                                }
                            } else {
                                $mensaje = 'CONTRIBUYENTE: No se pudo registrar la tasa en RUAT <br>';
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
                        } else {
                            $mensaje = 'CONTRIBUYENTE: No se pudo registrar el contribuyente y la tasa.';
                        }
                    }
                } else {
                    $mensaje = 'No se pudo iniciar sesión en RUAT';
                }
                if ($resultado) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $titulo,
                        'content' => '<span class="text-success">' . $mensaje . '</span>',
                        'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Recibo preliquidacion', ['preliquidacion-actividades', 'id' => $model->eventual_id], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                    ];
                } else {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $titulo,
                        'content' => '<span class="text-danger">' . $mensaje . '</span>',
                        'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
                    ];
                }
            } else {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('create-eventual', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            }
        } else {
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->eventual_id]);
            } else {
                return $this->render('create-eventual', [
                    'model' => $model,
                ]);
            }
        }
    }

    /*Liquidacion de act. economicas eventuales ALASITAS*/
    public function actionCreateAlasitas($id)
    {
        $codigoClasificador = '22983';
        $mensaje = '';
        $result = false;

        $this->verificarSesion();
        $request = Yii::$app->request;
        $idUsuarioAutenticado = Yii::$app->user->id;
        $usuarioAutenticado = Usuario::findOne($idUsuarioAutenticado);
        $codigoUsuario = $usuarioAutenticado->usua_cuenta;

        $model = new PagosEventuales();
        $model->scenario = "crear_alasitas_liquidacion";
        $model->sitios_id = $id;
        $model->patente = 0;
        $model->sentaje = 0;
        $model->aseo = 0;
        $model->eventual_cobrado = 0;
        $model->eventual_preliquidacion = 1;
        $model->eventual_fecha_hora_liquidacion = date('Y-m-d H:m:s');
        $model->eventual_costo_comprobante = $model::COMPROBANTE;
        $model->eventual_user_id_preliquidacion = Yii::$app->user->id;
        $model->eventual_cantidad_dia = 0;
        $model->eventual_costo_sentaje = 0;
        $titulo = "Preliquidacion de alasitas";

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('create-alasitas', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            } else if ($model->load($request->post()) && $model->validate()) {
                $porciones = explode(" a ", $model->rango_fechas);
                $model->eventual_fecha_inicio = $porciones[0]; //aqui partimos las fechas
                $model->eventual_fecha_limite = $porciones[1];

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
                    if ($codigoContribuyente) {
                        $tieneDeudas = Yii::$app->ruatServices->getTieneDeudaContribuyente($token, $ci_contribuyente);
                        if ($tieneDeudas) {
                            $mensaje = 'El contribuyente seleccionado tiene deudas pendientes, no podemos registrar la preliquidación';
                        } else {
                            $actividad = ActividadesEconomicas::findOne($model->activi_id);
                            $sitio = SitiosEventuales::findOne($model->sitios_id);
                            $montoTotal = $model->eventual_importe_total;
                            $sindicato = Sindicatos::findOne($contri->sindi_id);

                            $obs = 'DATOS ACTIVIDAD: ' . "ALASITAS " . date('Y') .
                                ', Sindicato: ' . $sindicato->sindi_nombre .
                                ', Clasificador: ' . $codigoClasificador .
                                ', Fechas de Act: ' . $porciones[0] . " a " . $porciones[1] .
                                ', Codigo Puesto: ' . $sitio->sitios_codigo .
                                ', Nro Puesto: ' . $sitio->sitios_numero_sitio .
                                ', Dir Puesto: ' . $sitio->sitios_descripcion .
                                ', Descripcion Act:' . $actividad->activi_descripcion;
                            $obsCut = mb_substr($obs, 0, 250);
                            $cleanedString = iconv('UTF-8', 'ASCII//TRANSLIT', $obsCut);
                            $obs = preg_replace('/[^a-zA-Z0-9\s.\-,.:]/u', '', $cleanedString);

                            $response = Yii::$app->ruatServices->createTasa($token, $codigoUsuario, $codigoContribuyente, $codigoClasificador, $montoTotal, $obs);
                            if ($response->continuarFlujo) {
                                $nroTasa = $response->numeroTasa;
                                $model->eventual_tasa = $nroTasa;
                                if ($model->save()) {
                                    $result = true;
                                    $mensaje = 'Se registro los datos de la preliquidación con exito';
                                } else {
                                    $mensaje = 'No se pudo registrar los datos de la preliquidación';
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
                        $codigoContrib = Yii::$app->ruatServices->registerContribuyente($token, $codigoUsuario, $contri);
                        if ($codigoContrib != null) {
                            $actividad = ActividadesEconomicas::findOne($model->activi_id);
                            $sitio = SitiosEventuales::findOne($model->sitios_id);
                            $montoTotal = $model->eventual_importe_total;
                            $sindicato = Sindicatos::findOne($contri->sindi_id);
                            $obs = 'DATOS ACTIVIDAD: ' . "ALASITAS " . date('Y') .
                                ', Sindicato: ' . $sindicato->sindi_nombre .
                                ', Clasificador: ' . $codigoClasificador .
                                ', Fechas de Act: ' . $porciones[0] . " a " . $porciones[1] .
                                ', Codigo Puesto: ' . $sitio->sitios_codigo .
                                ', Nro Puesto: ' . $sitio->sitios_numero_sitio .
                                ', Dir Puesto: ' . $sitio->sitios_descripcion .
                                ', Descripcion Act:' . $actividad->activi_descripcion;
                            $obsCut = mb_substr($obs, 0, 250);
                            $cleanedString = iconv('UTF-8', 'ASCII//TRANSLIT', $obsCut);
                            $obs = preg_replace('/[^a-zA-Z0-9\s.\-,.:]/u', '', $cleanedString);

                            $response = Yii::$app->ruatServices->createTasa($token, $codigoUsuario, $codigoContrib, $codigoClasificador, $montoTotal, $obs);
                            if ($response->continuarFlujo) {
                                $nroTasa = $response->numeroTasa;
                                $model->eventual_tasa = $nroTasa;
                                if ($model->save()) {
                                    $result = true;
                                    $mensaje = 'CONTRIBUYENTE: Se registro los datos de la preliquidación con exito';
                                } else {
                                    $mensaje = 'CONTRIBUYENTE: No se pudo registrar los datos de la preliquidación';
                                }
                            } else {
                                $mensaje = 'CONTRIBUYENTE: No se pudo registrar la tasa en RUAT <br>';
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
                        } else {
                            $mensaje = 'CONTRIBUYENTE: No se pudo registrar el contribuyente y la tasa.';
                        }
                    }
                } else {
                    $mensaje = 'No se pudo iniciar sesión en RUAT';
                }
                if ($result) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $titulo,
                        'content' => '<span class="text-success">' . $mensaje . '</span>',
                        'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Recibo preliquidacion', ['preliquidacion-actividades', 'id' => $model->eventual_id], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                    ];
                } else {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $titulo,
                        'content' => '<span class="text-danger">' . $mensaje . '</span>',
                        'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
                    ];
                }
            } else {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('create-alasitas', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            }
        } else {
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->eventual_id]);
            } else {
                return $this->render('create-alasitas', [
                    'model' => $model,
                ]);
            }
        }
    }

    /*Liquidacion de act. economicas eventuales*/
    public function actionCreateEspectaculo()
    {
        $this->verificarSesion();
        $mensaje = '';
        $resultado = false;

        /**Get CI auth user */
        $request = Yii::$app->request;
        $idUsuarioAutenticado = Yii::$app->user->id;
        $usuarioAutenticado = Usuario::findOne($idUsuarioAutenticado);
        $codigoUsuario = $usuarioAutenticado->usua_cuenta;

        $request = Yii::$app->request;
        $model = new PagosEventuales();
        $model->scenario = "crear_espectaculos_liquidacion";
        $model->eventual_preliquidacion = 1;
        $model->eventual_fecha_hora_liquidacion = date('Y-m-d H:m:s');
        $model->eventual_user_id_preliquidacion = Yii::$app->user->id;
        $model->eventual_costo_comprobante = $model::COMPROBANTE;
        $model->eventual_cantidad_sitio = 0;
        $model->eventual_costo_sentaje = 0;
        $model->eventual_cobrado = 0;

        $titulo = "Preliquidacion patente por espectaculos, exposicion y funcion";

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('create-espectaculo', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else if ($model->load($request->post()) && $model->validate()) {
                $porciones = explode(" a ", $model->rango_fechas);
                $model->eventual_fecha_inicio = $porciones[0];
                $model->eventual_fecha_limite = $porciones[1];
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
                    if ($codigoContribuyente) {
                        $tieneDeudas = Yii::$app->ruatServices->getTieneDeudaContribuyente($token, $ci_contribuyente);
                        if ($tieneDeudas) {
                            $mensaje = 'El contribuyente seleccionado tiene deudas pendientes, no podemos registrar la preliquidación';
                        } else {
                            $actividadEconomica = ActividadesEconomicas::findOne($model->activi_id);
                            $tipoActividad = $actividadEconomica->activi_descripcion;
                            $cantDias = $model->eventual_cantidad_dia;
                            $FechaInicio = $model->eventual_fecha_inicio;
                            $FechaFin = $model->eventual_fecha_limite;
                            $montoTotal = $model->eventual_importe_total;

                            $obs = 'Datos espectaculo: ' .
                                ', Nro dias: ' . $cantDias .
                                ', Fecha inicio: ' . $FechaInicio .
                                ', Fecha fin: ' . $FechaFin .
                                ', Actividad economica: ' . $tipoActividad;
                            $obsCut = mb_substr($obs, 0, 250);
                            $cleanedString = iconv('UTF-8', 'ASCII//TRANSLIT', $obsCut);
                            $obs = preg_replace('/[^a-zA-Z0-9\s.\-,.:]/u', '', $cleanedString);

                            // $nroTasa = Yii::$app->ruatServices->createTasa($token, $ci_usuarioAutenticado, $codigoContribuyente, '24978', $montoTotal, $obs);
                            $response = Yii::$app->ruatServices->createTasa($token, $codigoUsuario, $codigoContribuyente, '22978', $montoTotal, $obs);
                            if ($response->continuarFlujo) {
                                $nroTasa = $response->numeroTasa;
                                $model->eventual_tasa = $nroTasa;
                                if ($model->save()) {
                                    $resultado = true;
                                    $mensaje = 'Se registro los datos de la preliquidación con exito';
                                } else {
                                    $mensaje = 'No se pudo registrar los datos de la preliquidación';
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
                        $codigoContrib = Yii::$app->ruatServices->registerContribuyente($token, $codigoUsuario, $contri);
                        if ($codigoContrib != null) {
                            $actividadEconomica = ActividadesEconomicas::findOne($model->activi_id);
                            $tipoActividad = $actividadEconomica->activi_descripcion;
                            $cantDias = $model->eventual_cantidad_dia;
                            $FechaInicio = $model->eventual_fecha_inicio;
                            $FechaFin = $model->eventual_fecha_limite;
                            $montoTotal = $model->eventual_importe_total;

                            $obs = 'Datos espectaculo: ' .
                                ', Nro dias: ' . $cantDias .
                                ', Fecha inicio: ' . $FechaInicio .
                                ', Fecha fin: ' . $FechaFin .
                                ', Actividad economica: ' . $tipoActividad;
                            $obsCut = mb_substr($obs, 0, 250);
                            $cleanedString = iconv('UTF-8', 'ASCII//TRANSLIT', $obsCut);
                            $obs = preg_replace('/[^a-zA-Z0-9\s.\-,.:]/u', '', $cleanedString);

                            // $nroTasa = Yii::$app->ruatServices->createTasa($token, $ci_usuarioAutenticado, $codigoContrib, '24978', $montoTotal, $obs);
                            $response = Yii::$app->ruatServices->createTasa($token, $codigoUsuario, $codigoContrib, '22978', $montoTotal, $obs);
                            if ($response->continuarFlujo) {
                                $nroTasa = $response->numeroTasa;
                                $model->eventual_tasa = $nroTasa;
                                if ($model->save()) {
                                    $resultado = true;
                                    $mensaje = 'CONTRIBUYENTE: Se registro los datos de la preliquidación con exito';
                                } else {
                                    $mensaje = 'CONTRIBUYENTE: No se pudo registrar los datos de la preliquidación';
                                }
                            } else {
                                $mensaje = 'CONTRIBUYENTE: No se pudo registrar la tasa en RUAT <br>';
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
                        } else {
                            $mensaje = 'CONTRIBUYENTE: No se pudo registrar el contribuyente y la tasa.';
                        }
                    }
                } else {
                    $mensaje = 'No se pudo iniciar sesión en RUAT';
                }
                if ($resultado) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $titulo,
                        'content' => '<span class="text-success">' . $mensaje . '</span>',
                        'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Recibo preliquidacion', ['preliquidacion-actividades', 'id' => $model->eventual_id], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                    ];
                } else {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $titulo,
                        'content' => '<span class="text-danger">' . $mensaje . '</span>',
                        'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
                    ];
                }
            } else {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('create-espectaculo', [
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
        $mensaje = '';
        $resultado = false;
        $request = Yii::$app->request;
        $idUsuario = Yii::$app->user->id;
        $usuarioAutenticado = Usuario::findOne($idUsuario);
        $codigoUsuario = $usuarioAutenticado->usua_cuenta;

        $model = new PagosEventuales();
        $model->scenario = "crear_publicidad_liquidacion";
        $model->eventual_preliquidacion = 1;
        $model->eventual_fecha_hora_liquidacion = date('Y-m-d H:m:s');
        $model->eventual_costo_comprobante = $model::COMPROBANTE;
        $model->eventual_user_id_preliquidacion = Yii::$app->user->id;
        $model->eventual_cantidad_dia = 1;
        $model->eventual_costo_sentaje = 0;
        $model->eventual_cobrado = 0;
        $titulo = "Preliquidacion de publicidad";

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('create-publicidad', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            } else if ($model->load($request->post()) && $model->validate()) {
                $porciones = explode(" a ", $model->rango_fechas);
                $model->eventual_fecha_inicio = $porciones[0];
                $model->eventual_fecha_limite = $porciones[1];

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
                            $mensaje = 'El contribuyente seleccionado tiene deudas pendientes, no podemos registrar la preliquidación';
                        } else {
                            $actividadEconomica = ActividadesEconomicas::findOne($model->activi_id);
                            $tipoActividad = $actividadEconomica->activi_descripcion;
                            $cantDias = $model->eventual_cantidad_dia;
                            $FechaInicio = $model->eventual_fecha_inicio;
                            $FechaFin = $model->eventual_fecha_limite;
                            $cantSitio = $model->eventual_cantidad_sitio;
                            $montoTotal = $model->eventual_importe_total;

                            $obs = 'Datos publicidad: '  .
                                ', Nro dias: ' . $cantDias .
                                ', Fecha inicio: ' . $FechaInicio .
                                ', Fecha fin: ' . $FechaFin .
                                ', Cantidad puestos: ' . $cantSitio .
                                ', Actividad economica: ' . $tipoActividad;
                            $obsCut = mb_substr($obs, 0, 250);
                            $cleanedString = iconv('UTF-8', 'ASCII//TRANSLIT', $obsCut);
                            $obs = preg_replace('/[^a-zA-Z0-9\s.\-,.:]/u', '', $cleanedString);

                            // $nroTasa = Yii::$app->ruatServices->createTasa($token, $codigoUsuario, $codigoContribuyente, '24979', $montoTotal, $obs);
                            $response = Yii::$app->ruatServices->createTasa($token, $codigoUsuario, $codigoContribuyente, '22979', $montoTotal, $obs);
                            if ($response->continuarFlujo) {
                                $nroTasa = $response->numeroTasa;
                                $model->eventual_tasa = $nroTasa;
                                if ($model->save()) {
                                    $resultado = true;
                                    $mensaje = 'Se registro los datos de la preliquidación con exito';
                                } else {
                                    $mensaje = 'No se pudo registrar los datos de la preliquidación';
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
                        $codigoContrib = Yii::$app->ruatServices->registerContribuyente($token, $codigoUsuario, $contri);
                        if ($codigoContrib != null) {
                             $actividadEconomica = ActividadesEconomicas::findOne($model->activi_id);
                            $tipoActividad = $actividadEconomica->activi_descripcion;
                            $cantDias = $model->eventual_cantidad_dia;
                            $FechaInicio = $model->eventual_fecha_inicio;
                            $FechaFin = $model->eventual_fecha_limite;
                            $cantSitio = $model->eventual_cantidad_sitio;
                            $montoTotal = $model->eventual_importe_total;

                            $obs = 'Datos publicidad: '  .
                                ', Nro dias: ' . $cantDias .
                                ', Fecha inicio: ' . $FechaInicio .
                                ', Fecha fin: ' . $FechaFin .
                                ', Cantidad puestos: ' . $cantSitio .
                                ', Actividad economica: ' . $tipoActividad;
                            $obsCut = mb_substr($obs, 0, 250);
                            $cleanedString = iconv('UTF-8', 'ASCII//TRANSLIT', $obsCut);
                            $obs = preg_replace('/[^a-zA-Z0-9\s.\-,.:]/u', '', $cleanedString);

                            // $nroTasa = Yii::$app->ruatServices->createTasa($token, $codigoUsuario, $codigoContrib, '24979', $montoTotal, $obs);
                            $response = Yii::$app->ruatServices->createTasa($token, $codigoUsuario, $codigoContrib, '22979', $montoTotal, $obs);
                            if ($response->continuarFlujo) {
                                $nroTasa = $response->numeroTasa;
                                $model->eventual_tasa = $nroTasa;
                                if ($model->save()) {
                                    $resultado = true;
                                    $mensaje = 'CONTRIBUYENTE: Se registro los datos de la preliquidación con exito';
                                } else {
                                    $mensaje = 'CONTRIBUYENTE: No se pudo registrar los datos de la preliquidación';
                                }
                            } else {
                                $mensaje = 'CONTRIBUYENTE: No se pudo registrar la tasa en RUAT <br>';
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
                        } else {
                            $mensaje = 'CONTRIBUYENTE: No se pudo registrar el contribuyente y la tasa.';
                        }
                    }
                } else {
                    $mensaje = 'No se pudo iniciar sesión en RUAT';
                }
                if ($resultado) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $titulo,
                        'content' => '<span class="text-success">' . $mensaje . '</span>',
                        'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                            Html::a('Recibo preliquidacion', ['preliquidacion-actividades', 'id' => $model->eventual_id], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
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
                    'content' => $this->renderAjax('create-publicidad', [
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
                return $this->redirect(['view', 'id' => $model->eventual_id]);
            } else {
                return $this->render('create-publicidad', [
                    'model' => $model,
                ]);
            }
        }
    }

    public function actionAnularLiquidacion($id)
    {
        $result = false;
        $mensaje = "";
        $this->verificarSesion();
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $titulo = "Anular liquidacion nro. <strong>" . $model->eventual_nro_liquidacion . "</strong>";

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('anular', ['model' => $model,]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else if ($model->load($request->post()) && $model->validate()) {
                //$model->eventual_fecha_hora_pago = date('Y-m-d H:m:s');
                $model->usua_id = Yii::$app->user->id;
                $idUsuario = $model->usua_id;
                $datos = Usuario::findOne($idUsuario);
                //$model->eventual_cobrado = 1;
                $dir = $model->eventual_nro_comprobante;
                $ci_usuarioAutenticado = $datos->usua_cuenta;

                $token = Yii::$app->ruatServices->login('SWTRAMITESURKUPINIAQUI', 'Gam#1209');
                if ($token) {
                    $nrotasa = $model->eventual_tasa;
                    $motivo = $model->eventual_anulado_detalle;
                    $obs = $model->eventual_descripcion;
                    $response = Yii::$app->ruatServices->anularTasa($token, $ci_usuarioAutenticado, $nrotasa, $motivo, $obs);
                    if ($response->continuarFlujo) {
                        $model->eventual_estado = 0;
                        $mensajeConfirmacion = $response->mensajeConfirmacion;
                        if ($model->save()) {
                            $mensaje = "Se elimino la preliquidacion y  \n " . $mensajeConfirmacion;
                            $result = true;
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
                    'content' => $mensaje,
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
            /*       *   Process for non-ajax request            */
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['index']);
            } else {
                return $this->render('anular-liquidacion', ['model' => $model,]);
            }
        }
    }

    public function actionDelete($id)
    {
        $request = Yii::$app->request;
        $this->findModel($id)->delete();

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceCerrar' => true, 'forceReload' => '#crud-datatable-pjax'];
        } else {
            return $this->redirect(['index']);
        }
    }

    public function actionBulkDelete()
    {
        $request = Yii::$app->request;
        $pks = explode(',', $request->post('pks'));
        foreach ($pks as $pk) {
            $model = $this->findModel($pk);
            $model->delete();
        }

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceCerrar' => true, 'forceReload' => '#crud-datatable-pjax'];
        } else {
            return $this->redirect(['index']);
        }
    }

    /**Reportes Jasper */
    public function actionComprobantePago($id)
    {
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
            $parametros = ['id_pago' => $id, 'monto_literal' => '"' . $montoLiteral . '"'];
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

    public function actionPreliquidacionSitios($id)
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $montoLiteral = $model->montoTotalLiteral();
        $titulo = "RECIBO DE LIQUIDACION  DE SITIOS ";
        $url = "";
        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $archivo = "preliquidacion_actividades_economicas";
            $carpeta =  "reportes";
            $parametros = ['id_pago' => $id, 'monto_literal' => '"' . $montoLiteral . '"'];
            $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);
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

    public function actionPreliquidacionActividades($id)
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $montoLiteral = $model->montoTotalLiteral();
        $titulo = "RECIBO DE PRELIQUIDACION DE ACTIV. ECONOMICAS ";
        $url = "";

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $archivo = "preliquidacion_actividades_economicas";
            $carpeta = "reportes";
            $parametros = ['id_pago' => $id, 'monto_literal' => '"' . $montoLiteral . '"'];
            $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);
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

    protected function findModel($id)
    {
        if (($model = PagosEventuales::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**Verificar sesion activa */
    public function verificarSesion()
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->user->logout(true);
            return $this->goHome();
        }
    }

    public function actionReporteEventualesPagados()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DE SITIOS EVENTUALES PAGADOS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_eventuales_pagos";
        $carpeta = "reportes/eventuales";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);
        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('reporte-eventuales-pagados', ['url' => $url]);
    }

    public function actionReporteEventualesNopagados()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DE SITIOS EVENTUALES NO PAGADOS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_eventuales_sin_pagos";
        $carpeta = "reportes/eventuales";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('reporte-eventuales-nopagados', ['url' => $url]);
    }

    public function actionReporteAlasitasPagados()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DE ALASITAS PAGADOS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_alasitas_pagos";
        $carpeta = "reportes/eventuales";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('reporte-alasitas-pagados', ['url' => $url]);
    }

    public function actionReporteAlasitasNopagados()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $titulo = "REPORTE DE ALASITAS NO PAGADOS - FECHA " . date("d/m/Y H:m");
        $archivo = "reporte_alasitas_sin_pagos";
        $carpeta = "reportes/eventuales";
        $logoImagePath = realpath($_SERVER['DOCUMENT_ROOT']);

        $parametros = ['logo_path' => '"' . $logoImagePath . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        return $this->render('reporte-alasitas-nopagados', ['url' => $url]);
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
}
