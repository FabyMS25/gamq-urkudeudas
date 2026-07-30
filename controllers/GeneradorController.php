<?php

namespace app\controllers;

use Yii;
use app\models\Descargos;
use app\models\GeneradorDescargos;
use app\models\RazonSociales;
use app\models\SearchGeneradores;
use app\models\Usuario;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
     * Lists all GeneradorDescargos models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $this->verificarSesion();

        $searchModel = new SearchGeneradores();
        $dataProvider = $searchModel->search(
            Yii::$app->request->queryParams
        );

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Genera una sola tasa RUAT para todos los detalles pendientes
     * pertenecientes al mismo descargo/sentajero.
     *
     * El flujo original de SISURKU se conserva:
     *
     * - Se obtiene el desc_id desde el detalle seleccionado.
     * - Se suman todos los detalles activos, no pagados y sin tasa.
     * - Se registra una sola tasa agrupada en RUAT.
     * - El número de tasa se asigna a todos los detalles incluidos.
     * - También se guarda el código clasificador usado en RUAT.
     *
     * @param integer $id detalle_id
     * @return array|string|\yii\web\Response
     */
    public function actionGenerarTasa($id)
    {
        $this->verificarSesion();

        $request = Yii::$app->request;
        $model = $this->findModel($id);

        $titulo = 'Generar Tasa';
        $resultado = false;
        $mensaje = '';

        /*
         * SISURKU conserva su clasificador original de Sentajes Urkupiña.
         * Este mismo código se guardará en codigo_clasificador.
         */
        $codigoClasificador = isset(
            Yii::$app->params['clasificadores']['sentajes_urkupina']
        )
            ? (string)Yii::$app->params['clasificadores']['sentajes_urkupina']
            : null;

        if ($codigoClasificador === null || $codigoClasificador === '') {
            throw new \RuntimeException(
                'No está configurado el clasificador sentajes_urkupina.'
            );
        }

        $idUsuarioAutenticado = Yii::$app->user->id;
        $datosUsuario = Usuario::findOne($idUsuarioAutenticado);

        if (
            $datosUsuario === null
            || trim((string)$datosUsuario->usua_cuenta) === ''
        ) {
            throw new \RuntimeException(
                'El usuario autenticado no tiene una cuenta RUAT configurada.'
            );
        }

        $username = trim((string)$datosUsuario->usua_cuenta);

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($request->isGet) {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax(
                        'cobrar',
                        ['model' => $model]
                    ),
                    'footer' =>
                        Html::button(
                            'Cerrar',
                            [
                                'class' => 'btn btn-default pull-left',
                                'data-dismiss' => 'modal',
                            ]
                        ) .
                        Html::button(
                            'Guardar',
                            [
                                'class' => 'btn btn-primary',
                                'type' => 'submit',
                            ]
                        ),
                ];
            }

            if ($model->load($request->post())) {
                /*
                 * Se suman todos los detalles pendientes del mismo sentajero.
                 */
                $sqlMonto = '
                    SELECT SUM(detalle_importe_bs) AS monto_total
                    FROM detalle_descargos
                    WHERE desc_id = :desc_id
                      AND detalle_estado_pago = :pagado
                      AND detalle_estado = :estado
                      AND detalle_tasa IS NULL
                ';

                $montoTotal = Yii::$app->db
                    ->createCommand($sqlMonto)
                    ->bindValue(':desc_id', $model->desc_id)
                    ->bindValue(':pagado', 0)
                    ->bindValue(':estado', 1)
                    ->queryOne();

                $monto = isset($montoTotal['monto_total'])
                    ? (float)$montoTotal['monto_total']
                    : 0;

                if ($monto <= 0) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $titulo,
                        'content' =>
                            '<span class="text-danger text-bold">' .
                            'No existen detalles pendientes con un importe ' .
                            'válido para generar la tasa.' .
                            '</span>',
                        'footer' => Html::button(
                            'Cerrar',
                            [
                                'class' => 'btn btn-default pull-left',
                                'data-dismiss' => 'modal',
                            ]
                        ),
                    ];
                }

                $token = Yii::$app->ruatServices->loginConfigured();

                if (!$token) {
                    $mensaje = 'No se pudo iniciar sesión en RUAT.';
                } else {
                    $sentajero = Descargos::findOne($model->desc_id);

                    if ($sentajero === null) {
                        $mensaje =
                            'No se encontró el descargo del sentajero.';
                    } else {
                        $ciContribuyente = trim(
                            (string)$sentajero->desc_ci
                        );

                        $tipoDocumento =
                            (int)$sentajero->desc_ext === 12
                                ? 'CE'
                                : 'CI';

                        $codigoContribuyente = Yii::$app
                            ->ruatServices
                            ->getContribuyentePorCi(
                                $token,
                                $ciContribuyente,
                                $tipoDocumento
                            );

                        if (!$codigoContribuyente) {
                            $mensaje =
                                'El contribuyente seleccionado no se ' .
                                'encuentra registrado en RUAT.<br>' .
                                'Debe registrar al contribuyente primero.';
                        } else {
                            /*
                             * Se conserva la verificación original.
                             * Actualmente no se consulta deuda porque
                             * $tieneDeudas permanece en false.
                             */
                            $tieneDeudas = false;

                            if ($tieneDeudas) {
                                $mensaje =
                                    'El contribuyente seleccionado tiene ' .
                                    'deudas pendientes. No se puede registrar ' .
                                    'la preliquidación.';
                            } else {
                                $actividad = RazonSociales::findOne(
                                    $sentajero->razon_id
                                );

                                if ($actividad === null) {
                                    $mensaje =
                                        'No se encontró la actividad o razón ' .
                                        'social del sentajero.';
                                } else {
                                    $observacion =
                                        'DATOS DE ACTIVIDAD: SENTAJES, ' .
                                        'Tipo de sentaje: ' .
                                        $actividad->razon_nombre;

                                    $observacion = mb_substr(
                                        $observacion,
                                        0,
                                        250,
                                        'UTF-8'
                                    );

                                    $textoLimpio = iconv(
                                        'UTF-8',
                                        'ASCII//TRANSLIT',
                                        $observacion
                                    );

                                    if ($textoLimpio === false) {
                                        $textoLimpio = $observacion;
                                    }

                                    $observacion = preg_replace(
                                        '/[^a-zA-Z0-9\s.\-,.:]/u',
                                        '',
                                        $textoLimpio
                                    );

                                    $response = Yii::$app
                                        ->ruatServices
                                        ->createTasa(
                                            $token,
                                            $username,
                                            $codigoContribuyente,
                                            $codigoClasificador,
                                            $monto,
                                            $observacion
                                        );

                                    if (
                                        $this->ruatContinuarFlujo($response)
                                        && isset($response->numeroTasa)
                                        && trim(
                                            (string)$response->numeroTasa
                                        ) !== ''
                                    ) {
                                        $nroTasa = trim(
                                            (string)$response->numeroTasa
                                        );

                                        /*
                                         * Se asigna la misma tasa y el mismo
                                         * clasificador a todos los detalles
                                         * incluidos en el monto agrupado.
                                         */
                                        $sqlActualizar = '
                                            UPDATE detalle_descargos
                                            SET detalle_tasa = :tasa,
                                                codigo_clasificador =
                                                    :codigo_clasificador
                                            WHERE desc_id = :desc_id
                                              AND detalle_estado_pago =
                                                    :pagado
                                              AND detalle_estado = :estado
                                              AND detalle_tasa IS NULL
                                        ';

                                        $filasActualizadas = Yii::$app->db
                                            ->createCommand($sqlActualizar)
                                            ->bindValue(
                                                ':tasa',
                                                $nroTasa
                                            )
                                            ->bindValue(
                                                ':codigo_clasificador',
                                                $codigoClasificador
                                            )
                                            ->bindValue(
                                                ':desc_id',
                                                $model->desc_id
                                            )
                                            ->bindValue(':pagado', 0)
                                            ->bindValue(':estado', 1)
                                            ->execute();

                                        if ($filasActualizadas > 0) {
                                            $resultado = true;
                                            $mensaje =
                                                'Se creó la tasa con éxito.';
                                        } else {
                                            /*
                                             * RUAT ya pudo haber creado la tasa.
                                             * Se registra un error para revisar
                                             * la sincronización local.
                                             */
                                            Yii::error(
                                                'RUAT creó la tasa ' .
                                                $nroTasa .
                                                ', pero no se actualizaron ' .
                                                'detalles locales para desc_id ' .
                                                $model->desc_id . '.',
                                                __METHOD__
                                            );

                                            $mensaje =
                                                'RUAT creó la tasa, pero no ' .
                                                'se pudieron actualizar los ' .
                                                'detalles locales.';
                                        }
                                    } else {
                                        $mensaje =
                                            'No se pudo registrar la tasa ' .
                                            'en RUAT.';
                                    }
                                }
                            }
                        }
                    }
                }

                if ($resultado) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $titulo,
                        'content' =>
                            '<span class="text-success text-bold">' .
                            $mensaje .
                            '<br>Nro. preliquidación: ' .
                            Html::encode($model->detalle_id) .
                            '<br>Importe total Bs.: ' .
                            number_format($monto, 2, '.', ',') .
                            '<br>Código clasificador: ' .
                            Html::encode($codigoClasificador) .
                            '</span>',
                        'footer' => Html::button(
                            'Cerrar',
                            [
                                'class' => 'btn btn-default pull-left',
                                'data-dismiss' => 'modal',
                            ]
                        ),
                    ];
                }

                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => $titulo,
                    'content' =>
                        '<span class="text-danger text-bold">' .
                        $mensaje .
                        '</span>',
                    'footer' => Html::button(
                        'Cerrar',
                        [
                            'class' => 'btn btn-default pull-left',
                            'data-dismiss' => 'modal',
                        ]
                    ),
                ];
            }

            return [
                'title' => $titulo,
                'content' => $this->renderAjax(
                    'cobrar',
                    ['model' => $model]
                ),
                'footer' =>
                    Html::button(
                        'Cerrar',
                        [
                            'class' => 'btn btn-default pull-left',
                            'data-dismiss' => 'modal',
                        ]
                    ) .
                    Html::button(
                        'Guardar',
                        [
                            'class' => 'btn btn-primary',
                            'type' => 'submit',
                        ]
                    ),
            ];
        }

        if ($model->load($request->post()) && $model->save()) {
            return $this->redirect([
                'view',
                'id' => $model->detalle_id,
            ]);
        }

        return $this->render('cobrar', [
            'model' => $model,
        ]);
    }

    /**
     * Consulta en RUAT las tasas locales pendientes de pago.
     *
     * @return string
     */
    public function actionUpdatePagados()
    {
        $this->verificarSesion();

        $sql = '
            SELECT DISTINCT detalle_tasa
            FROM detalle_descargos
            WHERE detalle_estado_pago = :pagado
              AND detalle_estado = :estado
              AND detalle_tasa IS NOT NULL
              AND TRIM(CAST(detalle_tasa AS TEXT)) <> \'\'
        ';

        $listaTasasNoPagadas = Yii::$app->db
            ->createCommand($sql)
            ->bindValue(':pagado', 0)
            ->bindValue(':estado', 1)
            ->queryAll();

        $token = Yii::$app->ruatServices->loginConfigured();

        if (!$token) {
            Yii::$app->session->setFlash(
                'error',
                'No se pudo iniciar sesión en RUAT.'
            );

            return $this->actionIndex();
        }

        foreach ($listaTasasNoPagadas as $tasa) {
            $nroTasa = isset($tasa['detalle_tasa'])
                ? trim((string)$tasa['detalle_tasa'])
                : '';

            if ($nroTasa === '') {
                continue;
            }

            try {
                $estaPagada = Yii::$app
                    ->ruatServices
                    ->buscarPagadoPorNroTasa(
                        $token,
                        $nroTasa
                    );

                if (!$estaPagada) {
                    Yii::info(
                        'La tasa ' .
                        $nroTasa .
                        ' todavía no registra pago en RUAT.',
                        __METHOD__
                    );

                    continue;
                }

                $pagoTasa = Yii::$app
                    ->ruatServices
                    ->buscarPagadoPorNroTasas(
                        $token,
                        $nroTasa
                    );

                if (!$pagoTasa) {
                    Yii::warning(
                        'RUAT indicó que la tasa ' .
                        $nroTasa .
                        ' está pagada, pero no devolvió datos del pago.',
                        __METHOD__
                    );

                    continue;
                }

                $observacion =
                    'Folio: ' .
                    (isset($pagoTasa->folio)
                        ? $pagoTasa->folio
                        : '-') .
                    ', Fecha Pago: ' .
                    (isset($pagoTasa->fechaPago)
                        ? $pagoTasa->fechaPago
                        : '-') .
                    ', Entidad Financiera: ' .
                    (isset($pagoTasa->entidadFinanciera)
                        ? $pagoTasa->entidadFinanciera
                        : '-') .
                    ', Monto Pagado: ' .
                    (isset($pagoTasa->montoPago)
                        ? $pagoTasa->montoPago
                        : '-');

                $sqlActualizar = '
                    UPDATE detalle_descargos
                    SET detalle_estado_pago = :pagado,
                        nro_comprobante = :comprobante,
                        detalle_observacion = :observacion
                    WHERE detalle_tasa = :tasa
                      AND detalle_estado = :estado
                      AND detalle_estado_pago = :pendiente
                ';

                Yii::$app->db
                    ->createCommand($sqlActualizar)
                    ->bindValue(':tasa', $nroTasa)
                    ->bindValue(':pagado', 1)
                    ->bindValue(':pendiente', 0)
                    ->bindValue(':estado', 1)
                    ->bindValue(':comprobante', $nroTasa)
                    ->bindValue(':observacion', $observacion)
                    ->execute();
            } catch (\Throwable $exception) {
                Yii::error(
                    'Error al consultar la tasa ' .
                    $nroTasa .
                    ' en RUAT: ' .
                    $exception->getMessage(),
                    __METHOD__
                );
            }
        }

        return $this->actionIndex();
    }

    /**
     * Creates a new detail.
     *
     * @return array|string|\yii\web\Response
     */
    public function actionCreate()
    {
        $this->verificarSesion();

        $request = Yii::$app->request;
        $model = new GeneradorDescargos();

        $model->detalle_fecha_entrega = date('Y-m-d H:i');
        $model->detalle_estado = 1;
        $model->detalle_estado_pago = 0;

        $tituloMod = 'PRELIQUIDAR DESCARGO';
        $mensaje = 'Registro exitoso';

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($request->isGet) {
                return [
                    'title' => $tituloMod,
                    'content' => $this->renderAjax(
                        'create',
                        ['model' => $model]
                    ),
                    'footer' =>
                        Html::button(
                            'Cerrar',
                            [
                                'class' => 'btn btn-default pull-left',
                                'data-dismiss' => 'modal',
                            ]
                        ) .
                        Html::button(
                            'Guardar',
                            [
                                'class' => 'btn btn-primary',
                                'type' => 'submit',
                            ]
                        ),
                ];
            }

            if (
                $model->load($request->post())
                && $model->save()
            ) {
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => $tituloMod,
                    'content' =>
                        '<span class="text-success">' .
                        $mensaje .
                        '</span>',
                    'footer' =>
                        Html::button(
                            'Cerrar',
                            [
                                'class' => 'btn btn-default pull-left',
                                'data-dismiss' => 'modal',
                            ]
                        ) .
                        Html::a(
                            'Crear más',
                            ['create'],
                            [
                                'class' => 'btn btn-primary',
                                'role' => 'modal-remote',
                            ]
                        ),
                ];
            }

            return [
                'title' => $tituloMod,
                'content' => $this->renderAjax(
                    'create',
                    ['model' => $model]
                ),
                'footer' =>
                    Html::button(
                        'Cerrar',
                        [
                            'class' => 'btn btn-default pull-left',
                            'data-dismiss' => 'modal',
                        ]
                    ) .
                    Html::button(
                        'Guardar',
                        [
                            'class' => 'btn btn-primary',
                            'type' => 'submit',
                        ]
                    ),
            ];
        }

        if (
            $model->load($request->post())
            && $model->save()
        ) {
            return $this->redirect([
                'view',
                'id' => $model->detalle_id,
            ]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Acción heredada para editar manualmente datos de tasa.
     *
     * @param integer $id
     * @return array|string|\yii\web\Response
     */
    public function actionGenerateTasa($id)
    {
        $this->verificarSesion();

        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $titulo = 'Generar tasa';

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($request->isGet) {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax(
                        'generate-tasa',
                        ['model' => $model]
                    ),
                    'footer' =>
                        Html::button(
                            'Cerrar',
                            [
                                'class' => 'btn btn-default pull-left',
                                'data-dismiss' => 'modal',
                            ]
                        ) .
                        Html::button(
                            'Guardar',
                            [
                                'class' => 'btn btn-primary',
                                'type' => 'submit',
                            ]
                        ),
                ];
            }

            if (
                $model->load($request->post())
                && $model->validate()
            ) {
                $resultado = $model->save();

                $mensaje = $resultado
                    ? 'Se realizó el cobro correctamente.'
                    : 'Error al realizar el cobro.';

                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => $titulo,
                    'content' =>
                        '<span class="' .
                        ($resultado
                            ? 'text-success'
                            : 'text-danger') .
                        '">' .
                        $mensaje .
                        '</span>',
                    'footer' => Html::button(
                        'Cerrar',
                        [
                            'class' => 'btn btn-default pull-left',
                            'data-dismiss' => 'modal',
                        ]
                    ),
                ];
            }

            return [
                'title' => $titulo,
                'content' => $this->renderAjax(
                    'generate-tasa',
                    ['model' => $model]
                ),
                'footer' =>
                    Html::button(
                        'Cerrar',
                        [
                            'class' => 'btn btn-default pull-left',
                            'data-dismiss' => 'modal',
                        ]
                    ) .
                    Html::button(
                        'Guardar',
                        [
                            'class' => 'btn btn-primary',
                            'type' => 'submit',
                        ]
                    ),
            ];
        }

        if (
            $model->load($request->post())
            && $model->save()
        ) {
            return $this->redirect([
                'view',
                'id' => $model->detalle_id,
            ]);
        }

        return $this->render('cobrar', [
            'model' => $model,
        ]);
    }

    /**
     * Genera el recibo de liquidación.
     *
     * @param integer $id
     * @return array|string
     */
    public function actionReciboLiquidacion($id)
    {
        $this->verificarSesion();

        $request = Yii::$app->request;
        $model = $this->findModel($id);

        $titulo = 'RECIBO COBRO SENTAJE';
        $archivo = 'preliquidacion_sentaje';
        $carpeta = 'reportes';

        $montoLiteral = $model->montoTotalLiteral();

        $parametros = [
            'id_detalle' => $id,
            'monto_literal' => '"' . $montoLiteral . '"',
        ];

        $url = $this->generarURLReportePdf(
            $carpeta,
            $archivo,
            $parametros
        );

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $titulo,
                'content' => $this->renderAjax(
                    'recibo_liquidacion',
                    [
                        'url' => $url,
                        'size' => 'modal-lg',
                    ]
                ),
                'footer' => Html::button(
                    'Cerrar',
                    [
                        'class' => 'btn btn-default pull-left',
                        'data-dismiss' => 'modal',
                    ]
                ),
            ];
        }

        return $this->render('recibo-liquidacion', [
            'url' => $url,
        ]);
    }

    /**
     * Genera un reporte Jasper en formato PDF.
     *
     * @param string $carpeta
     * @param string $file
     * @param array $parametros
     * @return string
     */
    protected function generarURLReportePdf(
        $carpeta,
        $file,
        $parametros = []
    ) {
        $archivo = $file;

        Yii::setAlias('@ruta', $carpeta);

        $jasper = Yii::$app->jasper;

        $jasper
            ->compile(
                Yii::getAlias('@ruta') .
                '/' .
                $archivo .
                '.jrxml'
            )
            ->execute();

        $jasper
            ->process(
                Yii::getAlias('@ruta') .
                '/' .
                $archivo .
                '.jasper',
                $parametros,
                ['pdf'],
                false
            )
            ->execute();

        return Yii::getAlias('@ruta') .
            '/' .
            $archivo .
            '.pdf';
    }

    /**
     * Updates an existing detail.
     *
     * @param integer $id
     * @return array|string|\yii\web\Response
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
                    'title' => 'Actualizar Descargo #' . $id,
                    'content' => $this->renderAjax(
                        'update',
                        ['model' => $model]
                    ),
                    'footer' =>
                        Html::button(
                            'Cerrar',
                            [
                                'class' => 'btn btn-default pull-left',
                                'data-dismiss' => 'modal',
                            ]
                        ) .
                        Html::button(
                            'Guardar',
                            [
                                'class' => 'btn btn-primary',
                                'type' => 'submit',
                            ]
                        ),
                ];
            }

            if (
                $model->load($request->post())
                && $model->save()
            ) {
                return [
                    'forceReload' => '#crud-datatable-pjax',
                    'title' => 'Descargo #' . $id,
                    'content' => $this->renderAjax(
                        'view',
                        ['model' => $model]
                    ),
                    'footer' =>
                        Html::button(
                            'Cerrar',
                            [
                                'class' => 'btn btn-default pull-left',
                                'data-dismiss' => 'modal',
                            ]
                        ) .
                        Html::a(
                            'Actualizar',
                            ['update', 'id' => $id],
                            [
                                'class' => 'btn btn-primary',
                                'role' => 'modal-remote',
                            ]
                        ),
                ];
            }

            return [
                'title' => 'Actualizar Descargo #' . $id,
                'content' => $this->renderAjax(
                    'update',
                    ['model' => $model]
                ),
                'footer' =>
                    Html::button(
                        'Cerrar',
                        [
                            'class' => 'btn btn-default pull-left',
                            'data-dismiss' => 'modal',
                        ]
                    ) .
                    Html::button(
                        'Guardar',
                        [
                            'class' => 'btn btn-primary',
                            'type' => 'submit',
                        ]
                    ),
            ];
        }

        if (
            $model->load($request->post())
            && $model->save()
        ) {
            return $this->redirect([
                'view',
                'id' => $model->detalle_id,
            ]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Performs a logical deletion of one detail.
     *
     * @param integer $id
     * @return array|\yii\web\Response
     */
    public function actionDelete($id)
    {
        $this->verificarSesion();

        $request = Yii::$app->request;

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $sql = '
                UPDATE detalle_descargos
                SET detalle_estado = 0
                WHERE detalle_id = :detalle_id
            ';

            Yii::$app->db
                ->createCommand($sql)
                ->bindValue(':detalle_id', (int)$id)
                ->execute();

            return [
                'forceCerrar' => true,
                'forceReload' => '#crud-datatable-pjax',
            ];
        }

        return $this->redirect(['index']);
    }

    /**
     * Deletes multiple records.
     *
     * This preserves the original physical-delete behavior.
     *
     * @return array|\yii\web\Response
     */
    public function actionBulkDelete()
    {
        $this->verificarSesion();

        $request = Yii::$app->request;
        $pks = explode(',', (string)$request->post('pks'));

        foreach ($pks as $pk) {
            $pk = trim($pk);

            if ($pk === '' || !ctype_digit($pk)) {
                continue;
            }

            $model = $this->findModel((int)$pk);
            $model->delete();
        }

        if ($request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'forceCerrar' => true,
                'forceReload' => '#crud-datatable-pjax',
            ];
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the GeneradorDescargos model.
     *
     * @param integer $id
     * @return GeneradorDescargos
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = GeneradorDescargos::findOne($id);

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException(
            'The requested page does not exist.'
        );
    }

    /**
     * Interpreta continuarFlujo enviado por RUAT.
     *
     * @param object|null $response
     * @return boolean
     */
    private function ruatContinuarFlujo($response)
    {
        if (
            $response === null
            || !isset($response->continuarFlujo)
        ) {
            return false;
        }

        $valor = $response->continuarFlujo;

        if (is_bool($valor)) {
            return $valor;
        }

        if (is_numeric($valor)) {
            return (int)$valor === 1;
        }

        if (is_string($valor)) {
            return in_array(
                strtoupper(trim($valor)),
                ['TRUE', '1', 'SI', 'SÍ'],
                true
            );
        }

        return false;
    }

    /**
     * Verifica la existencia de una sesión activa.
     *
     * @return \yii\web\Response|null
     */
    public function verificarSesion()
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->user->logout(true);

            return $this->goHome();
        }

        return null;
    }
}