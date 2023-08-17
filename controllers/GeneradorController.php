<?php

namespace app\controllers;

use app\models\Contribuyentes;
use app\models\Descargos;
use Yii;
use app\models\DetalleDescargos;
use app\models\GeneradorDescargos;
use app\models\SearchGeneradores;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;
use chrmorandi\jasper\Jasper;
use app\models\Pagos;
use app\models\RazonSociales;
use app\models\Usuario;
use yii\helpers\VarDumper;

class GeneradorController extends Controller
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
     * Lists all Descargos models.
     * @return mixed
     */
    public function actionIndex()
    {
        $this->verificarSesion();
        $searchModel = new SearchGeneradores();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Displays a single Descargos model.
     * @param integer $id
     * @return mixed
     */
    /*public function actionView($id)
    {   
        $this->verificarSesion();
        $request = Yii::$app->request;
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                    'title'=> "Descargos #".$id,
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
    }*/

    public function actionCobrar($id)
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $titulo = "Cobrar sentaje";
        $resultado = false;

        $idUsuarioAutenticado = Yii::$app->user->id;
        $datos = Usuario::findOne($idUsuarioAutenticado);
        $username = $datos->usua_cuenta;

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('cobrar', ['model' => $model]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else if ($model->load($request->post()) && $model->validate()) {
                $sql = 'SELECT SUM(detalle_importe_bs) FROM detalle_descargos WHERE desc_id = :desc_id';
                $montoTotal = Yii::$app->db->createCommand($sql)
                    ->bindValue(':desc_id', $model->desc_id)
                    ->queryOne();
                $token = Yii::$app->ruatServices->login('SWTRAMITESURKUPINIAQUI', 'S1234567');
                if ($token) {
                    $id = $model->desc_id;
                    $sentajero = Descargos::findOne($id);
                    $ci_contribuyente = $sentajero->desc_ci;
                    $tipo_id = $sentajero->desc_ext;
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
                            $idactividad = $sentajero->razon_id;
                            $acti = RazonSociales::findOne($idactividad);
                            $obs = 'DATOS DE ACTIVIDAD: SENTAJES  ' .
                                ', Tipo de sentaje: ' . $acti->razon_nombre;
                            $obsCut = mb_substr($obs, 0, 250);
                            $cleanedString = iconv('UTF-8', 'ASCII//TRANSLIT', $obsCut);
                            $obs = preg_replace('/[^a-zA-Z0-9\s.\-,.:]/u', '', $cleanedString);
                            $response = Yii::$app->ruatServices->createTasa($token, $username, $codigoContribuyente, '24980', $montoTotal['sum'], $obs);
                            if ($response->continuarFlujo) {
                                $nroTasa = $response->numeroTasa;
                                $sql = 'UPDATE detalle_descargos SET detalle_tasa = :tasa WHERE desc_id = :desc_id';
                                $command = Yii::$app->db->createCommand($sql)
                                    ->bindValue(':tasa', $nroTasa)
                                    ->bindValue(':desc_id', $id)
                                    ->queryOne();
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
                        $mensaje = "El contribuyente seleccionado no se encuentra registrado en RUAT.
                                    <br> debe registrar contribuyente primero";
                    }
                } else {
                    $mensaje = 'No se pudo iniciar sesión en RUAT';
                }
                if ($token) {
                    $montoTotal = '';
                    $obs = '';
                    $response = Yii::$app->ruatServices->createTasa($token, 'ERODRIGUEZ2.QUI', '0A025F03001B0807140E565453', '22976', $montoTotal, $obs);
                }

                //$model->detalle_estado_pago = 1;
                $dir = $model->nro_comprobante;
                if ($model->save())
                    $resultado = true;
                else
                    $resultado = false;
                //$resultado =$this->actualizarDatosCobro($model);
                $mensaje = ($resultado ? "Se realizo el cobro correctamente" : " Error al realizar el cobro");
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => $titulo,
                    'content' => '<span class="text-success">' . $mensaje . '<br> Nro. preliquidacion : ' . $model->detalle_id .
                        ' <br> Importe total Bs.: ' . $model->detalle_importe_bs . '  </span>',
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
                    //. Html::a('Comprobante pago', ['comprobante-pago', 'id' => $id], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
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
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->detalle_id]);
            } else {
                return $this->render('cobrar', [
                    'model' => $model,
                ]);
            }
        }
    }

    public function actionCreate()
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $model = new GeneradorDescargos();
        $model->detalle_fecha_entrega = date('Y-m-d H:m');
        $model->detalle_estado = 1;
        $model->detalle_estado_pago = 0;
        $tituloMod = "PRELIQUIDAR DESCARGO";
        $mensaje = 'Registro exitoso';

        if ($request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => $tituloMod,
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => $tituloMod,
                    'content' => '<span class="text-success">' . $mensaje . '</span>',

                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::a('Crear mas', ['create'], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])

                ];
            } else {
                return [
                    'title' => $tituloMod,
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])

                ];
            }
        } else {
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->detalle_id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
    }

    public function actionGenerateTasa($id)
    {
        $this->verificarSesion();
        $request = Yii::$app->request;
        $model = $this->findModel($id);

        $titulo = "Generar tasa";

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('generate-tasa', ['model' => $model,]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else if ($model->load($request->post()) && $model->validate()) {
                //$model->detalle_estado_pago=1;
                if ($model->save())
                    $resultado = true;
                else
                    $resultado = false;
                //$resultado =$this->actualizarDatosCobro($model);
                $mensaje = ($resultado ? "Se realizo el cobro correctamente" : " Error al realizar el cobro");
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => $titulo,
                    'content' => '<span class="text-success">' . $mensaje . '</span>',
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"])
                ];
            } else {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax('generate-tasa', ['model' => $model,]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            }
        } else {
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->detalle_id]);
            } else {
                return $this->render('cobrar', [
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

        $titulo = "RECIBO COBRO SENTAJE";
        $archivo = "preliquidacion_sentaje";
        $carpeta = "reportes";
        $logoImagePath = 'C:\laragon\www\proyecto-urkupina\web'; //realpath($_SERVER['DOCUMENT_ROOT']);

        $monto_literal = $model->montoTotalLiteral();
        $parametros = ['id_detalle' => $id, 'monto_literal' => '"' . $monto_literal . '"'];
        $url = $this->generarURLReportePdf($carpeta, $archivo, $parametros);

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $titulo,
                'content' => $this->renderAjax('recibo_liquidacion', [
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
     * Updates an existing Descargos model.
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

        if ($request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($request->isGet) {
                return [
                    'title' => "Update Descargos #" . $id,
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            } else if ($model->load($request->post()) && $model->save()) {
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => "Descargos #" . $id,
                    'content' => $this->renderAjax('view', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::a('Actualizar', ['update', 'id' => $id], ['class' => 'btn btn-primary', 'role' => 'modal-remote'])
                ];
            } else {
                return [
                    'title' => "Update Descargos #" . $id,
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                    ]),
                    'footer' => Html::button('Cerrar', ['class' => 'btn btn-default pull-left', 'data-dismiss' => "modal"]) .
                        Html::button('Guardar', ['class' => 'btn btn-primary', 'type' => "submit"])
                ];
            }
        } else {

            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->desc_id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }
    }

    public function actionDelete($id)
    {
        $this->verificarSesion();
        $request = Yii::$app->request;

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $sql = ' UPDATE detalle_descargos
                    SET detalle_estado = 0
                    WHERE detalle_id =' . $id;
            $command = Yii::$app->db->createCommand($sql)->queryAll();

            return ['forceCerrar' => true, 'forceReload' => '#crud-datatable-pjax'];
        } else {
            return $this->redirect(['index']);
        }
    }

    public function actionBulkDelete()
    {
        $request = Yii::$app->request;
        $pks = explode(',', $request->post('pks')); // Array or selected records primary keys
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

    protected function findModel($id)
    {
        if (($model = GeneradorDescargos::findOne($id)) !== null) {
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
