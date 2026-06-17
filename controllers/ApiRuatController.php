<?php
namespace app\controllers;

use Yii;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use app\models\Contribuyentes;
use app\models\Pagos;
use app\models\PagosEventuales;
use app\models\PagosInfracciones;

class ApiRuatController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => $this->allowedOrigins(),
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Request-Method' => ['POST', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'login'                         => ['POST', 'OPTIONS'],
                    'consultar-contribuyente'       => ['POST', 'OPTIONS'],
                    'consulta-deudas-contribuyente' => ['POST', 'OPTIONS'],
                    'consulta-pago-tasa'            => ['POST', 'OPTIONS'],
                    'registrar-contribuyente'       => ['POST', 'OPTIONS'],
                    'registrar-pago-infraccion'     => ['POST', 'OPTIONS'],
                    'anular-tasa'                   => ['POST', 'OPTIONS'],
                ],
            ],
        ];
    }

    private function allowedOrigins(): array
    {
        return [
            'http://localhost:4200',
            'http://localhost:5200',
            'http://localhost:5173',
            'http://127.0.0.1:4200',
            'http://127.0.0.1:5200',
            'http://127.0.0.1:5173',
            'http://181.177.143.185:4205',
        ];
    }

    private function applyCorsPreflightHeaders()
    {
        $origin = Yii::$app->request->headers->get('Origin');
        if (in_array($origin, $this->allowedOrigins(), true)) {
            Yii::$app->response->headers->set('Access-Control-Allow-Origin', $origin);
            Yii::$app->response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        $requestHeaders = Yii::$app->request->headers->get('Access-Control-Request-Headers');

        Yii::$app->response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS');
        Yii::$app->response->headers->set(
            'Access-Control-Allow-Headers',
            $requestHeaders ?: 'Content-Type, Authorization, X-Requested-With'
        );
        Yii::$app->response->headers->set('Access-Control-Max-Age', '86400');
    }

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->request->isOptions) {
            $this->applyCorsPreflightHeaders();
            Yii::$app->response->statusCode = 204;
            return false;
        }

        return parent::beforeAction($action);
    }

    // -------------------------------------------------------------------------
    // Auth helpers
    // -------------------------------------------------------------------------

    private function tokenFromRequest(array $body)
    {
        $authorization = Yii::$app->request->headers->get('Authorization');

        if ($authorization && preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
            return trim($matches[1]);
        }

        if (isset($body['token']) && trim((string)$body['token']) !== '') {
            return trim((string)$body['token']);
        }

        if (isset($body['accessToken']) && trim((string)$body['accessToken']) !== '') {
            return trim((string)$body['accessToken']);
        }

        return $this->loginFromRequest($body);
    }

    private function loginFromRequest(array $body)
    {
        $credentials = isset($body['credentials']) && is_array($body['credentials'])
            ? $body['credentials']
            : $body;

        $username = $this->firstRequiredString($credentials, ['username', 'usuario']);
        $password = $this->firstRequiredString($credentials, ['password', 'clave']);

        $token = Yii::$app->ruatServices->login($username, $password);

        if (!$token) {
            throw new UnauthorizedHttpException('No se pudo autenticar en RUAT.');
        }

        return $token;
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function actionLogin()
    {
        $body = $this->requestBodyParams();
        $token = $this->loginFromRequest($body);

        return [
            'success' => true,
            'token' => $token,
        ];
    }

    public function actionConsultarContribuyente()
    {
        $body = $this->requestBodyParams();
        $data = $this->taxpayerDataFromRequest($body);

        $token = $this->tokenFromRequest($body);
        $codigoAlcaldia = $this->requiredString($body, 'codigoAlcaldia');

        $numeroDocumento = $this->firstRequiredString($data, ['contri_ci']);
        $tipoDocumento = $this->tipoDocumento($this->firstRequiredString($data, ['contri_tipo_documento_ruat']));
        $expedidoRuat = $this->ruatExpedidoFromData($data, $tipoDocumento, false);

        $response = Yii::$app->ruatServices->getContribuyentePorCiResponse(
            $token,
            $numeroDocumento,
            $tipoDocumento,
            $codigoAlcaldia,
            $expedidoRuat
        );

        $localContribuyente = null;
        if ($response !== null && isset($response->codigoContribuyente)) {
            $localContribuyente = $this->syncLocalContribuyenteFromRuat($body, $response, false);
        } else {
            $localContribuyente = Contribuyentes::findOne(['contri_ci' => $numeroDocumento]);
        }

        if ($localContribuyente !== null && !$localContribuyente->hasErrors()) {
            $localContribuyente->contri_estado_operativo = 'REGISTRADO';
            $localContribuyente->save(false);
        }

        return $this->ruatApiResponse($response, [
            'existe' => $response !== null && isset($response->codigoContribuyente),
            'codigoContribuyente' => $response && isset($response->codigoContribuyente)
                ? $response->codigoContribuyente
                : null,
            'contribuyente' => $response && isset($response->contribuyente)
                ? $response->contribuyente
                : null,
            'localContribuyente' => $localContribuyente ? $localContribuyente->attributes : null,
            'ruat' => $response,
        ]);
    }

    public function actionConsultaDeudasContribuyente()
    {
        $body = $this->requestBodyParams();
        $token = $this->tokenFromRequest($body);
        $codigoAlcaldia = $this->requiredString($body, 'codigoAlcaldia');

        $contribuyente = $this->contribuyenteFromRequest($body);
        $tipoDocumento = $contribuyente->contri_tipo_documento_ruat;
        $expedidoRuat = $this->ruatExpedidoFromData($this->taxpayerDataFromRequest($body), $tipoDocumento, false);

        $response = Yii::$app->ruatServices->getTieneDeudaContribuyentePorNroDocumento(
            $token,
            $contribuyente,
            $codigoAlcaldia,
            1,
            $expedidoRuat
        );
        $localContribuyente = Contribuyentes::findOne(['contri_ci' => $contribuyente->contri_ci]);
        if ($localContribuyente !== null) {
            $ruatContribuyenteResponse = Yii::$app->ruatServices->getContribuyentePorCiResponse(
                $token,
                $contribuyente->contri_ci,
                $tipoDocumento,
                $codigoAlcaldia,
                $expedidoRuat
            );

            if ($ruatContribuyenteResponse !== null && isset($ruatContribuyenteResponse->codigoContribuyente)) {
                $syncedContribuyente = $this->syncLocalContribuyenteFromRuat($body, $ruatContribuyenteResponse, false);

                if ($syncedContribuyente !== null && !$syncedContribuyente->hasErrors()) {
                    $localContribuyente = $syncedContribuyente;
                }
            }

            $tieneDeudas = isset($response->continuarFlujo) ? (bool)$response->continuarFlujo : false;
            $localContribuyente->contri_estado_operativo = $tieneDeudas
                ? 'CON_DEUDAS_RUAT'
                : 'REGISTRADO';
            $localContribuyente->save(false);
        }

        return $this->ruatApiResponse($response, [
            'tieneDeudas' => isset($response->continuarFlujo) ? (bool)$response->continuarFlujo : null,
            'localContribuyente' => $localContribuyente ? $localContribuyente->attributes : null,
        ]);
    }

    public function actionConsultaPagoTasa()
    {
        $body = $this->requestBodyParams();

        $token = $this->tokenFromRequest($body);
        $codigoAlcaldia = $this->requiredString($body, 'codigoAlcaldia');
        $numeroTasa = $this->firstRequiredString($body, ['numeroTasa', 'numero_tasa', 'pago_tasa', 'eventual_tasa']);

        $response = Yii::$app->ruatServices->consultaPagoTasa($token, $numeroTasa, $codigoAlcaldia);

        $localRegistros = $this->syncLocalPagosFromRuat($numeroTasa, $response);

        return $this->ruatApiResponse($response, [
            'pagado' => isset($response->continuarFlujo) ? (bool)$response->continuarFlujo : false,
            'registroLocalTipos' => array_keys($localRegistros),
            'registrosLocales' => $localRegistros,
            'pagoInfraccion' => isset($localRegistros['infraccion']) ? $localRegistros['infraccion'] : null,
        ]);
    }

    public function actionRegistrarContribuyente()
    {
        $body = $this->requestBodyParams();

        $token = $this->tokenFromRequest($body);
        $codigoAlcaldia = $this->requiredString($body, 'codigoAlcaldia');
        $codigoUsuario = $this->requiredString($body, 'codigoUsuario');

        $contribuyente = $this->contribuyenteRegistroFromRequest($body);

        $tipoDocumento = $contribuyente->contri_tipo_documento_ruat;
        $expedidoRuat = $this->ruatExpedidoFromData($this->taxpayerDataFromRequest($body), $tipoDocumento, true);

        $localExistente = Contribuyentes::findOne(['contri_ci' => $contribuyente->contri_ci]);

        $ruatResponse = Yii::$app->ruatServices->getContribuyentePorCiResponse(
            $token,
            $contribuyente->contri_ci,
            $tipoDocumento,
            $codigoAlcaldia,
            $expedidoRuat
        );

        $existeRuat = $ruatResponse !== null && isset($ruatResponse->codigoContribuyente);

        if ($localExistente !== null && $existeRuat) {
            $this->applyRuatContribuyenteMetadata($localExistente, $ruatResponse);
            $localExistente->save(false);

            return [
                'success' => true,
                'accion' => 'YA_EXISTE_EN_RUAT_Y_LOCAL',
                'mensaje' => 'El contribuyente ya existe en RUAT y en la base local.',
                'codigoContribuyente' => $ruatResponse->codigoContribuyente,
                'localContribuyente' => $localExistente->attributes,
                'ruat' => $ruatResponse,
            ];
        }

        if ($localExistente === null && $existeRuat) {
            $localContribuyente = $this->syncLocalContribuyenteFromRuat($body, $ruatResponse, true);

            return [
                'success' => $localContribuyente !== null && !$localContribuyente->hasErrors(),
                'accion' => 'SINCRONIZADO_LOCAL_DESDE_RUAT',
                'mensaje' => 'El contribuyente existía en RUAT y fue registrado localmente.',
                'codigoContribuyente' => $ruatResponse->codigoContribuyente,
                'localContribuyente' => $localContribuyente && !$localContribuyente->hasErrors()
                    ? $localContribuyente->attributes
                    : null,
                'localErrors' => $localContribuyente && $localContribuyente->hasErrors()
                    ? $localContribuyente->getErrors()
                    : null,
                'ruat' => $ruatResponse,
            ];
        }

        if (!$existeRuat) {
            $motivo = $this->requiredString($body, 'motivo');
            $observacion = $this->stringValue($body, 'observacion', '');
            $observacionErrors = $this->validateRuatObservacion($observacion);

            if (!empty($observacionErrors)) {
                return [
                    'success' => false,
                    'accion' => 'VALIDACION_FORMULARIO',
                    'mensaje' => implode(' ', $observacionErrors),
                    'fieldErrors' => [
                        'observacion' => $observacionErrors,
                    ],
                    'localContribuyente' => null,
                    'localErrors' => null,
                    'ruat' => null,
                ];
            }

            $registroRuatResponse = Yii::$app->ruatServices->registerContribuyenteResponse(
                $token,
                $codigoUsuario,
                $contribuyente,
                $codigoAlcaldia,
                $motivo,
                $observacion,
                $expedidoRuat
            );

            if (!$this->ruatContinuarFlujo($registroRuatResponse) || !isset($registroRuatResponse->codigoContribuyente)) {
                return [
                    'success' => false,
                    'accion' => 'ERROR_REGISTRO_RUAT',
                    'mensaje' => $this->ruatMensaje($registroRuatResponse, 'RUAT no registró el contribuyente.'),
                    'fieldErrors' => isset($registroRuatResponse->mensaje) ? $registroRuatResponse->mensaje : null,
                    'localContribuyente' => null,
                    'localErrors' => null,
                    'ruat' => $registroRuatResponse,
                ];
            }

            $ruatResponse = $this->buildRuatContribuyenteResponseFromLocal(
                $contribuyente,
                $registroRuatResponse,
                $tipoDocumento,
                $expedidoRuat
            );

            $localContribuyente = $this->syncLocalContribuyenteFromRuat($body, $ruatResponse, true);

            return [
                'success' => $localContribuyente !== null && !$localContribuyente->hasErrors(),
                'accion' => $localExistente === null ? 'REGISTRADO_RUAT_Y_LOCAL' : 'REGISTRADO_RUAT_ACTUALIZADO_LOCAL',
                'mensaje' => $localExistente === null
                    ? 'El contribuyente fue registrado en RUAT y en la base local.'
                    : 'El contribuyente fue registrado en RUAT y actualizado en la base local.',
                'codigoContribuyente' => $registroRuatResponse->codigoContribuyente,
                'localContribuyente' => $localContribuyente && !$localContribuyente->hasErrors()
                    ? $localContribuyente->attributes
                    : null,
                'localErrors' => $localContribuyente && $localContribuyente->hasErrors()
                    ? $localContribuyente->getErrors()
                    : null,
                'ruat' => $ruatResponse,
            ];
        }

        return [
            'success' => false,
            'mensaje' => 'No se pudo determinar el flujo de registro.',
        ];
    }

    public function actionRegistrarPagoInfraccion()
    {
        $body = $this->requestBodyParams();
        $data = $this->infraccionDataFromRequest($body);

        $token = $this->tokenFromRequest($body);
        $codigoAlcaldia = $this->requiredString($data, 'codigoAlcaldia');
        $codigoUsuario = $this->firstRequiredString($data, ['codigo_usuario', 'codigoUsuario']);

        $response = $this->createRuatTasa(
            $token,
            $codigoAlcaldia,
            $codigoUsuario,
            $this->firstRequiredString($data, ['codigo_contribuyente', 'codigoContribuyente']),
            $this->firstRequiredString($data, ['codigo_clasificador', 'codigoClasificador']),
            $this->requiredMontoString($data, 'monto'),
            $this->requiredString($data, 'observacion')
        );

        if (!$this->ruatContinuarFlujo($response) || !isset($response->numeroTasa)) {
            return [
                'success' => false,
                'mensaje' => $this->ruatMensaje($response, 'RUAT no registró la tasa de infracción.'),
                'ruat' => $response,
                'pagoInfraccion' => null,
                'localErrors' => null,
            ];
        }

        $localResult = $this->savePagoInfraccion($data, $codigoUsuario, $response);
        $model = $localResult['model'];
        $errors = $localResult['errors'];

        return [
            'success' => $errors === null,
            'mensajeLocal' => $errors === null
                ? 'Tasa de infracción registrada en RUAT y guardada localmente.'
                : 'RUAT registró la tasa, pero no se pudo guardar el pago de infracción local.',
            'ruat' => $response,
            'pagoInfraccion' => $model && !$model->hasErrors() ? $model->attributes : null,
            'localErrors' => $errors,
        ];
    }

    public function actionAnularTasa()
    {
        $body = $this->requestBodyParams();

        $token = $this->tokenFromRequest($body);
        $data = isset($body['tasa']) && is_array($body['tasa'])
            ? array_merge($body, $body['tasa'])
            : $body;

        $codigoAlcaldia = $this->requiredString($data, 'codigoAlcaldia');
        $codigoUsuario = $this->firstRequiredString($data, ['codigoUsuario', 'codigo_usuario']);
        $numeroTasa = $this->firstRequiredString($data, ['numeroTasa', 'numero_tasa', 'nroTasa']);
        $motivo = $this->firstRequiredString($data, ['motivoTasa', 'motivo', 'anulado_motivo']);
        $observacion = $this->firstRequiredString($data, ['observacion', 'anulado_observacion']);

        $this->validateMotivoAnulacion($motivo);
        $this->validateObservacionAnulacion($observacion);

        $response = Yii::$app->ruatServices->anularTasa(
            $token,
            $codigoUsuario,
            $numeroTasa,
            $motivo,
            $observacion,
            $codigoAlcaldia
        );

        $pagoInfraccion = null;
        $localErrors = null;

        if ($this->ruatContinuarFlujo($response)) {
            $pagoInfraccion = PagosInfracciones::findOne(['numero_tasa' => $numeroTasa]);

            if ($pagoInfraccion !== null) {
                $pagoInfraccion->infraccion_estado = PagosInfracciones::ESTADO_ANULADO;
                $pagoInfraccion->infraccion_anulado = 1;
                $pagoInfraccion->anulado_motivo = $motivo;
                $pagoInfraccion->anulado_observacion = $observacion;
                $pagoInfraccion->anulado_fecha_hora = date('Y-m-d H:i:s');
                $pagoInfraccion->updated_at = date('Y-m-d H:i:s');

                $localErrors = $pagoInfraccion->save(false) ? null : $pagoInfraccion->getErrors();
            }
        }

        return $this->ruatApiResponse($response, [
            'pagoInfraccion' => $pagoInfraccion ? $pagoInfraccion->attributes : null,
            'localErrors' => $localErrors,
        ]);
    }

    // -------------------------------------------------------------------------
    // Contribuyente builders
    // -------------------------------------------------------------------------

    private function taxpayerDataFromRequest(array $body): array
    {
        return isset($body['contribuyente']) && is_array($body['contribuyente'])
            ? array_merge($body, $body['contribuyente'])
            : $body;
    }

    private function contribuyenteFromRequest(array $body)
    {
        $data = $this->taxpayerDataFromRequest($body);

        $contribuyente = new \stdClass();
        $contribuyente->contri_ci = $this->firstRequiredString($data, ['contri_ci']);
        $contribuyente->contri_tipo_documento_ruat = $this->tipoDocumento(
            $this->firstRequiredString($data, ['contri_tipo_documento_ruat'])
        );
        $contribuyente->ext_id = $contribuyente->contri_tipo_documento_ruat === 'CE'
            ? 12
            : $this->requiredInteger($data, ['ext_id'], 'ext_id');

        return $contribuyente;
    }

    private function contribuyenteRegistroFromRequest(array $body)
    {
        $data = $this->taxpayerDataFromRequest($body);
        $contribuyente = $this->contribuyenteFromRequest($data);

        $contribuyente->sindi_id = $this->requiredInteger($data, ['sindi_id'], 'sindi_id');
        $contribuyente->contri_nombres = $this->firstRequiredString($data, ['contri_nombres']);
        $contribuyente->contri_paterno = $this->stringValue($data, 'contri_paterno', null);
        $contribuyente->contri_materno = $this->stringValue($data, 'contri_materno', null);
        $contribuyente->contri_apellidocasada = $this->stringValue($data, 'contri_apellidocasada', null);
        $contribuyente->contri_direccion = $this->firstRequiredString($data, ['contri_direccion']);
        $contribuyente->contri_telefono = $this->stringValue($data, 'contri_telefono', null);
        $contribuyente->contri_nit = $this->stringValue($data, 'contri_nit', null);
        $contribuyente->contri_fechanac = $this->localDateValue($this->stringValue($data, 'contri_fechanac', null));
        $contribuyente->contri_sexo = $this->validValue($data, 'contri_sexo', ['F', 'M']);
        $contribuyente->contri_estadocivil = $this->validValue($data, 'contri_estadocivil', ['SO', 'CA', 'VI', 'DI']);
        $contribuyente->contri_tipo_contribuyente_ruat = $this->validValue($data, 'contri_tipo_contribuyente_ruat', ['NA', 'JU']);
        $contribuyente->contri_estado = $this->optionalInteger($data, ['contri_estado'], 'contri_estado') ?? 1;
        $contribuyente->contri_fecharegistro = $this->stringValue($data, 'contri_fecharegistro', date('Y-m-d'));

        return $contribuyente;
    }

    // -------------------------------------------------------------------------
    // Local DB sync
    // -------------------------------------------------------------------------

    private function syncLocalContribuyenteFromRuat(array $body, $ruatResponse, bool $createIfMissing = true)
    {
        $data = $this->taxpayerDataFromRequest($body);

        $numeroDocumento = isset($ruatResponse->contribuyente->numeroDocumento)
            ? trim((string)$ruatResponse->contribuyente->numeroDocumento)
            : $this->firstRequiredString($data, ['contri_ci']);

        $model = Contribuyentes::findOne(['contri_ci' => $numeroDocumento]);
        $isNew = ($model === null);

        if ($isNew && !$createIfMissing) {
            return null;
        }

        if ($isNew) {
            $model = new Contribuyentes();
            $model->contri_ci = $numeroDocumento;
            $model->contri_estado = 1;
            $model->contri_fecharegistro = date('Y-m-d');
        }

        $ruatContribuyente = isset($ruatResponse->contribuyente) ? $ruatResponse->contribuyente : null;

        $model->ext_id = $this->localExtIdFromData($data, $ruatContribuyente);
        $model->sindi_id = $this->optionalInteger($data, ['sindi_id'], 'sindi_id') ?: $model->sindi_id;
        $model->contri_nombres = $this->localValue($data, ['contri_nombres'], $ruatContribuyente, 'nombre') ?: $model->contri_nombres;
        $model->contri_paterno = $this->localValue($data, ['contri_paterno'], $ruatContribuyente, 'primerApellido') ?: $model->contri_paterno;
        $model->contri_materno = $this->localValue($data, ['contri_materno'], $ruatContribuyente, 'segundoApellido') ?: $model->contri_materno;
        $model->contri_apellidocasada = $this->localValue($data, ['contri_apellidocasada'], $ruatContribuyente, 'apellidoEsposo') ?? $model->contri_apellidocasada;
        $model->contri_direccion = $this->localDireccion($data, $ruatContribuyente, $model->contri_direccion);
        $model->contri_telefono = $this->localTelefono($data, $ruatContribuyente, $model->contri_telefono);
        $model->contri_nit = $this->localNit($data, $ruatContribuyente, $model->contri_nit);

        $fechaNacimiento = $this->localValue($data, ['contri_fechanac'], $ruatContribuyente, 'fechaNacimiento');
        $model->contri_fechanac = $fechaNacimiento !== null
            ? $this->localDateValue($fechaNacimiento)
            : $model->contri_fechanac;
        $model->contri_sexo = $this->localSexo($data, $ruatContribuyente, $model->contri_sexo);
        $model->contri_estadocivil = $this->localEstadoCivil($data, $ruatContribuyente, $model->contri_estadocivil);
        $model->contri_estado = $this->optionalInteger($data, ['contri_estado'], 'contri_estado') ?? $model->contri_estado ?? 1;

        if ($model->sindi_id === null) {
            $model->sindi_id = $this->requiredInteger($data, ['sindi_id'], 'sindi_id');
        }

        $this->applyRuatContribuyenteMetadata($model, $ruatResponse);

        $model->save();

        return $model;
    }

    private function applyRuatContribuyenteMetadata(Contribuyentes $model, $ruatResponse)
    {
        $ruatContribuyente = isset($ruatResponse->contribuyente) ? $ruatResponse->contribuyente : null;

        if (isset($ruatResponse->codigoContribuyente)) {
            $model->contri_codigo_ruat = $ruatResponse->codigoContribuyente;
        }

        $model->contri_tipo_contribuyente_ruat = $ruatContribuyente && isset($ruatContribuyente->tipoContribuyente)
            ? $ruatContribuyente->tipoContribuyente
            : $model->contri_tipo_contribuyente_ruat;

        $model->contri_tipo_documento_ruat = $ruatContribuyente && isset($ruatContribuyente->tipoDocumento)
            ? $this->localTipoDocumentoFromRuat($ruatContribuyente->tipoDocumento)
            : $model->contri_tipo_documento_ruat;

        $model->contri_estado_ruat = $ruatContribuyente && isset($ruatContribuyente->estado)
            ? $ruatContribuyente->estado
            : $model->contri_estado_ruat;

        $model->contri_ruat_sync_at = date('Y-m-d H:i:s');
        if (!$model->contri_estado_operativo) {
            $model->contri_estado_operativo = 'REGISTRADO';
        }
    }

    private function buildRuatContribuyenteResponseFromLocal($contribuyente, $registroRuatResponse, string $tipoDocumento, string $expedidoRuat)
    {
        return (object)[
            'mensaje' => isset($registroRuatResponse->mensaje) ? $registroRuatResponse->mensaje : null,
            'continuarFlujo' => true,
            'codigoContribuyente' => $registroRuatResponse->codigoContribuyente,
            'contribuyente' => (object)[
                'numeroDocumento' => $contribuyente->contri_ci,
                'tipoDocumento' => $tipoDocumento,
                'expedido' => $expedidoRuat,
                'nombre' => $contribuyente->contri_nombres,
                'primerApellido' => $contribuyente->contri_paterno,
                'segundoApellido' => $contribuyente->contri_materno,
                'apellidoEsposo' => $contribuyente->contri_apellidocasada,
                'fechaNacimiento' => $contribuyente->contri_fechanac,
                'genero' => $contribuyente->contri_sexo,
                'estadoCivil' => $contribuyente->contri_estadocivil,
                'estado' => 'ACTIVO',
                'tipoContribuyente' => $contribuyente->contri_tipo_contribuyente_ruat,
                'datosDireccion' => (object)[
                    'direccionDescriptiva' => $contribuyente->contri_direccion,
                ],
                'telefono' => $contribuyente->contri_telefono,
                'nit' => $contribuyente->contri_nit,
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Infracciones
    // -------------------------------------------------------------------------

    private function createRuatTasa($token, $codigoAlcaldia, $codigoUsuario, $codigoContribuyente, $codigoClasificador, $monto, $observacion)
    {
        return Yii::$app->ruatServices->createTasa(
            $token,
            $codigoUsuario,
            $codigoContribuyente,
            $codigoClasificador,
            $monto,
            $observacion,
            $codigoAlcaldia
        );
    }

    private function infraccionDataFromRequest(array $body): array
    {
        return isset($body['infraccion']) && is_array($body['infraccion'])
            ? array_merge($body, $body['infraccion'])
            : $body;
    }

    private function savePagoInfraccion(array $data, string $codigoUsuario, $ruatResponse): array
    {
        $numeroDocumento = $this->firstRequiredString($data, ['numero_documento', 'numeroDocumento', 'ci']);
        $contribuyente = Contribuyentes::findOne(['contri_ci' => $numeroDocumento]);
        $now = date('Y-m-d H:i:s');

        $model = new PagosInfracciones();
        $model->usua_id = $this->usuarioIdFromRequest($data);
        $model->contri_id = $contribuyente ? $contribuyente->contri_id : $this->optionalInteger($data, ['contri_id'], 'contri_id');
        $model->codigo_usuario = $codigoUsuario;
        $model->codigo_contribuyente = $this->firstRequiredString($data, ['codigo_contribuyente', 'codigoContribuyente']);
        $model->numero_documento = $numeroDocumento;
        $model->tipo_documento = $this->tipoDocumento($this->firstRequiredString($data, ['tipo_documento', 'tipoDocumento']));
        $model->expedido = $this->stringValue($data, 'expedido', null);
        $model->tipo_infraccion = $this->validFirstValue($data, ['tipo_infraccion', 'tipoInfraccion'], ['INFRACCION']);
        $model->descripcion_infraccion = $this->stringValue($data, 'descripcion_infraccion',
            $this->stringValue($data, 'descripcionInfraccion', null));
        $model->lugar_infraccion = $this->stringValue($data, 'lugar_infraccion',
            $this->stringValue($data, 'lugarInfraccion', null));
        $model->fecha_infraccion = $this->stringValue($data, 'fecha_infraccion',
            $this->stringValue($data, 'fechaInfraccion', date('Y-m-d')));
        $model->gestion = (string)$this->stringValue($data, 'gestion', date('Y'));
        $model->codigo_clasificador = $this->firstRequiredString($data, ['codigo_clasificador', 'codigoClasificador']);
        $model->monto = $this->requiredNumber($data, 'monto');
        $model->observacion = $this->requiredString($data, 'observacion');
        $model->numero_tasa = $ruatResponse->numeroTasa;
        $model->infraccion_estado = PagosInfracciones::ESTADO_ACTIVO;
        $model->infraccion_pagado = 0;
        $model->infraccion_anulado = 0;
        $model->registro_ruat_payload = json_encode($ruatResponse);
        $model->created_at = $now;
        $model->updated_at = $now;

        return [
            'model' => $model,
            'errors' => $model->save() ? null : $model->getErrors(),
        ];
    }

    private function syncLocalPagosFromRuat(string $numeroTasa, $ruatResponse): array
    {
        if (!$this->ruatContinuarFlujo($ruatResponse)) {
            return [];
        }

        $fechaPago = isset($ruatResponse->pagoTasa->fechaPago)
            ? $this->normalizeRuatDateTime($ruatResponse->pagoTasa->fechaPago)
            : date('Y-m-d H:i:s');

        $localRegistros = [];

        $pago = Pagos::findOne(['pago_tasa' => $numeroTasa]);
        if ($pago !== null) {
            $pago->pago_cobrado = 1;
            $pago->pago_fecha_hora_cobro = $fechaPago;
            $pago->save(false);
            $localRegistros['pago'] = $pago->attributes;
        }

        $pagoEventual = PagosEventuales::findOne(['eventual_tasa' => $numeroTasa]);
        if ($pagoEventual !== null) {
            $pagoEventual->eventual_cobrado = 1;
            $pagoEventual->eventual_fecha_hora_pago = $fechaPago;
            $pagoEventual->save(false);
            $localRegistros['eventual'] = $pagoEventual->attributes;
        }

        $pagoInfraccion = PagosInfracciones::findOne(['numero_tasa' => $numeroTasa]);
        if ($pagoInfraccion !== null) {
            $pagoInfraccion->infraccion_pagado = 1;
            $pagoInfraccion->fecha_pago = $fechaPago;
            $pagoInfraccion->pago_ruat_payload = json_encode($ruatResponse);
            $pagoInfraccion->updated_at = date('Y-m-d H:i:s');
            $pagoInfraccion->save(false);
            $localRegistros['infraccion'] = $pagoInfraccion->attributes;
        }

        return $localRegistros;
    }

    // -------------------------------------------------------------------------
    // Field extractors
    // -------------------------------------------------------------------------

    private function localValue(array $data, array $keys, $ruatContribuyente = null, $ruatKey = null)
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && trim((string)$data[$key]) !== '') {
                return trim((string)$data[$key]);
            }
        }

        if ($ruatContribuyente !== null && $ruatKey !== null && isset($ruatContribuyente->$ruatKey)) {
            return trim((string)$ruatContribuyente->$ruatKey);
        }

        return null;
    }

    private function localDireccion(array $data, $ruatContribuyente = null, $fallback = null)
    {
        $direccion = $this->localValue($data, ['contri_direccion'], null, null);

        if ($direccion !== null) {
            return $direccion;
        }

        if ($ruatContribuyente !== null
            && isset($ruatContribuyente->datosDireccion->direccionDescriptiva)
            && trim((string)$ruatContribuyente->datosDireccion->direccionDescriptiva) !== ''
        ) {
            return trim((string)$ruatContribuyente->datosDireccion->direccionDescriptiva);
        }

        return $fallback ?: 'SIN DIRECCION';
    }

    private function localTelefono(array $data, $ruatContribuyente = null, $fallback = null)
    {
        $value = $this->localValue($data, ['contri_telefono'], $ruatContribuyente, 'telefono');
        return $value !== null ? $this->nullableInt($value) : $fallback;
    }

    private function localNit(array $data, $ruatContribuyente = null, $fallback = null)
    {
        $value = $this->localValue($data, ['contri_nit'], $ruatContribuyente, 'nit');
        return $value !== null ? $this->nullableInt($value) : $fallback;
    }

    private function localSexo(array $data, $ruatContribuyente = null, $fallback = null)
    {
        $value = $this->localValue($data, ['contri_sexo'], $ruatContribuyente, 'genero');

        if ($value === null) {
            return $fallback;
        }

        $value = strtoupper(trim((string)$value));

        if (in_array($value, ['F', 'FEMENINO', 'MUJER'], true)) {
            return 'F';
        }

        if (in_array($value, ['M', 'MASCULINO', 'HOMBRE'], true)) {
            return 'M';
        }

        return strlen($value) <= 2 ? $value : $fallback;
    }

    private function localEstadoCivil(array $data, $ruatContribuyente = null, $fallback = null)
    {
        $value = $this->localValue($data, ['contri_estadocivil'], $ruatContribuyente, 'estadoCivil');

        if ($value === null) {
            return $fallback;
        }

        $value = strtoupper(trim((string)$value));

        $map = [
            'SO' => 'SO',
            'SOLTERO' => 'SO',
            'SOLTERO(A)' => 'SO',
            'SOLTERA' => 'SO',
            'CA' => 'CA',
            'CASADO' => 'CA',
            'CASADO(A)' => 'CA',
            'CASADA' => 'CA',
            'VI' => 'VI',
            'VIUDO' => 'VI',
            'VIUDO(A)' => 'VI',
            'VIUDA' => 'VI',
            'DI' => 'DI',
            'DIVORCIADO' => 'DI',
            'DIVORCIADO(A)' => 'DI',
            'DIVORCIADA' => 'DI',
        ];

        return isset($map[$value]) ? $map[$value] : (strlen($value) <= 2 ? $value : $fallback);
    }

    private function localTipoDocumentoFromRuat($value): string
    {
        $value = strtoupper(trim((string)$value));

        if (in_array($value, ['CI', 'CEDULA DE IDENTIDAD', 'CÉDULA DE IDENTIDAD'], true)) {
            return 'CI';
        }

        if (in_array($value, ['CE', 'CEDULA EXTRANJERO', 'CÉDULA EXTRANJERO', 'CARNET DE EXTRANJERO'], true)) {
            return 'CE';
        }

        return $value;
    }

    private function nullableInt($value)
    {
        if ($value === null || trim((string)$value) === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string)$value);

        return $digits === '' ? null : (int)$digits;
    }

    private function localDateValue($value)
    {
        if ($value === null || trim((string)$value) === '') {
            return null;
        }

        $value = trim((string)$value);

        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'] as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date instanceof \DateTime && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }

        throw new BadRequestHttpException('El campo contri_fechanac debe tener formato dd/mm/yyyy o yyyy-mm-dd.');
    }

    private function localExtIdFromData(array $data, $ruatContribuyente = null)
    {
        if (isset($data['contri_tipo_documento_ruat']) && $data['contri_tipo_documento_ruat'] === 'CE') {
            return 12;
        }

        if (isset($data['ext_id']) && is_numeric($data['ext_id'])) {
            return (int)$data['ext_id'];
        }

        if ($ruatContribuyente !== null && isset($ruatContribuyente->tipoDocumento) && $ruatContribuyente->tipoDocumento === 'CE') {
            return 12;
        }

        if ($ruatContribuyente !== null && isset($ruatContribuyente->expedido)) {
            return $this->localExtIdFromRuatExpedido($ruatContribuyente->expedido);
        }

        throw new BadRequestHttpException('El campo ext_id es requerido para CI.');
    }

    private function ruatExpedidoFromData(array $data, string $tipoDocumento, bool $required): string
    {
        if ($tipoDocumento === 'CE') {
            return '';
        }

        if (isset($data['expedido']) && !is_array($data['expedido']) && trim((string)$data['expedido']) !== '') {
            return trim((string)$data['expedido']);
        }

        if (isset($data['ext_id']) && is_numeric($data['ext_id'])) {
            return (string)$this->ruatExpedidoFromLocalExtId((int)$data['ext_id']);
        }

        if ($required) {
            throw new BadRequestHttpException('El campo ext_id o expedido es requerido para documento CI.');
        }

        return '';
    }

    private function localExtIdFromRuatExpedido($expedido): int
    {
        $map = [
            '1' => 4,
            '2' => 5,
            '3' => 3,
            '4' => 6,
            '5' => 8,
            '6' => 11,
            '7' => 7,
            '8' => 9,
            '9' => 10,
            '12' => 12,
        ];

        $key = trim((string)$expedido);

        return isset($map[$key]) ? $map[$key] : 3;
    }

    private function ruatExpedidoFromLocalExtId(int $extId): int
    {
        $map = [
            4 => 1,
            5 => 2,
            3 => 3,
            6 => 4,
            8 => 5,
            11 => 6,
            7 => 7,
            9 => 8,
            10 => 9,
            12 => 12,
        ];

        return isset($map[$extId]) ? $map[$extId] : 3;
    }

    // -------------------------------------------------------------------------
    // Request/response utilities
    // -------------------------------------------------------------------------

    private function requestBodyParams()
    {
        $params = Yii::$app->request->post();
        $rawBody = Yii::$app->request->getRawBody();

        if (!empty($rawBody)) {
            $json = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new BadRequestHttpException('El cuerpo de la solicitud debe ser JSON válido.');
            }

            if (is_array($json)) {
                $params = array_merge($params, $json);
            }
        }

        return $params;
    }

    private function ruatApiResponse($response, array $extra = [])
    {
        if ($response === null) {
            return array_merge([
                'success' => false,
                'mensaje' => 'RUAT no devolvió una respuesta válida.',
                'continuarFlujo' => false,
            ], $extra);
        }

        $data = (array)$response;
        $success = $this->ruatContinuarFlujo($response);

        return array_merge(['success' => $success], $data, $extra);
    }

    private function ruatContinuarFlujo($response): bool
    {
        if ($response === null) {
            return false;
        }

        if (!isset($response->continuarFlujo)) {
            return true;
        }

        $value = $response->continuarFlujo;

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return in_array(strtoupper(trim($value)), ['TRUE', '1', 'SI', 'SÍ'], true);
        }

        return (bool)$value;
    }

    private function tipoDocumento(string $tipoDocumento): string
    {
        $tipoDocumento = strtoupper(trim($tipoDocumento));

        if (!in_array($tipoDocumento, ['CI', 'CE'], true)) {
            throw new BadRequestHttpException('El campo contri_tipo_documento_ruat debe ser CI o CE.');
        }

        return $tipoDocumento;
    }

    private function requiredString(array $params, string $key): string
    {
        if (!isset($params[$key]) || trim((string)$params[$key]) === '') {
            throw new BadRequestHttpException("El campo $key es requerido.");
        }

        return trim((string)$params[$key]);
    }

    private function firstRequiredString(array $params, array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($params[$key]) && trim((string)$params[$key]) !== '') {
                return trim((string)$params[$key]);
            }
        }

        throw new BadRequestHttpException('Uno de los campos es requerido: ' . implode(', ', $keys) . '.');
    }

    private function validValue(array $params, string $key, array $allowed): string
    {
        $value = $this->requiredString($params, $key);

        if (!in_array($value, $allowed, true)) {
            throw new BadRequestHttpException(
                "El campo $key debe ser uno de: " . implode(', ', $allowed) . "."
            );
        }

        return $value;
    }

    private function validFirstValue(array $params, array $keys, array $allowed): string
    {
        $value = $this->firstRequiredString($params, $keys);

        if (!in_array($value, $allowed, true)) {
            throw new BadRequestHttpException(
                'Uno de los campos ' . implode(', ', $keys) . ' debe ser uno de: ' . implode(', ', $allowed) . '.'
            );
        }

        return $value;
    }

    private function requiredInteger(array $params, array $keys, string $label): int
    {
        foreach ($keys as $key) {
            if (isset($params[$key]) && trim((string)$params[$key]) !== '') {
                if (!is_numeric($params[$key])) {
                    throw new BadRequestHttpException("El campo $label debe ser numérico.");
                }

                return (int)$params[$key];
            }
        }

        throw new BadRequestHttpException("El campo $label es requerido.");
    }

    private function optionalInteger(array $params, array $keys, string $label)
    {
        foreach ($keys as $key) {
            if (isset($params[$key]) && trim((string)$params[$key]) !== '') {
                if (!is_numeric($params[$key])) {
                    throw new BadRequestHttpException("El campo $label debe ser numérico.");
                }

                return (int)$params[$key];
            }
        }

        return null;
    }

    private function stringValue(array $params, string $key, $default = null)
    {
        if (!isset($params[$key]) || trim((string)$params[$key]) === '') {
            return $default;
        }

        return trim((string)$params[$key]);
    }

    private function requiredNumber(array $params, string $key)
    {
        if (!isset($params[$key])) {
            throw new BadRequestHttpException("El campo $key debe ser numérico.");
        }

        $value = str_replace(',', '.', trim((string)$params[$key]));

        if ($value === '' || !is_numeric($value)) {
            throw new BadRequestHttpException("El campo $key debe ser numérico.");
        }

        return $value + 0;
    }

    private function requiredMontoString(array $params, string $key): string
    {
        $number = $this->requiredNumber($params, $key);

        if ($number <= 0) {
            throw new BadRequestHttpException("El campo $key debe ser mayor a cero.");
        }

        return number_format($number, 2, ',', '');
    }

    private function usuarioIdFromRequest(array $data)
    {
        $value = $this->stringValue($data, 'usua_id', null);

        return $value !== null ? (int)$value : null;
    }

    private function normalizeRuatDateTime($value): string
    {
        $value = trim((string)$value);
        $formats = ['d/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y', 'Y-m-d H:i:s', 'Y-m-d'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);

            if ($date instanceof \DateTime) {
                return $date->format('Y-m-d H:i:s');
            }
        }

        return date('Y-m-d H:i:s');
    }

    private function validateMotivoAnulacion(string $motivo)
    {
        $allowed = [
            'A SOLICITUD DEL CONTRIBUYENTE',
            'NO SE EFECTUO EL PAGO EN EL DIA DEL REGISTRO',
            'REGISTRO DE DATOS INCONSISTENTES',
        ];

        if (!in_array($motivo, $allowed, true)) {
            throw new BadRequestHttpException(
                'El campo motivo debe ser uno de: ' . implode(', ', $allowed) . '.'
            );
        }
    }

    private function validateObservacionAnulacion(string $observacion)
    {
        if (strlen(trim($observacion)) <= 10) {
            throw new BadRequestHttpException('El campo observacion debe tener más de 10 caracteres.');
        }
    }

    private function validateRuatObservacion(string $observacion): array
    {
        $errors = [];
        $length = strlen(trim($observacion));

        if ($length === 0) {
            $errors[] = 'La observación es obligatoria.';
        }

        if ($length > 0 && $length < 10) {
            $errors[] = 'La observación debe tener al menos 10 caracteres.';
        }

        if ($length > 255) {
            $errors[] = 'La observación no debe superar 255 caracteres.';
        }

        return $errors;
    }

    private function ruatMensaje($response, string $default = 'RUAT rechazó la operación.'): string
    {
        if ($response === null || !isset($response->mensaje)) {
            return $default;
        }

        $messages = $this->flattenRuatMessages($response->mensaje);

        return !empty($messages) ? implode(' ', $messages) : $default;
    }

    private function flattenRuatMessages($value): array
    {
        $messages = [];

        if (is_string($value)) {
            $text = trim($value);
            return $text !== '' ? [$text] : [];
        }

        if (is_array($value) || is_object($value)) {
            foreach ((array)$value as $item) {
                $messages = array_merge($messages, $this->flattenRuatMessages($item));
            }
        }

        return $messages;
    }
}
