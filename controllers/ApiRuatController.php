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
                    'consulta-deudas-contribuyente' => ['POST', 'OPTIONS'],
                    'consulta-pago-tasa-otros-ingresos' => ['POST', 'OPTIONS'],
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

        $numeroDocumento = $this->requiredString($body, 'numeroDocumento');
        $tipoDocumento = $this->tipoDocumento($this->requiredString($body, 'tipoDocumento'));

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
        $tipoDocumento = $this->tipoDocumento($this->requiredString($body, 'tipoDocumento'));

        $contribuyente->contri_ci = $this->requiredString($body, 'numeroDocumento');
        $contribuyente->ext_id = $tipoDocumento === 'CE' ? 12 : 0;

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

        throw new BadRequestHttpException('Las credenciales de RUAT son requeridas.');
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
