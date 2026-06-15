<?php

namespace app\controllers;

use Yii;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

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
                    'login' => ['POST', 'OPTIONS'],
                    'busqueda-contribuyente' => ['POST', 'OPTIONS'],
                    'buscar-contribuyente-by-ci' => ['POST', 'OPTIONS'],
                    'consulta-deudas-contribuyente' => ['POST', 'OPTIONS'],
                    'consulta-pago-tasa-otros-ingresos' => ['POST', 'OPTIONS'],
                    'create-contribuyente' => ['POST', 'OPTIONS'],
                    'create-tasa' => ['POST', 'OPTIONS'],
                    'anular-tasa' => ['POST', 'OPTIONS'],
                ],
            ],
        ];
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

    private function allowedOrigins()
    {
        return [
            'http://localhost:4200',
            'http://localhost:5200',
            'http://localhost:5173',
            'http://127.0.0.1:4200',
            'http://127.0.0.1:5200',
            'http://127.0.0.1:5173',
            'http://181.177.143.185:4205'
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

    public function actionLogin()
    {
        $body = $this->requestBodyParams();
        $token = $this->loginFromRequest($body);

        return [
            'success' => true,
            'token' => $token,
        ];
    }

    public function actionBusquedaContribuyente()
    {
        $body = $this->requestBodyParams();
        $token = $this->tokenFromRequest($body);

        $numeroDocumento = $this->firstRequiredString($body, ['numeroDocumento', 'ci']);
        $tipoDocumento = $this->tipoDocumento($this->stringValue($body, 'tipoDocumento', 'CI'));

        $codigoContribuyente = Yii::$app->ruatServices->getContribuyentePorCi(
            $token,
            $numeroDocumento,
            $tipoDocumento
        );

        return [
            'success' => $codigoContribuyente !== null,
            'existe' => $codigoContribuyente !== null,
            'codigoContribuyente' => $codigoContribuyente,
        ];
    }

    public function actionBuscarContribuyenteByCi()
    {
        return $this->actionBusquedaContribuyente();
    }

    public function actionCreateContribuyente()
    {
        $body = $this->requestBodyParams();
        $token = $this->tokenFromRequest($body);
        $codigoUsuario = $this->firstRequiredString($body, ['codigoUsuario', 'usuario']);
        $contribuyente = $this->contribuyenteRegistroFromRequest($body);

        $codigoContribuyente = Yii::$app->ruatServices->registerContribuyente(
            $token,
            $codigoUsuario,
            $contribuyente
        );

        return [
            'success' => $codigoContribuyente !== null,
            'codigoContribuyente' => $codigoContribuyente,
        ];
    }

    public function actionCreateTasa()
    {
        $body = $this->requestBodyParams();
        $token = $this->tokenFromRequest($body);

        $codigoUsuario = $this->firstRequiredString($body, ['codigoUsuario', 'usuario']);
        $codigoContribuyente = $this->requiredString($body, 'codigoContribuyente');
        $codigoClasificador = $this->requiredString($body, 'codigoClasificador');
        $monto = $this->requiredNumber($body, 'monto');
        $observacion = $this->stringValue($body, 'observacion', '');

        $response = Yii::$app->ruatServices->createTasa(
            $token,
            $codigoUsuario,
            $codigoContribuyente,
            $codigoClasificador,
            $monto,
            $observacion
        );

        return [
            'success' => $this->ruatResponseWasSuccessful($response),
            'numeroTasa' => isset($response->numeroTasa) ? $response->numeroTasa : null,
            'data' => $response,
        ];
    }

    public function actionAnularTasa()
    {
        $body = $this->requestBodyParams();
        $token = $this->tokenFromRequest($body);

        $codigoUsuario = $this->firstRequiredString($body, ['codigoUsuario', 'usuario']);
        $numeroTasa = $this->firstRequiredString($body, ['numeroTasa', 'nroTasa']);
        $motivo = $this->requiredString($body, 'motivo');
        $observacion = $this->stringValue($body, 'observacion', '');

        $response = Yii::$app->ruatServices->anularTasa(
            $token,
            $codigoUsuario,
            $numeroTasa,
            $motivo,
            $observacion
        );

        return [
            'success' => $this->ruatResponseWasSuccessful($response),
            'data' => $response,
        ];
    }

    public function actionConsultaDeudasContribuyente()
    {
        $body = $this->requestBodyParams();
        $token = $this->tokenFromRequest($body);

        $contribuyente = $this->contribuyenteFromRequest($body);
        $response = Yii::$app->ruatServices->getTieneDeudaContribuyentePorNroDocumento($token, $contribuyente);

        return [
            'success' => $response !== null,
            'tieneDeudas' => isset($response->continuarFlujo) ? (bool)$response->continuarFlujo : null,
            'data' => $response,
        ];
    }

    public function actionConsultaPagoTasaOtrosIngresos()
    {
        $body = $this->requestBodyParams();
        $token = $this->tokenFromRequest($body);

        $numeroTasa = $this->requiredString($body, 'numeroTasa');
        $pagado = Yii::$app->ruatServices->buscarPagadoPorNroTasa($token, $numeroTasa);
        $pagoTasa = null;

        if ($pagado) {
            $pagoTasa = Yii::$app->ruatServices->buscarPagadoPorNroTasas($token, $numeroTasa);
        }

        return [
            'success' => true,
            'pagado' => (bool)$pagado,
            'pagoTasa' => $pagoTasa,
        ];
    }

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

    private function contribuyenteFromRequest(array $body)
    {
        $contribuyente = new \stdClass();
        $tipoDocumento = $this->tipoDocumento($this->stringValue($body, 'tipoDocumento', 'CI'));

        $contribuyente->contri_ci = $this->firstRequiredString($body, ['numeroDocumento', 'ci']);
        $contribuyente->ext_id = $tipoDocumento === 'CE' ? 12 : 0;

        return $contribuyente;
    }

    private function contribuyenteRegistroFromRequest(array $body)
    {
        $data = isset($body['contribuyente']) && is_array($body['contribuyente'])
            ? array_merge($body, $body['contribuyente'])
            : $body;

        $contribuyente = $this->contribuyenteFromRequest($data);
        $tipoDocumento = $this->tipoDocumento($this->stringValue($data, 'tipoDocumento', 'CI'));

        $contribuyente->ext_id = $tipoDocumento === 'CE'
            ? 12
            : (int)$this->stringValue($data, 'extId', $this->stringValue($data, 'ext_id', '0'));
        $contribuyente->contri_nombres = $this->firstRequiredString($data, ['nombres', 'nombre', 'contri_nombres']);
        $contribuyente->contri_paterno = $this->stringValue($data, 'primerApellido', $this->stringValue($data, 'paterno', $this->stringValue($data, 'contri_paterno', null)));
        $contribuyente->contri_materno = $this->stringValue($data, 'segundoApellido', $this->stringValue($data, 'materno', $this->stringValue($data, 'contri_materno', null)));
        $contribuyente->contri_estadocivil = $this->stringValue($data, 'estadoCivil', $this->stringValue($data, 'contri_estadocivil', 'SO'));
        $contribuyente->contri_fechanac = $this->stringValue($data, 'fechaNacimiento', $this->stringValue($data, 'contri_fechanac', null));
        $contribuyente->contri_sexo = $this->stringValue($data, 'genero', $this->stringValue($data, 'sexo', $this->stringValue($data, 'contri_sexo', 'M')));
        $contribuyente->contri_apellidocasada = $this->stringValue($data, 'apellidoEsposo', $this->stringValue($data, 'apellidoCasada', $this->stringValue($data, 'contri_apellidocasada', null)));

        return $contribuyente;
    }

    private function requestBodyParams()
    {
        $params = Yii::$app->request->post();
        $rawBody = Yii::$app->request->getRawBody();

        if (!empty($rawBody)) {
            $json = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new BadRequestHttpException('El cuerpo de la solicitud debe ser JSON valido.');
            }

            if (is_array($json)) {
                $params = array_merge($params, $json);
            }
        }

        return $params;
    }

    private function requiredString(array $params, $key)
    {
        if (!isset($params[$key]) || trim((string)$params[$key]) === '') {
            throw new BadRequestHttpException("El campo $key es requerido.");
        }

        return trim((string)$params[$key]);
    }

    private function firstRequiredString(array $params, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($params[$key]) && trim((string)$params[$key]) !== '') {
                return trim((string)$params[$key]);
            }
        }

        throw new BadRequestHttpException('Uno de los campos es requerido: ' . implode(', ', $keys) . '.');
    }

    private function stringValue(array $params, $key, $default = null)
    {
        if (!isset($params[$key]) || trim((string)$params[$key]) === '') {
            return $default;
        }

        return trim((string)$params[$key]);
    }

    private function requiredNumber(array $params, $key)
    {
        if (!isset($params[$key]) || !is_numeric($params[$key])) {
            throw new BadRequestHttpException("El campo $key debe ser numerico.");
        }

        return $params[$key] + 0;
    }

    private function ruatResponseWasSuccessful($response)
    {
        if ($response === null) {
            return false;
        }

        if (isset($response->continuarFlujo)) {
            return (bool)$response->continuarFlujo;
        }

        if (isset($response->numeroTasa) || isset($response->codigoContribuyente)) {
            return true;
        }

        return true;
    }

    private function tipoDocumento($tipoDocumento)
    {
        $tipoDocumento = strtoupper(trim($tipoDocumento));

        if (!in_array($tipoDocumento, ['CI', 'CE'], true)) {
            throw new BadRequestHttpException('El campo tipoDocumento debe ser CI o CE.');
        }

        return $tipoDocumento;
    }
}
