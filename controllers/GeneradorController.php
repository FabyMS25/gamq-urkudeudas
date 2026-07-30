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
        $numeroTasaCreada = null;

        /*
         * SISURKU always uses the Sentajes Urkupiña classifier.
         */
        $codigoClasificador = isset(
            Yii::$app->params['clasificadores']['sentajes_urkupina']
        )
            ? trim(
                (string)Yii::$app
                    ->params['clasificadores']['sentajes_urkupina']
            )
            : '';

        if ($codigoClasificador === '') {
            throw new \RuntimeException(
                'No está configurado el clasificador sentajes_urkupina.'
            );
        }

        /*
         * Obtain the RUAT username of the authenticated SISURKU user.
         */
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

            /*
             * Open the tasa generation modal.
             */
            if ($request->isGet) {
                return [
                    'title' => $titulo,
                    'content' => $this->renderAjax(
                        'cobrar',
                        [
                            'model' => $model,
                        ]
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

            /*
             * Process modal submission.
             */
            if ($model->load($request->post())) {
                /*
                 * Calculate the total of every active, unpaid and untaxed
                 * detail belonging to the same sentajero/descargo.
                 */
                $sqlMonto = '
                    SELECT SUM(detalle_importe_bs) AS monto_total
                    FROM detalle_descargos
                    WHERE desc_id = :desc_id
                      AND detalle_estado_pago = :pagado
                      AND detalle_estado = :estado
                      AND detalle_tasa IS NULL
                ';

                $resultadoMonto = Yii::$app->db
                    ->createCommand($sqlMonto)
                    ->bindValue(':desc_id', $model->desc_id)
                    ->bindValue(':pagado', 0)
                    ->bindValue(':estado', 1)
                    ->queryOne();

                $monto = isset($resultadoMonto['monto_total'])
                    ? (float)$resultadoMonto['monto_total']
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

                /*
                 * Authenticate in RUAT.
                 */
                $token = Yii::$app->ruatServices->loginConfigured();

                if (!$token) {
                    $mensaje = 'No se pudo iniciar sesión en RUAT.';
                } else {
                    /*
                     * Obtain the sentajero attached to the selected detail.
                     */
                    $sentajero = Descargos::findOne($model->desc_id);

                    if ($sentajero === null) {
                        $mensaje =
                            'No se encontró el descargo correspondiente al sentajero.';
                    } else {
                        $ciContribuyente = trim(
                            (string)$sentajero->desc_ci
                        );

                        /*
                         * SISURKU uses ext_id 12 to identify CE.
                         */
                        $tipoDocumento =
                            (int)$sentajero->desc_ext === 12
                                ? 'CE'
                                : 'CI';

                        /*
                         * Find the contributor in RUAT.
                         */
                        $codigoContribuyente = Yii::$app
                            ->ruatServices
                            ->getContribuyentePorCi(
                                $token,
                                $ciContribuyente,
                                $tipoDocumento
                            );

                        if (!$codigoContribuyente) {
                            $mensaje =
                                'El contribuyente seleccionado no se encuentra ' .
                                'registrado en RUAT.<br>' .
                                'Debe registrar al contribuyente primero.';
                        } else {
                            /*
                             * Keep the original SISURKU flow.
                             */
                            $tieneDeudas = false;

                            if ($tieneDeudas) {
                                $mensaje =
                                    'El contribuyente seleccionado tiene deudas ' .
                                    'pendientes. No se puede registrar la ' .
                                    'preliquidación.';
                            } else {
                                /*
                                 * Obtain the sentaje activity.
                                 */
                                $actividad = RazonSociales::findOne(
                                    $sentajero->razon_id
                                );

                                if ($actividad === null) {
                                    $mensaje =
                                        'No se encontró la actividad del sentajero.';
                                } else {
                                    /*
                                     * Build the observation sent to RUAT.
                                     */
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

                                    /*
                                     * Register the grouped tasa in RUAT.
                                     */
                                    try {
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
                                    } catch (\Throwable $exception) {
                                        Yii::error(
                                            [
                                                'mensaje' =>
                                                    'Excepción al registrar la tasa en RUAT.',
                                                'desc_id' => $model->desc_id,
                                                'codigoClasificador' =>
                                                    $codigoClasificador,
                                                'monto' => $monto,
                                                'error' =>
                                                    $exception->getMessage(),
                                            ],
                                            __METHOD__
                                        );

                                        $response = null;
                                        $mensaje =
                                            'Ocurrió un error al comunicarse con RUAT.';
                                    }

                                    if ($response !== null) {
                                        /*
                                         * RUAT responses may be returned as an object
                                         * or as an associative array.
                                         */
                                        $continuarFlujo =
                                            $this->getRuatResponseValue(
                                                $response,
                                                'continuarFlujo'
                                            );

                                        $numeroTasa =
                                            $this->getRuatResponseValue(
                                                $response,
                                                'numeroTasa'
                                            );

                                        /*
                                         * Try the possible RUAT message fields.
                                         */
                                        $mensajeRuat =
                                            $this->getRuatResponseValue(
                                                $response,
                                                'mensaje'
                                            );

                                        if (
                                            $mensajeRuat === null
                                            || trim(
                                                (string)$mensajeRuat
                                            ) === ''
                                        ) {
                                            $mensajeRuat =
                                                $this->getRuatResponseValue(
                                                    $response,
                                                    'mensajeRespuesta'
                                                );
                                        }

                                        if (
                                            $mensajeRuat === null
                                            || trim(
                                                (string)$mensajeRuat
                                            ) === ''
                                        ) {
                                            $mensajeRuat =
                                                $this->getRuatResponseValue(
                                                    $response,
                                                    'descripcion'
                                                );
                                        }

                                        if (
                                            $mensajeRuat === null
                                            || trim(
                                                (string)$mensajeRuat
                                            ) === ''
                                        ) {
                                            $mensajeRuat =
                                                $this->getRuatResponseValue(
                                                    $response,
                                                    'message'
                                                );
                                        }

                                        /*
                                         * RUAT authorized the operation and returned
                                         * a tasa number.
                                         */
                                        if (
                                            $this->ruatContinuarFlujo(
                                                $continuarFlujo
                                            )
                                            && $numeroTasa !== null
                                            && trim(
                                                (string)$numeroTasa
                                            ) !== ''
                                        ) {
                                            $numeroTasaCreada = trim(
                                                (string)$numeroTasa
                                            );

                                            /*
                                             * Save the tasa and classifier on every
                                             * detail included in the grouped amount.
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

                                            try {
                                                $filasActualizadas =
                                                    Yii::$app->db
                                                        ->createCommand(
                                                            $sqlActualizar
                                                        )
                                                        ->bindValue(
                                                            ':tasa',
                                                            $numeroTasaCreada
                                                        )
                                                        ->bindValue(
                                                            ':codigo_clasificador',
                                                            $codigoClasificador
                                                        )
                                                        ->bindValue(
                                                            ':desc_id',
                                                            $model->desc_id
                                                        )
                                                        ->bindValue(
                                                            ':pagado',
                                                            0
                                                        )
                                                        ->bindValue(
                                                            ':estado',
                                                            1
                                                        )
                                                        ->execute();

                                                if ($filasActualizadas > 0) {
                                                    $resultado = true;
                                                    $mensaje =
                                                        'Se creó la tasa con éxito.';
                                                } else {
                                                    Yii::error(
                                                        [
                                                            'mensaje' =>
                                                                'RUAT creó la tasa, pero no se actualizaron los detalles locales.',
                                                            'numeroTasa' =>
                                                                $numeroTasaCreada,
                                                            'desc_id' =>
                                                                $model->desc_id,
                                                            'codigoClasificador' =>
                                                                $codigoClasificador,
                                                        ],
                                                        __METHOD__
                                                    );

                                                    $mensaje =
                                                        'RUAT creó la tasa ' .
                                                        Html::encode(
                                                            $numeroTasaCreada
                                                        ) .
                                                        ', pero no se pudieron ' .
                                                        'actualizar los detalles locales.';
                                                }
                                            } catch (\Throwable $exception) {
                                                Yii::error(
                                                    [
                                                        'mensaje' =>
                                                            'RUAT creó la tasa, pero ocurrió un error al guardar localmente.',
                                                        'numeroTasa' =>
                                                            $numeroTasaCreada,
                                                        'desc_id' =>
                                                            $model->desc_id,
                                                        'codigoClasificador' =>
                                                            $codigoClasificador,
                                                        'error' =>
                                                            $exception->getMessage(),
                                                    ],
                                                    __METHOD__
                                                );

                                                $mensaje =
                                                    'RUAT creó la tasa ' .
                                                    Html::encode(
                                                        $numeroTasaCreada
                                                    ) .
                                                    ', pero ocurrió un error al ' .
                                                    'guardar los datos locales.';
                                            }
                                        } else {
                                            /*
                                             * Log the complete RUAT response so the
                                             * real rejection can be reviewed.
                                             */
                                            Yii::error(
                                                [
                                                    'mensaje' =>
                                                        'RUAT rechazó la creación de la tasa.',
                                                    'desc_id' =>
                                                        $model->desc_id,
                                                    'codigoClasificador' =>
                                                        $codigoClasificador,
                                                    'monto' => $monto,
                                                    'codigoContribuyente' =>
                                                        $codigoContribuyente,
                                                    'continuarFlujo' =>
                                                        $continuarFlujo,
                                                    'numeroTasa' =>
                                                        $numeroTasa,
                                                    'mensajeRuat' =>
                                                        $mensajeRuat,
                                                    'respuestaRuat' =>
                                                        $response,
                                                ],
                                                __METHOD__
                                            );

                                            $mensaje =
                                                'No se pudo registrar la tasa en RUAT.';

                                            if (
                                                $mensajeRuat !== null
                                                && trim(
                                                    (string)$mensajeRuat
                                                ) !== ''
                                            ) {
                                                $mensaje .=
                                                    '<br>Detalle RUAT: ' .
                                                    Html::encode(
                                                        (string)$mensajeRuat
                                                    );
                                            } elseif (
                                                $continuarFlujo !== null
                                            ) {
                                                $mensaje .=
                                                    '<br>RUAT no autorizó la ' .
                                                    'continuación del flujo.';
                                            } else {
                                                $mensaje .=
                                                    '<br>RUAT devolvió una respuesta ' .
                                                    'sin el campo continuarFlujo.';
                                            }
                                        }
                                    } elseif ($mensaje === '') {
                                        $mensaje =
                                            'RUAT no devolvió una respuesta válida.';
                                    }
                                }
                            }
                        }
                    }
                }

                /*
                 * Return success response.
                 */
                if ($resultado) {
                    return [
                        'forceReload' => '#crud-datatable-pjax',
                        'title' => $titulo,
                        'content' =>
                            '<span class="text-success text-bold">' .
                            Html::encode($mensaje) .
                            '<br>Nro. tasa: ' .
                            Html::encode($numeroTasaCreada) .
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

                /*
                 * Return error response.
                 *
                 * $mensaje may contain a controlled <br> separator, therefore
                 * it is not encoded as a complete string here. Dynamic values
                 * included in it were encoded previously.
                 */
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

            /*
             * The model could not load the submitted modal data.
             */
            return [
                'title' => $titulo,
                'content' => $this->renderAjax(
                    'cobrar',
                    [
                        'model' => $model,
                    ]
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

        /*
         * Non-AJAX fallback.
         */
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
 * Obtains one field from a RUAT response.
 *
 * RUAT services may return a stdClass object or an associative array,
 * depending on how the response was decoded.
 *
 * @param mixed $response
 * @param string $field
 * @return mixed|null
 */
    private function getRuatResponseValue($response, $field)
    {
        if (
            is_object($response)
            && property_exists($response, $field)
        ) {
            return $response->$field;
        }

        if (
            is_array($response)
            && array_key_exists($field, $response)
        ) {
            return $response[$field];
        }

        return null;
    }
/**
 * Interprets the continuarFlujo value returned by RUAT.
 *
 * Possible accepted values:
 *
 * true
 * 1
 * "1"
 * "true"
 * "si"
 * "sí"
 * "ok"
 *
 * @param mixed $value
 * @return boolean
 */
private function ruatContinuarFlujo($value)
{
    if (is_bool($value)) {
        return $value;
    }

    if (is_int($value) || is_float($value)) {
        return (int)$value === 1;
    }

    if (is_string($value)) {
        return in_array(
            strtoupper(trim($value)),
            [
                'TRUE',
                '1',
                'SI',
                'SÍ',
                'OK',
            ],
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