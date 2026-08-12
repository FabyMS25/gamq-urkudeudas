<?php
namespace app\controllers;

use Yii;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use app\models\Gestiones;
use app\models\Descargos;
use app\models\GeneradorDescargos;
use app\models\GraderiasSillas;
use app\models\Pagos;
use app\models\PagosEventuales;
use app\models\PagosInfracciones;
use app\models\RazonSociales;
use app\models\Usuario;
use app\components\MapWebSocketPublisher;

class ApiMapsController extends Controller
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
                    'Access-Control-Request-Method' => ['GET', 'POST', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'contexto-actual' => ['GET', 'OPTIONS'],
                    'zonas' => ['GET', 'OPTIONS'],
                    'tipo-armados' => ['GET', 'OPTIONS'],
                    'categorias' => ['GET', 'OPTIONS'],
                    'sindicatos' => ['GET', 'OPTIONS'],
                    'clasificadores-tasa' => ['GET', 'OPTIONS'],
                    'razones-sociales' => ['GET', 'OPTIONS'],
                    'actividades-economicas' => ['GET', 'OPTIONS'],
                    'contribuyentes' => ['GET', 'OPTIONS'],
                    'usuarios' => ['GET', 'OPTIONS'],
                    'usuario-por-cuenta' => ['GET', 'OPTIONS'],
                    'sitios-eventuales' => ['GET', 'OPTIONS'],
                    'graderias-sillas' => ['GET', 'OPTIONS'],
                    'actualizar-reserva-graderia-silla' => ['POST', 'OPTIONS'],
                    'pagos' => ['GET', 'OPTIONS'],
                    'pago-graderia-silla' => ['GET', 'OPTIONS'],
                    'pagos-eventuales' => ['GET', 'OPTIONS'],
                    'pagos-infracciones' => ['GET', 'OPTIONS'],
                    'descargos-sentajeros' => ['GET', 'OPTIONS'],
                    'sentajes' => ['GET', 'OPTIONS'],
                    'preliquidacion-sentaje' => ['POST', 'OPTIONS'],
                    'consulta-pago-sentaje' => ['POST', 'OPTIONS'],
                    'consulta-pago-sentajes' => ['POST', 'OPTIONS'],
                    'anular-tasa-sentaje' => ['POST', 'OPTIONS'],
                    'anular-tasa-pago' => ['POST', 'OPTIONS'],
                    'recibo-sentaje' => ['GET', 'OPTIONS'],
                    'recibo-sentaje-pdf' => ['GET', 'OPTIONS'],
                    'comprobante-pago' => ['GET', 'OPTIONS'],
                    'comprobante-pago-pdf' => ['GET', 'OPTIONS'],
                    'recibo-preliquidacion-pago' => ['GET', 'OPTIONS'],
                    'recibo-preliquidacion-pago-pdf' => ['GET', 'OPTIONS'],
                    'comprobante-pago-eventual' => ['GET', 'OPTIONS'],
                    'comprobante-pago-eventual-pdf' => ['GET', 'OPTIONS'],
                    'comprobante-pago-infraccion' => ['GET', 'OPTIONS'],
                    'comprobante-pago-infraccion-pdf' => ['GET', 'OPTIONS'],
                ],
            ],
        ];
    }

    private function allowedOrigins()
    {
        return [
            'http://localhost:4200',
            'http://127.0.0.1:4200',
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

        Yii::$app->response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
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

    public function actionContextoActual()
    {
        $gestion = (new Gestiones())->gestionVigente();

        if ($gestion === null) {
            return ['gestion' => null];
        }

        return [
            'gestion' => [
                'gest_id' => (int)$gestion->gest_id,
                'gest_nombre' => (int)$gestion->gest_nombre,
                'gest_ordenanza' => $gestion->gest_ordenanza,
            ],
        ];
    }

    public function actionZonas()
    {
        $gestion = (new Gestiones())->gestionVigente();
        $gestId = $gestion ? $gestion->gest_id : null;
        $query = (new \yii\db\Query())
            ->select([
                'zona_id',
                'gest_id',
                'zona_nombre',
                'zona_color',
                'zona_color_hexadecimal',
                'zona_descripcion',
                'zona_estado',
            ])
            ->from('zonas')
            ->where(['zona_estado' => 1])
            ->orderBy(['zona_nombre' => SORT_ASC]);
        if ($gestId !== null) {
            $query->andWhere(['gest_id' => $gestId]);
        }
        return $query->all();
    }

    public function actionTipoArmados()
    {
        $gestion = (new Gestiones())->gestionVigente();
        $gestId = $gestion ? $gestion->gest_id : null;

        $query = (new \yii\db\Query())
            ->select([
                'ta.tip_arm_id',
                'ta.zona_id',
                'ta.gest_id',
                'ta.tip_arm_descricpion',
                'ta.tip_arm_patente',
                'ta.tip_arm_tasa_aseo',
                'ta.tip_arm_unidad_medida',
                'ta.tip_arm_estado',
                'z.zona_nombre',
                'z.zona_color',
                'z.zona_color_hexadecimal',
            ])
            ->from(['ta' => 'tipo_armados'])
            ->leftJoin(['z' => 'zonas'], 'z.zona_id = ta.zona_id')
            ->where(['ta.tip_arm_estado' => 1])
            ->orderBy(['z.zona_nombre' => SORT_ASC, 'ta.tip_arm_descricpion' => SORT_ASC]);

        if ($gestId !== null) {
            $query->andWhere(['ta.gest_id' => $gestId]);
        }

        $zonaId = Yii::$app->request->get('zona_id');
        if ($zonaId !== null && $zonaId !== '') {
            $query->andWhere(['ta.zona_id' => $zonaId]);
        }

        return $query->all();
    }

    public function actionCategorias()
    {
        return (new \yii\db\Query())
            ->select([
                'categ_id',
                'categ_nombre',
                'categ_codigo',
                'categ_estado',
            ])
            ->from('categorias')
            ->where(['categ_estado' => 1])
            ->orderBy(['categ_nombre' => SORT_ASC])
            ->all();
    }
    public function actionSindicatos()
    {
        return (new \yii\db\Query())
            ->select([
                'sindi_id',
                'sindi_nombre',
                'sindi_descripcion',
                'sindi_estado',
            ])
            ->from('sindicatos')
            ->where(['sindi_estado' => 1])
            ->orderBy(['sindi_nombre' => SORT_ASC])
            ->all();
    }
    public function actionClasificadoresTasa()
    {
        $grupo = trim((string)Yii::$app->request->get('grupo', ''));
        $catalogo = $this->clasificadoresCatalogo();
        $defaultKey = $this->clasificadorSentajePorDefectoKey();

        $resultado = [];

        foreach ($catalogo as $key => $config) {
            if (!is_array($config)) {
                continue;
            }

            $configGrupo = isset($config['grupo'])
                ? trim((string)$config['grupo'])
                : '';

            if ($grupo !== '' && $configGrupo !== $grupo) {
                continue;
            }

            $payload = $this->clasificadorPayload($key);
            if ($payload === null) {
                continue;
            }

            $payload['is_default'] =
                $defaultKey !== null && $key === $defaultKey;

            $resultado[] = $payload;
        }

        return $resultado;
    }

    public function actionRazonesSociales()
    {
        $schema = Yii::$app->db->schema->getTableSchema('razon_sociales');
        $hasClasificador = $schema !== null && isset($schema->columns['razon_clasificador']);

        $query = (new \yii\db\Query())
            ->select([
                'razon_id',
                'razon_nombre',
                'razon_estado',
                $hasClasificador
                    ? 'razon_clasificador'
                    : new \yii\db\Expression('NULL AS razon_clasificador'),
            ])
            ->from('razon_sociales')
            ->where(['razon_estado' => 1])
            ->orderBy(['razon_nombre' => SORT_ASC]);

        $id = Yii::$app->request->get('id');
        if ($id !== null && $id !== '') {
            $row = $query->andWhere(['razon_id' => $id])->one();
            return $row === false ? null : $this->razonSocialPayload($row);
        }

        return array_map([$this, 'razonSocialPayload'], $query->all());
    }

    public function actionActividadesEconomicas()
    {
        $query = (new \yii\db\Query())
            ->select([
                'a.activi_id',
                'a.categ_id',
                'a.activi_descripcion',
                'a.activi_largo_mts',
                'a.activi_ancho_mts',
                'a.activi_superficie',
                'a.activi_costo_patente',
                'a.activi_costo_sentaje_dia',
                'a.activi_costo_aseo_por_dia',
                'a.activi_costo_aseo_por_sitio',
                'a.activi_cobro_por_dia',
                'a.activi_estado',
                'c.categ_nombre',
                'c.categ_codigo',
            ])
            ->from(['a' => 'actividades_economicas'])
            ->leftJoin(['c' => 'categorias'], 'c.categ_id = a.categ_id')
            ->where(['a.activi_estado' => 1])
            ->orderBy(['c.categ_nombre' => SORT_ASC, 'a.activi_descripcion' => SORT_ASC]);

        $id = Yii::$app->request->get('id');
        if ($id !== null && $id !== '') {
            return $query->andWhere(['a.activi_id' => $id])->one();
        }

        $categoriaId = Yii::$app->request->get('categ_id');
        if ($categoriaId !== null && $categoriaId !== '') {
            $query->andWhere(['a.categ_id' => $categoriaId]);
        }
        return $query->all();
    }

    public function actionContribuyentes()
    {
        $query = (new \yii\db\Query())
            ->select('c.*')
            ->from(['c' => 'contribuyentes'])
            ->where(['c.contri_estado' => 1])
            ->orderBy([
                'c.contri_nombres' => SORT_ASC,
                'c.contri_paterno' => SORT_ASC,
                'c.contri_materno' => SORT_ASC,
                'c.contri_id' => SORT_ASC,
            ]);

        $this->applyContribuyenteFilters($query, 'c');

        return $this->paginatedList($query);
    }

    public function actionUsuarios()
    {
        $query = (new \yii\db\Query())
            ->select([
                'usua_id',
                'usua_nombres',
                'usua_apellidos',
                'usua_ci',
                'usua_cuenta',
                'usua_rol',
                'usua_estado',
            ])
            ->from('usuario')
            ->where(['usua_estado' => 1])
            ->orderBy([
                'usua_nombres' => SORT_ASC,
                'usua_apellidos' => SORT_ASC,
            ]);

        $id = Yii::$app->request->get('id');
        if ($id !== null && $id !== '') {
            return $query->andWhere(['usua_id' => $id])->one();
        }

        return $query->all();
    }

    public function actionUsuarioPorCuenta()
    {
        $cuenta = trim((string)Yii::$app->request->get('cuenta', ''));

        if ($cuenta === '') {
            throw new BadRequestHttpException('Debe enviar el parametro cuenta.');
        }

        $usuario = Usuario::findOne(['usua_cuenta' => $cuenta, 'usua_estado' => 1]);

        if ($usuario === null) {
            return [
                'success' => false,
                'mensaje' => 'El usuario no existe o no esta activo en este sistema. No puede registrar operaciones.',
                'usuario' => null,
            ];
        }

        return [
            'success' => true,
            'usuario' => $this->usuarioModelPayload($usuario),
        ];
    }

    public function actionSitiosEventuales()
    {
        $query = (new \yii\db\Query())
            ->select([
                'sitios_id',
                'sitios_codigo',
                'sitios_descripcion',
                'sitios_numero_sitio',
                'sitios_vendido',
                'sitios_es_alasita',
                'sitios_estado',
            ])
            ->from('sitios_eventuales')
            ->where(['sitios_estado' => 1])
            ->orderBy(['sitios_codigo' => SORT_ASC, 'sitios_numero_sitio' => SORT_ASC]);
        $id = Yii::$app->request->get('id');
        if ($id !== null && $id !== '') {
            return $query->andWhere(['sitios_id' => $id])->one();
        }
        return $query->all();
    }

    public function actionGraderiasSillas()
    {
        $gestion = (new Gestiones())->gestionVigente();
        $gestId = $gestion ? $gestion->gest_id : null;

        $query = (new \yii\db\Query())
            ->select([
                'gs.grad_id',
                'gs.zona_id',
                'gs.gest_id',
                'gs.grad_codigo',
                'gs.grad_propietario',
                'gs.grad_codigo_catastral',
                'gs.grad_direccion',
                'gs.grad_acera',
                'gs.grad_tipo_armado',
                'gs.grad_tipo_sitio',
                'gs.grad_resto',
                'gs.grad_longitud',
                'gs.grad_vendido',
                'gs.grad_reservado',
                'gs.grad_estado',
                'z.zona_nombre',
                'z.zona_color',
                'z.zona_color_hexadecimal',
            ])
            ->from(['gs' => 'graderias_sillas'])
            ->leftJoin(['z' => 'zonas'], 'z.zona_id = gs.zona_id')
            ->where(['gs.grad_estado' => 1])
            ->orderBy(['z.zona_nombre' => SORT_ASC, 'gs.grad_codigo' => SORT_ASC]);
        if ($gestId !== null) {
            $query->andWhere(['gs.gest_id' => $gestId]);
        }
        return $query->all();
    }

    public function actionActualizarReservaGraderiaSilla($id = null)
    {
        $this->ensureWriteMethod(['POST']);
        $body = $this->requestBodyParams();
        $reservado = $this->parseReservadoValue($body);

        return $this->updateReservaGraderiaSilla($id, $reservado, $reservado ? 'reservar' : 'unreservar');
    }

public function actionPagos()
{
    $query = $this->pagoQuery();
    $this->applyPagoFilters($query, 'p');
    return $this->paginatedList($query, [$this, 'pagoPayload']);
}

public function actionPagoGraderiaSilla($grad_id = null)
{
    $gradId = $this->resolvePositiveInteger($grad_id, 'grad_id');
    $row = $this->pagoQuery()
        ->andWhere(['p.grad_id' => $gradId])
        ->one();
    if ($row === false) {
        throw new NotFoundHttpException('No existe un pago activo para la graderia o silla indicada.');
    }
    return $this->pagoPayload($row);
}

private function pagoQuery()
{
    return (new \yii\db\Query())
        ->select([
            'p.*',
            'c.ext_id', 'c.sindi_id', 'c.contri_nombres', 'c.contri_paterno',
            'c.contri_materno', 'c.contri_apellidocasada', 'c.contri_ci',
            'c.contri_direccion', 'c.contri_telefono', 'c.contri_nit',
            'c.contri_fecharegistro', 'c.contri_fechanac', 'c.contri_sexo',
            'c.contri_estadocivil', 'c.contri_estado', 'c.contri_codigo_ruat',
            'c.contri_tipo_contribuyente_ruat', 'c.contri_tipo_documento_ruat',
            'c.contri_estado_ruat', 'c.contri_ruat_sync_at',
            'u.usua_nombres', 'u.usua_apellidos', 'u.usua_ci',
            'u.usua_cuenta', 'u.usua_rol', 'u.usua_estado',
            'gs.zona_id', 'gs.gest_id', 'gs.grad_codigo', 'gs.grad_propietario',
            'gs.grad_codigo_catastral', 'gs.grad_direccion', 'gs.grad_acera',
            'gs.grad_tipo_armado', 'gs.grad_tipo_sitio', 'gs.grad_resto',
            'gs.grad_longitud', 'gs.grad_vendido', 'gs.grad_reservado', 'gs.grad_estado',
            'z.zona_nombre', 'z.zona_color', 'z.zona_color_hexadecimal',
            'ta.tip_arm_descricpion', 'ta.tip_arm_patente', 'ta.tip_arm_tasa_aseo',
            'ta.tip_arm_unidad_medida', 'ta.tip_arm_estado',
        ])
        ->from(['p' => 'pagos'])
        ->leftJoin(['c' => 'contribuyentes'], 'c.contri_id = p.contri_id')
        ->leftJoin(['gs' => 'graderias_sillas'], 'gs.grad_id = p.grad_id')
        ->leftJoin(['z' => 'zonas'], 'z.zona_id = gs.zona_id')
        ->leftJoin(['ta' => 'tipo_armados'], 'ta.tip_arm_id = p.tip_arm_id')
        ->leftJoin(['u' => 'usuario'], 'u.usua_id = p.usua_id')
        ->where(['p.pago_estado' => 1])
        ->orderBy([
            'p.pago_fecha_hora_preliquidacion' => SORT_DESC,
            'p.pago_id' => SORT_DESC,
        ]);
}
public function actionPagosEventuales()
{
    $query = (new \yii\db\Query())
        ->select([
            'pe.*',
            'c.ext_id', 'c.sindi_id', 'c.contri_nombres', 'c.contri_paterno',
            'c.contri_materno', 'c.contri_apellidocasada', 'c.contri_ci',
            'c.contri_direccion', 'c.contri_telefono', 'c.contri_nit',
            'c.contri_fecharegistro', 'c.contri_fechanac', 'c.contri_sexo',
            'c.contri_estadocivil', 'c.contri_estado', 'c.contri_codigo_ruat',
            'c.contri_tipo_contribuyente_ruat', 'c.contri_tipo_documento_ruat',
            'c.contri_estado_ruat', 'c.contri_ruat_sync_at',
            'u.usua_nombres', 'u.usua_apellidos', 'u.usua_ci',
            'u.usua_cuenta', 'u.usua_rol', 'u.usua_estado',
            's.sitios_codigo', 's.sitios_descripcion', 's.sitios_numero_sitio',
            's.sitios_vendido', 's.sitios_es_alasita', 's.sitios_estado',
            'a.categ_id', 'a.activi_descripcion', 'a.activi_largo_mts',
            'a.activi_ancho_mts', 'a.activi_superficie', 'a.activi_costo_patente',
            'a.activi_costo_sentaje_dia', 'a.activi_costo_aseo_por_dia',
            'a.activi_costo_aseo_por_sitio', 'a.activi_cobro_por_dia', 'a.activi_estado',
            'cat.categ_nombre', 'cat.categ_codigo', 'cat.categ_estado',
        ])
        ->from(['pe' => 'pagos_eventuales'])
        ->leftJoin(['c' => 'contribuyentes'], 'c.contri_id = pe.contri_id')
        ->leftJoin(['s' => 'sitios_eventuales'], 's.sitios_id = pe.sitios_id')
        ->leftJoin(['a' => 'actividades_economicas'], 'a.activi_id = pe.activi_id')
        ->leftJoin(['cat' => 'categorias'], 'cat.categ_id = a.categ_id')
        ->leftJoin(['u' => 'usuario'], 'u.usua_id = pe.usua_id')
        ->where(['pe.eventual_estado' => 1])
        ->orderBy([
            'pe.eventual_fecha_hora_liquidacion' => SORT_DESC,
            'pe.eventual_id' => SORT_DESC,
        ]);

    $this->applyPagoEventualFilters($query, 'pe');

    return $this->paginatedList($query, [$this, 'pagoEventualPayload']);
}
public function actionPagosInfracciones()
{
    $query = (new \yii\db\Query())
        ->select([
            'pi.*',
            'c.ext_id', 'c.sindi_id', 'c.contri_nombres', 'c.contri_paterno',
            'c.contri_materno', 'c.contri_apellidocasada', 'c.contri_ci',
            'c.contri_direccion', 'c.contri_telefono', 'c.contri_nit',
            'c.contri_fecharegistro', 'c.contri_fechanac', 'c.contri_sexo',
            'c.contri_estadocivil', 'c.contri_estado', 'c.contri_codigo_ruat',
            'c.contri_tipo_contribuyente_ruat', 'c.contri_tipo_documento_ruat',
            'c.contri_estado_ruat', 'c.contri_ruat_sync_at',
            'u.usua_nombres', 'u.usua_apellidos', 'u.usua_ci',
            'u.usua_cuenta', 'u.usua_rol', 'u.usua_estado',
        ])
        ->from(['pi' => 'pagos_infracciones'])
        ->leftJoin(['c' => 'contribuyentes'], 'c.contri_id = pi.contri_id')
        ->leftJoin(['u' => 'usuario'], 'u.usua_id = pi.usua_id')
        ->orderBy([
            'pi.created_at' => SORT_DESC,
            'pi.infraccion_id' => SORT_DESC,
        ]);

    $this->applyPagoInfraccionFilters($query, 'pi');

    return $this->paginatedList($query, [$this, 'pagoInfraccionPayload']);
}

    public function actionDescargosSentajeros()
    {
        $schema = Yii::$app->db->schema->getTableSchema('razon_sociales');
        $hasClasificador = $schema !== null && isset($schema->columns['razon_clasificador']);

        $query = (new \yii\db\Query())
            ->select([
                'd.desc_id',
                'd.usua_id',
                'd.razon_id',
                'd.desc_nro_comprobante',
                'd.desc_responsable',
                'd.desc_fecha_hora',
                'd.desc_anulado',
                'd.desc_anulado_justificacion',
                'd.desc_anulado_fecha_hora',
                'd.desc_impreso',
                'd.desc_estado',
                'd.desc_ci',
                'd.desc_ext',
                'rs.razon_nombre',
                'rs.razon_estado',
                $hasClasificador
                    ? 'rs.razon_clasificador'
                    : new \yii\db\Expression('NULL AS razon_clasificador'),
                'u.usua_nombres',
                'u.usua_apellidos',
                'u.usua_ci',
                'u.usua_cuenta',
                'u.usua_rol',
                'u.usua_estado',
            ])
            ->from(['d' => 'descargos'])
            ->innerJoin(['rs' => 'razon_sociales'], 'rs.razon_id = d.razon_id')
            ->leftJoin(['u' => 'usuario'], 'u.usua_id = d.usua_id')
            ->where(['d.desc_estado' => 1])
            ->orderBy([
                'd.desc_fecha_hora' => SORT_DESC,
                'd.desc_id' => SORT_DESC,
            ]);

        return $this->paginatedList($query, [$this, 'descargoSentajeroPayload']);
    }
    public function actionSentajes()
    {
        $razonSchema = Yii::$app->db->schema->getTableSchema('razon_sociales');
        $detalleSchema = Yii::$app->db->schema->getTableSchema('detalle_descargos');

        $hasRazonClasificador = $razonSchema !== null
            && isset($razonSchema->columns['razon_clasificador']);
        $hasDetalleTasa = $detalleSchema !== null
            && isset($detalleSchema->columns['detalle_tasa']);
        $hasNroComprobante = $detalleSchema !== null
            && isset($detalleSchema->columns['nro_comprobante']);
        $hasDetalleObservacion = $detalleSchema !== null
            && isset($detalleSchema->columns['detalle_observacion']);
        $hasDetalleFeria = $detalleSchema !== null
            && isset($detalleSchema->columns['detalle_feria']);
        $hasDetalleEstadoAnulado = $detalleSchema !== null
            && isset($detalleSchema->columns['detalle_estado_anulado']);
        $hasCodigoClasificador = $detalleSchema !== null
            && isset($detalleSchema->columns['codigo_clasificador']);

        $query = (new \yii\db\Query())
            ->select([
                'dd.detalle_id',
                'dd.desc_id',
                'dd.detalle_precio',
                'dd.detalle_nro_inicio',
                'dd.detalle_nro_limite',
                'dd.detalle_cantidad',
                'dd.detalle_cantidad_anulado',
                'dd.detalle_fecha_entrega',
                'dd.detalle_importe_bs',
                'dd.detalle_estado',
                'dd.detalle_estado_pago',
                $hasDetalleEstadoAnulado
                    ? 'dd.detalle_estado_anulado'
                    : new \yii\db\Expression('0 AS detalle_estado_anulado'),
                $hasDetalleTasa
                    ? 'dd.detalle_tasa'
                    : new \yii\db\Expression('NULL AS detalle_tasa'),
                $hasNroComprobante
                    ? 'dd.nro_comprobante'
                    : new \yii\db\Expression('NULL AS nro_comprobante'),
                $hasDetalleObservacion
                    ? 'dd.detalle_observacion'
                    : new \yii\db\Expression('NULL AS detalle_observacion'),
                $hasDetalleFeria
                    ? 'dd.detalle_feria'
                    : new \yii\db\Expression('NULL AS detalle_feria'),
                $hasCodigoClasificador
                    ? 'dd.codigo_clasificador'
                    : new \yii\db\Expression('NULL AS codigo_clasificador'),
                'd.usua_id',
                'd.razon_id',
                'd.desc_nro_comprobante',
                'd.desc_responsable',
                'd.desc_fecha_hora',
                'd.desc_anulado',
                'd.desc_anulado_justificacion',
                'd.desc_anulado_fecha_hora',
                'd.desc_impreso',
                'd.desc_estado',
                'd.desc_ci',
                'd.desc_ext',
                'rs.razon_nombre',
                'rs.razon_estado',
                $hasRazonClasificador
                    ? 'rs.razon_clasificador'
                    : new \yii\db\Expression('NULL AS razon_clasificador'),
                'u.usua_nombres',
                'u.usua_apellidos',
                'u.usua_ci',
                'u.usua_cuenta',
                'u.usua_rol',
                'u.usua_estado',
            ])
            ->from(['dd' => 'detalle_descargos'])
            ->innerJoin(['d' => 'descargos'], 'd.desc_id = dd.desc_id')
            ->innerJoin(['rs' => 'razon_sociales'], 'rs.razon_id = d.razon_id')
            ->leftJoin(['u' => 'usuario'], 'u.usua_id = d.usua_id')
            ->where([
                'd.desc_estado' => 1,
                'dd.detalle_estado' => 1,
            ])
            ->orderBy([
                'dd.detalle_fecha_entrega' => SORT_DESC,
                'dd.detalle_id' => SORT_DESC,
            ]);

        $this->applySentajeFilters($query, $hasDetalleTasa);

        return $this->paginatedList($query, [$this, 'sentajePayload']);
    }
    public function actionPreliquidacionSentaje()
    {
        $this->ensureWriteMethod(['POST']);

        $body = $this->requestBodyParams();
        $data = isset($body['preliquidacion']) && is_array($body['preliquidacion'])
            ? array_merge($body, $body['preliquidacion'])
            : $body;

        $descId = $this->requiredPositiveIntegerFrom($data, ['desc_id'], 'desc_id');
        $descargo = Descargos::findOne($descId);

        if ($descargo === null || (int)$descargo->desc_estado !== 1) {
            throw new NotFoundHttpException(
                'El descargo seleccionado no existe o no esta activo.'
            );
        }

        if ((int)$descargo->desc_anulado === 1) {
            throw new BadRequestHttpException(
                'No se puede preliquidar un descargo anulado.'
            );
        }

        $usuarioOperador = $this->usuarioLocalFromCuenta(
            $data,
            'la preliquidacion del descargo'
        );

        $razon = RazonSociales::find()
            ->where(['razon_id' => $descargo->razon_id])
            ->andWhere(['razon_estado' => 1])
            ->one();

        if ($razon === null) {
            throw new BadRequestHttpException(
                'La razon social del descargo no existe o no esta activa.'
            );
        }

        $precio = $this->requiredNumberFrom(
            $data,
            ['detalle_precio', 'precio'],
            'precio'
        );
        $nroInicio = $this->requiredPositiveIntegerFrom(
            $data,
            ['detalle_nro_inicio', 'nro_inicio'],
            'nro_inicio'
        );
        $nroLimite = $this->requiredPositiveIntegerFrom(
            $data,
            ['detalle_nro_limite', 'nro_limite'],
            'nro_limite'
        );
        $cantidadAnulado = $this->optionalNonNegativeInteger(
            $data,
            ['detalle_cantidad_anulado', 'cantidad_anulado'],
            0
        );

        if ($nroLimite < $nroInicio) {
            throw new BadRequestHttpException(
                'El nro limite no puede ser menor al nro inicio.'
            );
        }

        $cantidad = ($nroLimite - $nroInicio) + 1;
        if ($cantidadAnulado > $cantidad) {
            throw new BadRequestHttpException(
                'La cantidad de anulados no puede ser mayor a la cantidad.'
            );
        }

        $cantidadFinal = $cantidad - $cantidadAnulado;
        $importe = round($precio * $cantidadFinal, 2);

        if ($importe <= 0) {
            throw new BadRequestHttpException(
                'El importe de la preliquidacion debe ser mayor a cero.'
            );
        }

        $documento = [
            'numero' => trim((string)$descargo->desc_ci),
            'ext_id' => (int)$descargo->desc_ext,
            'tipo_documento' => (int)$descargo->desc_ext === 12 ? 'CE' : 'CI',
            'expedido' => '',
        ];

        if ($documento['numero'] === '') {
            throw new BadRequestHttpException(
                'El descargo no tiene documento del sentajero.'
            );
        }

        $clasificadorKey = $this->clasificadorSentajeFromRazon($razon, $data);
        $codigoClasificador = $this->codigoClasificadorFromKey($clasificadorKey);

        $detalleFeria = $this->optionalStringFrom(
            $data,
            ['detalle_feria', 'feria', 'actividad'],
            $razon->razon_nombre
        );

        $observacion = $this->sentajeObservacion(
            $clasificadorKey,
            $detalleFeria
        );
        $detalleItems = $this->sentajeDetalleItemsFromRequest($data, $detalleFeria);
        $importe = 0.0;
        foreach ($detalleItems as $detalleItem) {
            $importe += (float)$detalleItem['detalle_importe_bs'];
        }

        $token = Yii::$app->ruatServices->loginConfigured();
        if (!$token) {
            throw new BadRequestHttpException(
                'No se pudo iniciar sesion en RUAT.'
            );
        }

        $codigoContribuyente = Yii::$app->ruatServices->getContribuyentePorCi(
            $token,
            $documento['numero'],
            $documento['tipo_documento'],
            'QUI',
            $documento['expedido']
        );

        if (!$codigoContribuyente) {
            throw new BadRequestHttpException(
                'El sentajero seleccionado no se encuentra registrado en RUAT. ' .
                'Debe registrar contribuyente primero.'
            );
        }

        $codigoUsuario = trim((string)$usuarioOperador->usua_cuenta);

        try {
            $response = Yii::$app->ruatServices->createTasa(
                $token,
                $codigoUsuario,
                $codigoContribuyente,
                $codigoClasificador,
                $importe,
                $observacion
            );
        } catch (\Throwable $exception) {
            Yii::error([
                'mensaje' => 'Error tecnico al registrar la tasa de sentaje en RUAT.',
                'desc_id' => $descId,
                'clasificador_key' => $clasificadorKey,
                'codigo_clasificador' => $codigoClasificador,
                'importe' => $importe,
                'error' => $exception->getMessage(),
            ], __METHOD__);

            throw new BadRequestHttpException(
                'No se pudo completar la comunicacion con RUAT.'
            );
        }

        $numeroTasa = $response && isset($response->numeroTasa)
            ? trim((string)$response->numeroTasa)
            : '';

        if (!$this->ruatContinuarFlujo($response) || $numeroTasa === '') {
            return [
                'success' => false,
                'mensaje' => $this->ruatMensaje(
                    $response,
                    'RUAT no registro la tasa de sentaje.'
                ),
                'codigoUsuario' => $codigoUsuario,
                'codigoContribuyente' => $codigoContribuyente,
                'clasificadorKey' => $clasificadorKey,
                'codigoClasificador' => $codigoClasificador,
                'ruat' => $response,
                'descargo' => $descargo->attributes,
                'detalle' => null,
            ];
        }

        $detallesGuardados = [];
        $ultimoDetalle = null;
        foreach ($detalleItems as $detalleItem) {
            $detalle = new GeneradorDescargos();
            $detalle->desc_id = $descId;
            $detalle->detalle_precio = $detalleItem['detalle_precio'];
            $detalle->detalle_nro_inicio = $detalleItem['detalle_nro_inicio'];
            $detalle->detalle_nro_limite = $detalleItem['detalle_nro_limite'];
            $detalle->detalle_cantidad = $detalleItem['detalle_cantidad'];
            $detalle->detalle_cantidad_anulado = $detalleItem['detalle_cantidad_anulado'];
            $detalle->detalle_fecha_entrega = date('Y-m-d H:i:s');
            $detalle->detalle_importe_bs = $detalleItem['detalle_importe_bs'];
            $detalle->detalle_estado = 1;
            $detalle->detalle_estado_pago = 0;

            if ($detalle->hasAttribute('detalle_estado_anulado')) {
                $detalle->setAttribute('detalle_estado_anulado', 0);
            }
            if ($detalle->hasAttribute('usua_id')) {
                $detalle->setAttribute('usua_id', (int)$usuarioOperador->usua_id);
            }
            if ($detalle->hasAttribute('detalle_tasa')) {
                $detalle->setAttribute('detalle_tasa', $numeroTasa);
            }
            if ($detalle->hasAttribute('nro_comprobante')) {
                $detalle->setAttribute('nro_comprobante', null);
            }
            if ($detalle->hasAttribute('detalle_observacion')) {
                $detalle->setAttribute('detalle_observacion', $observacion);
            }
            if ($detalle->hasAttribute('detalle_feria')) {
                $detalle->setAttribute('detalle_feria', $detalleItem['detalle_feria']);
            }
            if ($detalle->hasAttribute('codigo_clasificador')) {
                $detalle->setAttribute('codigo_clasificador', $codigoClasificador);
            }

            if (!$detalle->save()) {
                Yii::error([
                    'mensaje' => 'RUAT creo la tasa, pero no se pudo guardar localmente.',
                    'numero_tasa' => $numeroTasa,
                    'desc_id' => $descId,
                    'clasificador_key' => $clasificadorKey,
                    'codigo_clasificador' => $codigoClasificador,
                    'errores' => $detalle->getErrors(),
                    'detalle_item' => $detalleItem,
                ], __METHOD__);
                throw new BadRequestHttpException(
                    'RUAT creo la tasa ' . $numeroTasa .
                    ', pero no se pudo guardar la preliquidacion local: ' .
                    json_encode($detalle->getErrors())
                );
            }

            $detallesGuardados[] = $detalle->attributes;
            $ultimoDetalle = $detalle;
        }
        return [
            'success' => true,
            'mensaje' => 'Se creo la tasa y se registro la preliquidacion correctamente.',
            'numeroTasa' => $numeroTasa,
            'clasificadorKey' => $clasificadorKey,
            'codigoClasificador' => $codigoClasificador,
            'codigoUsuario' => $codigoUsuario,
            'codigoContribuyente' => $codigoContribuyente,
            'descargo' => $descargo->attributes,
            'detalle' => $ultimoDetalle ? $ultimoDetalle->attributes : null,
            'detalles' => $detallesGuardados,
            'usuario' => $this->usuarioModelPayload($usuarioOperador),
            'reciboUrl' => Url::to([
                'api-maps/recibo-sentaje-pdf',
                'id_detalle' => $ultimoDetalle ? $ultimoDetalle->detalle_id : null,
            ], true),
            'ruat' => $response,
        ];
    }

    public function actionReciboSentaje($id_detalle = null)
    {
        $detalleId = $this->resolvePositiveInteger($id_detalle, 'id_detalle');
        return [
            'success' => true,
            'url' => Url::to(['api-maps/recibo-sentaje-pdf', 'id_detalle' => $detalleId], true),
        ];
    }

    public function actionReciboSentajePdf($id_detalle = null)
    {
        $detalleId = $this->resolvePositiveInteger($id_detalle, 'id_detalle');
        $url = $this->generarReciboSentajePdf($detalleId);
        return $this->sendPdfFile($url, 'recibo_sentaje_' . $detalleId . '.pdf');
    }

    public function actionConsultaPagoSentaje()
    {
        $this->ensureWriteMethod(['POST']);
        $body = $this->requestBodyParams();
        $data = isset($body['sentaje']) && is_array($body['sentaje'])
            ? array_merge($body, $body['sentaje'])
            : $body;
        $token = $this->requiredStringFrom($data, ['token']);
        $codigoAlcaldia = $this->optionalStringFrom($data, ['codigoAlcaldia', 'codigo_alcaldia'], 'QUI');
        $numeroTasa = $this->requiredStringFrom($data, ['numeroTasa', 'numero_tasa', 'detalle_tasa']);
        return $this->consultaPagoSentajeResponse($token, $codigoAlcaldia, $numeroTasa);
    }

    public function actionConsultaPagoSentajes()
    {
        $this->ensureWriteMethod(['POST']);
        $body = $this->requestBodyParams();
        $data = isset($body['sentajes']) && is_array($body['sentajes'])
            ? array_merge($body, $body['sentajes'])
            : $body;
        $token = $this->requiredStringFrom($data, ['token']);
        $codigoAlcaldia = $this->optionalStringFrom($data, ['codigoAlcaldia', 'codigo_alcaldia'], 'QUI');
        $numerosTasa = $this->numeroTasasFromRequest($data);
        $resultados = [];
        foreach ($numerosTasa as $numeroTasa) {
            try {
                $resultados[] = $this->consultaPagoSentajeResponse($token, $codigoAlcaldia, $numeroTasa);
            } catch (\Throwable $e) {
                $resultados[] = [
                    'success' => false,
                    'consultaExitosa' => false,
                    'continuarFlujo' => false,
                    'numeroTasa' => $numeroTasa,
                    'pagado' => false,
                    'mensaje' => $e->getMessage(),
                ];
            }
        }
        $pagadas = 0;
        $sinPago = 0;
        $fallidas = 0;
        foreach ($resultados as $resultado) {
            if (!empty($resultado['pagado'])) {
                $pagadas++;
            }
            if (empty($resultado['success'])) {
                $fallidas++;
            } elseif (empty($resultado['pagado'])) {
                $sinPago++;
            }
        }
        return [
            'success' => $fallidas === 0,
            'consultadas' => count($resultados),
            'pagadas' => $pagadas,
            'sinPago' => $sinPago,
            'fallidas' => $fallidas,
            'resultados' => $resultados,
        ];
    }

    public function actionAnularTasaSentaje()
    {
        $this->ensureWriteMethod(['POST']);
        $body = $this->requestBodyParams();
        $data = isset($body['tasa']) && is_array($body['tasa'])
            ? array_merge($body, $body['tasa'])
            : $body;
        $token = $this->requiredStringFrom($data, ['token']);
        $codigoUsuario = $this->requiredStringFrom($data, ['codigoUsuario', 'codigo_usuario']);
        $codigoAlcaldia = $this->optionalStringFrom($data, ['codigoAlcaldia', 'codigo_alcaldia'], 'QUI');
        $numeroTasa = $this->requiredStringFrom($data, ['numeroTasa', 'numero_tasa', 'nroTasa']);
        $motivo = $this->requiredStringFrom($data, ['motivoTasa', 'motivo', 'anulado_motivo']);
        $observacion = $this->requiredStringFrom($data, ['observacion', 'anulado_observacion']);
        $response = Yii::$app->ruatServices->anularTasa(
            $token,
            $codigoUsuario,
            $numeroTasa,
            $motivo,
            $observacion,
            $codigoAlcaldia
        );
        $detalle = null;
        $localErrors = null;
        if ($this->ruatContinuarFlujo($response)) {
            $detalles = $this->sentajeDetallesFromTasa($numeroTasa);
        $detalle = reset($detalles);
            if ($detalle->hasAttribute('detalle_estado_anulado')) {
                $detalle->setAttribute('detalle_estado_anulado', 1);
            }
            $detalle = reset($detalles);
        if ($detalle === false) {
            return;
        }
        if ($detalle->hasAttribute('detalle_estado_pago')) {
                $detalle->setAttribute('detalle_estado_pago', 0);
            }
            if ($detalle->hasAttribute('detalle_observacion')) {
                $detalle->setAttribute('detalle_observacion', trim('Anulado RUAT: ' . $motivo . '. ' . $observacion));
            }
            $localErrors = $detalle->save(false) ? null : $detalle->getErrors();
        }
        return array_merge($this->ruatResponseArray($response), [
            'success' => $this->ruatContinuarFlujo($response),
            'mensaje' => $this->ruatMensaje($response, 'RUAT no pudo anular la tasa de sentaje.'),
            'numeroTasa' => $numeroTasa,
            'detalle' => $detalle ? $detalle->attributes : null,
            'localErrors' => $localErrors,
            'ruat' => $response,
        ]);
    }

    public function actionAnularTasaPago()
    {
        $this->ensureWriteMethod(['POST']);
        $body = $this->requestBodyParams();
        $data = isset($body['tasa']) && is_array($body['tasa'])
            ? array_merge($body, $body['tasa'])
            : $body;
        $tipo = strtolower($this->requiredStringFrom($data, ['tipo', 'paymentCategory', 'categoria']));
        $id = $this->requiredPositiveIntegerFrom($data, ['id', 'pago_id', 'eventual_id'], 'id');
        $token = $this->requiredStringFrom($data, ['token']);
        $codigoUsuario = $this->requiredStringFrom($data, ['codigoUsuario', 'codigo_usuario']);
        $codigoAlcaldia = $this->optionalStringFrom($data, ['codigoAlcaldia', 'codigo_alcaldia'], 'QUI');
        $motivo = $this->requiredStringFrom($data, ['motivoTasa', 'motivo', 'anulado_motivo']);
        $observacion = $this->requiredStringFrom($data, ['observacion', 'anulado_observacion']);
        if (in_array($tipo, ['regular', 'graderias_sillas', 'graderias', 'sillas'], true)) {
            $model = Pagos::findOne($id);
            if ($model === null || (int)$model->pago_estado !== 1) {
                throw new NotFoundHttpException('El pago seleccionado no existe o no esta activo.');
            }
            if ((int)$model->pago_anulado === 1) {
                throw new BadRequestHttpException('El pago ya se encuentra anulado.');
            }
            if ((int)$model->pago_cobrado === 1) {
                throw new BadRequestHttpException('No se puede anular un pago cobrado.');
            }
            $numeroTasa = trim((string)$model->pago_tasa);
            if ($numeroTasa === '') {
                throw new BadRequestHttpException('El pago seleccionado no tiene numero de tasa.');
            }
            $response = Yii::$app->ruatServices->anularTasa(
                $token,
                $codigoUsuario,
                $numeroTasa,
                $motivo,
                $observacion,
                $codigoAlcaldia
            );
            $localErrors = null;
            if ($this->ruatContinuarFlujo($response)) {
                $model->pago_anulado = 1;
                $model->pago_anulado_fecha_hora = date('Y-m-d H:i:s');
                $model->pago_anulado_detalle = trim($motivo . '. ' . $observacion);
                $localErrors = $model->save(false) ? null : $model->getErrors();
            }
            return array_merge($this->ruatResponseArray($response), [
                'success' => $this->ruatContinuarFlujo($response),
                'mensaje' => $this->ruatMensaje($response, 'RUAT no pudo anular la tasa del pago.'),
                'numeroTasa' => $numeroTasa,
                'tipo' => 'regular',
                'pago' => $model->attributes,
                'localErrors' => $localErrors,
                'ruat' => $response,
            ]);
        }
        if (in_array($tipo, ['eventual', 'alasita', 'alasitas'], true)) {
            $model = PagosEventuales::findOne($id);
            if ($model === null || (int)$model->eventual_estado !== 1) {
                throw new NotFoundHttpException('El pago eventual seleccionado no existe o no esta activo.');
            }
            if ((int)$model->eventual_anulado === 1) {
                throw new BadRequestHttpException('El pago eventual ya se encuentra anulado.');
            }
            if ((int)$model->eventual_cobrado === 1) {
                throw new BadRequestHttpException('No se puede anular un pago eventual cobrado.');
            }
            $numeroTasa = trim((string)$model->eventual_tasa);
            if ($numeroTasa === '') {
                throw new BadRequestHttpException('El pago eventual seleccionado no tiene numero de tasa.');
            }
            $response = Yii::$app->ruatServices->anularTasa(
                $token,
                $codigoUsuario,
                $numeroTasa,
                $motivo,
                $observacion,
                $codigoAlcaldia
            );
            $localErrors = null;
            if ($this->ruatContinuarFlujo($response)) {
                $model->eventual_anulado = 1;
                $model->eventual_anulado_fecha_hora = date('Y-m-d H:i:s');
                $model->eventual_anulado_detalle = trim($motivo . '. ' . $observacion);
                $localErrors = $model->save(false) ? null : $model->getErrors();
            }
            return array_merge($this->ruatResponseArray($response), [
                'success' => $this->ruatContinuarFlujo($response),
                'mensaje' => $this->ruatMensaje($response, 'RUAT no pudo anular la tasa del pago eventual.'),
                'numeroTasa' => $numeroTasa,
                'tipo' => 'eventual',
                'pago' => $model->attributes,
                'localErrors' => $localErrors,
                'ruat' => $response,
            ]);
        }
        throw new BadRequestHttpException('Tipo de pago no valido para anulacion.');
    }

    public function actionComprobantePago($id = null)
    {
        $pagoId = $this->resolvePositiveInteger($id, 'id');
        return [
            'success' => true,
            'url' => Url::to(['api-maps/comprobante-pago-pdf', 'id' => $pagoId], true),
        ];
    }

    public function actionComprobantePagoPdf($id = null)
    {
        $pagoId = $this->resolvePositiveInteger($id, 'id');
        $url = $this->generarComprobanteGraderiaSillaPdf($pagoId);
        return $this->sendPdfFile($url, 'comprobante_pago_' . $pagoId . '.pdf');
    }

    public function actionReciboPreliquidacionPago($id = null)
    {
        $pagoId = $this->resolvePositiveInteger($id, 'id');
        return [
            'success' => true,
            'url' => Url::to(['api-maps/recibo-preliquidacion-pago-pdf', 'id' => $pagoId], true),
        ];
    }

    public function actionReciboPreliquidacionPagoPdf($id = null)
    {
        $pagoId = $this->resolvePositiveInteger($id, 'id');
        $url = $this->generarReciboPreliquidacionGraderiaSillaPdf($pagoId);
        return $this->sendPdfFile($url, 'recibo_preliquidacion_pago_' . $pagoId . '.pdf');
    }

    public function actionComprobantePagoEventual($id = null)
    {
        $eventualId = $this->resolvePositiveInteger($id, 'id');
        return [
            'success' => true,
            'url' => Url::to(['api-maps/comprobante-pago-eventual-pdf', 'id' => $eventualId], true),
        ];
    }

    public function actionComprobantePagoEventualPdf($id = null)
    {
        $eventualId = $this->resolvePositiveInteger($id, 'id');
        $url = $this->generarComprobanteEventualPdf($eventualId);
        return $this->sendPdfFile($url, 'comprobante_pago_eventual_' . $eventualId . '.pdf');
    }

    public function actionComprobantePagoInfraccion($id = null)
    {
        $infraccionId = $this->resolvePositiveInteger($id, 'id');
        return [
            'success' => true,
            'url' => Url::to(['api-maps/comprobante-pago-infraccion-pdf', 'id' => $infraccionId], true),
        ];
    }

    public function actionComprobantePagoInfraccionPdf($id = null)
    {
        $infraccionId = $this->resolvePositiveInteger($id, 'id');
        $url = $this->generarComprobanteInfraccionPdf($infraccionId);
        return $this->sendPdfFile($url, 'comprobante_pago_infraccion_' . $infraccionId . '.pdf');
    }


    private function updateReservaGraderiaSilla($id, $reservado, $action)
    {
        $gradId = $this->resolveGradId($id);
        $model = GraderiasSillas::findOne($gradId);
        if ($model === null || (int)$model->grad_estado !== 1) {
            throw new NotFoundHttpException('La graderia o silla no existe o no esta activa.');
        }
        if ((int)$reservado === 1 && (int)$model->grad_vendido === 1) {
            throw new ConflictHttpException('La graderia o silla ya fue vendida y no se puede reservar.');
        }
        if ((int)$model->grad_reservado !== (int)$reservado) {
            $model->grad_reservado = (int)$reservado;
            if (!$model->save(false, ['grad_reservado'])) {
                throw new BadRequestHttpException('No se pudo actualizar la reserva de la graderia o silla.');
            }
            $changed = true;
            MapWebSocketPublisher::publishGraderiaSilla((int)$reservado === 1 ? 'reserved' : 'reservation_released', $model->grad_id);
        } else {
            $changed = false;
        }
        return [
            'success' => true,
            'action' => $action,
            'changed' => $changed,
            'message' => (int)$reservado === 1 ? 'Graderia o silla reservada.' : 'Reserva de graderia o silla liberada.',
            'graderia_silla' => $this->graderiaSillaPayload($model),
        ];
    }
    private function resolveGradId($id)
    {
        if ($id === null || $id === '') {
            $id = Yii::$app->request->get('id');
        }
        if ($id === null || $id === '') {
            $body = $this->requestBodyParams();

            if (isset($body['grad_id'])) {
                $id = $body['grad_id'];
            } elseif (isset($body['id'])) {
                $id = $body['id'];
            }
        }
        if ($id === null || $id === '' || !ctype_digit((string)$id)) {
            throw new BadRequestHttpException('Debe enviar el parametro id o grad_id.');
        }
        return (int)$id;
    }
    private function graderiaSillaPayload(GraderiasSillas $model)
    {
        return [
            'grad_id' => (int)$model->grad_id,
            'zona_id' => $model->zona_id !== null ? (int)$model->zona_id : null,
            'gest_id' => $model->gest_id !== null ? (int)$model->gest_id : null,
            'grad_codigo' => $model->grad_codigo,
            'grad_codigo_catastral' => $model->grad_codigo_catastral,
            'grad_direccion' => $model->grad_direccion,
            'grad_longitud' => $model->grad_longitud,
            'grad_propietario' => $model->grad_propietario,
            'grad_acera' => $model->grad_acera,
            'grad_tipo_armado' => $model->grad_tipo_armado,
            'grad_tipo_sitio' => $model->grad_tipo_sitio,
            'grad_vendido' => (int)$model->grad_vendido,
            'grad_reservado' => (int)$model->grad_reservado,
            'grad_estado' => (int)$model->grad_estado,
        ];
    }



    private function parseReservadoValue($body)
    {
        if (isset($body['grad_reservado'])) {
            $value = $body['grad_reservado'];
        } elseif (isset($body['reservado'])) {
            $value = $body['reservado'];
        } else {
            throw new BadRequestHttpException('Debe enviar reservado o grad_reservado.');
        }

        if ($value === true || $value === 1 || $value === '1' || $value === 'true') {
            return 1;
        }

        if ($value === false || $value === 0 || $value === '0' || $value === 'false') {
            return 0;
        }

        throw new BadRequestHttpException('El valor de reservado debe ser true/false o 1/0.');
    }

    private function requestBodyParams()
    {
        $params = Yii::$app->request->post();
        $rawBody = Yii::$app->request->getRawBody();
        if (!empty($rawBody)) {
            $json = json_decode($rawBody, true);
            if (is_array($json)) {
                $params = array_merge($params, $json);
            }
        }
        return $params;
    }

    private function generarComprobanteGraderiaSillaPdf($id)
    {
        $model = Pagos::findOne($id);
        if ($model === null || (int)$model->pago_estado !== 1) {
            throw new NotFoundHttpException('El pago no existe o no esta activo.');
        }
        if ((int)$model->pago_preliquidacion === 1 && (int)$model->pago_cobrado === 0) {
            return $this->generarReciboPreliquidacionGraderiaSillaPdf($id);
        }
        return $this->generarReportePdf('reportes', 'comprobante_graderia_silla', [
            'id_pago' => $id,
            'monto_literal' => '"' . $model->montoTotalLiteral() . '"',
            'image_path' => '"' . Yii::getAlias('@webroot') . '"',
        ]);
    }

    private function generarReciboPreliquidacionGraderiaSillaPdf($id)
    {
        $model = Pagos::findOne($id);
        if ($model === null || (int)$model->pago_estado !== 1) {
            throw new NotFoundHttpException('El pago no existe o no esta activo.');
        }
        if ((int)$model->pago_preliquidacion !== 1) {
            throw new NotFoundHttpException('El pago no corresponde a una preliquidacion.');
        }
        return $this->generarReportePdf('reportes', 'recibo_preliquidacion', [
            'id_pago' => $id,
            'monto_literal' => '"' . $model->montoTotalLiteral() . '"',
        ]);
    }

    private function generarComprobanteEventualPdf($id)
    {
        $model = PagosEventuales::findOne($id);
        if ($model === null || (int)$model->eventual_estado !== 1) {
            throw new NotFoundHttpException('El pago eventual no existe o no esta activo.');
        }
        if ((int)$model->eventual_preliquidacion === 1 && (int)$model->eventual_cobrado === 0) {
            return $this->generarReportePdf('reportes', 'preliquidacion_actividades_economicas', [
                'id_pago' => $id,
                'monto_literal' => '"' . $model->montoTotalLiteral() . '"',
            ]);
        }
        return $this->generarReportePdf('reportes', 'comprobante_eventuales2', [
            'id_pago' => $id,
            'monto_literal' => '"' . $model->montoTotalLiteral() . '"',
        ]);
    }

    private function generarComprobanteInfraccionPdf($id)
    {
        $model = PagosInfracciones::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('El pago de infraccion no existe.');
        }
        return $this->generarReportePdf('reportes', 'comprobante_infraccion', [
            'id_pago' => $id,
            'monto_literal' => '"' . $model->montoTotalLiteral() . '"',
        ]);
    }

    private function generarReciboSentajePdf($detalleId)
    {
        $model = GeneradorDescargos::findOne($detalleId);
        if ($model === null || (int)$model->detalle_estado !== 1) {
            throw new NotFoundHttpException('El detalle de sentaje no existe o no esta activo.');
        }
        return $this->generarReportePdf('reportes', 'preliquidacion_sentaje', [
            'id_detalle' => $detalleId,
            'monto_literal' => '"' . $model->montoTotalLiteral() . '"',
        ]);
    }

    private function generarReportePdf($carpeta, $archivo, $parametros)
    {
        $jasper = Yii::$app->jasper;
        $directorioActual = getcwd();
        chdir(Yii::getAlias('@webroot'));
        try {
            $jasper->compile($carpeta . '/' . $archivo . '.jrxml')->execute();
            $jasper->process(
                $carpeta . '/' . $archivo . '.jasper',
                $parametros,
                ['pdf'],
                false
            )->execute();
        } finally {
            chdir($directorioActual);
        }
        return $carpeta . '/' . $archivo . '.pdf';
    }

    private function contribuyentePayload(array $row)
    {
        if (!isset($row['contri_id']) || $row['contri_id'] === null) {
            return null;
        }
        return [
            'contri_id' => (int)$row['contri_id'],
            'ext_id' => $this->nullableInt($row, 'ext_id'),
            'sindi_id' => $this->nullableInt($row, 'sindi_id'),
            'contri_nombres' => $this->value($row, 'contri_nombres'),
            'contri_paterno' => $this->value($row, 'contri_paterno'),
            'contri_materno' => $this->value($row, 'contri_materno'),
            'contri_apellidocasada' => $this->value($row, 'contri_apellidocasada'),
            'contri_ci' => $this->value($row, 'contri_ci'),
            'contri_direccion' => $this->value($row, 'contri_direccion'),
            'contri_telefono' => $this->value($row, 'contri_telefono'),
            'contri_nit' => $this->value($row, 'contri_nit'),
            'contri_fecharegistro' => $this->value($row, 'contri_fecharegistro'),
            'contri_fechanac' => $this->value($row, 'contri_fechanac'),
            'contri_sexo' => $this->value($row, 'contri_sexo'),
            'contri_estadocivil' => $this->value($row, 'contri_estadocivil'),
            'contri_estado' => $this->nullableInt($row, 'contri_estado'),
            'contri_codigo_ruat' => $this->value($row, 'contri_codigo_ruat'),
            'contri_tipo_contribuyente_ruat' => $this->value($row, 'contri_tipo_contribuyente_ruat'),
            'contri_tipo_documento_ruat' => $this->value($row, 'contri_tipo_documento_ruat'),
            'contri_estado_ruat' => $this->value($row, 'contri_estado_ruat'),
            'contri_ruat_sync_at' => $this->value($row, 'contri_ruat_sync_at'),
            'nombre_completo' => trim(implode(' ', array_filter([
                $this->value($row, 'contri_nombres'),
                $this->value($row, 'contri_paterno'),
                $this->value($row, 'contri_materno'),
            ]))),
        ];
    }

    private function usuarioPayload(array $row, $idKey)
    {
        if (!isset($row[$idKey]) || $row[$idKey] === null) {
            return null;
        }
        return [
            'usua_id' => (int)$row[$idKey],
            'usua_nombres' => $this->value($row, 'usua_nombres'),
            'usua_apellidos' => $this->value($row, 'usua_apellidos'),
            'usua_ci' => $this->value($row, 'usua_ci'),
            'usua_cuenta' => $this->value($row, 'usua_cuenta'),
            'usua_rol' => $this->value($row, 'usua_rol'),
            'usua_estado' => $this->nullableInt($row, 'usua_estado'),
            'nombre_completo' => trim(implode(' ', array_filter([
                $this->value($row, 'usua_nombres'),
                $this->value($row, 'usua_apellidos'),
            ]))),
        ];
    }

    private function usuarioByIdPayload($usuaId)
    {
        static $usuarios = [];
        if ($usuaId === null) {
            return null;
        }
        if (!array_key_exists($usuaId, $usuarios)) {
            $usuarios[$usuaId] = Usuario::findOne($usuaId);
        }
        $usuario = $usuarios[$usuaId];
        if ($usuario === null) {
            return null;
        }
        return $this->usuarioModelPayload($usuario);
    }

    private function usuarioModelPayload(Usuario $usuario)
    {
        return [
            'usua_id' => (int)$usuario->usua_id,
            'usua_nombres' => $usuario->usua_nombres,
            'usua_apellidos' => $usuario->usua_apellidos,
            'usua_ci' => $usuario->usua_ci,
            'usua_cuenta' => $usuario->usua_cuenta,
            'usua_rol' => $usuario->usua_rol,
            'usua_estado' => $usuario->usua_estado !== null ? (int)$usuario->usua_estado : null,
            'nombre_completo' => trim(implode(' ', array_filter([
                $usuario->usua_nombres,
                $usuario->usua_apellidos,
            ]))),
        ];
    }

    private function usuarioLocalFromCuenta(array $data, $contexto)
    {
        $cuenta = $this->requiredStringFrom($data, ['usua_cuenta', 'usuarioCuenta', 'cuentaUsuario']);
        $usuario = Usuario::findOne(['usua_cuenta' => $cuenta, 'usua_estado' => 1]);
        if ($usuario === null) {
            throw new BadRequestHttpException('La cuenta de usuario "' . $cuenta . '" no existe o no esta activa para ' . $contexto . '.');
        }
        return $usuario;
    }
    private function razonSocialPayload(array $row)
    {
        $clasificadorKey = $this->value($row, 'razon_clasificador');
        $clasificadorConfigurado =
            $clasificadorKey !== null
            && trim((string)$clasificadorKey) !== '';
        if ($clasificadorConfigurado) {
            $clasificadorKey = $this->normalizeClasificadorKey($clasificadorKey);
        } else {
            $clasificadorKey = null;
        }
        return [
            'razon_id' => $this->nullableInt($row, 'razon_id'),
            'razon_nombre' => $this->value($row, 'razon_nombre'),
            'razon_estado' => $this->nullableInt($row, 'razon_estado'),
            'clasificador_key' => $clasificadorKey,
            'clasificador_configurado' => $clasificadorConfigurado,
            'clasificador' => $clasificadorKey !== null
                ? $this->clasificadorPayload($clasificadorKey)
                : null,
        ];
    }
    private function clasificadorPayload($key)
    {
        $key = $this->normalizeClasificadorKey($key);
        $catalogo = $this->clasificadoresCatalogo();
        if (!isset($catalogo[$key]) || !is_array($catalogo[$key])) {
            return null;
        }
        $config = $catalogo[$key];
        return [
            'key' => $key,
            'codigo' => isset($config['codigo'])
                ? (string)$config['codigo']
                : null,
            'label' => isset($config['label'])
                ? (string)$config['label']
                : $key,
            'observacion' => isset($config['observacion'])
                ? (string)$config['observacion']
                : null,
            'grupo' => isset($config['grupo'])
                ? (string)$config['grupo']
                : null,
        ];
    }
    private function clasificadorLabel($key)
    {
        $payload = $this->clasificadorPayload($key);
        return $payload !== null ? $payload['label'] : (string)$key;
    }
    private function sentajeClasificadorKeys()
    {
        $keys = [];
        foreach ($this->clasificadoresCatalogo() as $key => $config) {
            if (
                is_array($config)
                && isset($config['grupo'])
                && trim((string)$config['grupo']) === 'sentajes'
            ) {
                $keys[] = $this->normalizeClasificadorKey($key);
            }
        }
        return array_values(array_unique($keys));
    }

    private function graderiaSillaRowPayload(array $row)
    {
        if (!isset($row['grad_id']) || $row['grad_id'] === null) {
            return null;
        }
        return [
            'grad_id' => (int)$row['grad_id'],
            'zona_id' => $this->nullableInt($row, 'zona_id'),
            'gest_id' => $this->nullableInt($row, 'gest_id'),
            'grad_codigo' => $this->value($row, 'grad_codigo'),
            'grad_propietario' => $this->value($row, 'grad_propietario'),
            'grad_codigo_catastral' => $this->value($row, 'grad_codigo_catastral'),
            'grad_direccion' => $this->value($row, 'grad_direccion'),
            'grad_acera' => $this->value($row, 'grad_acera'),
            'grad_tipo_armado' => $this->value($row, 'grad_tipo_armado'),
            'grad_tipo_sitio' => $this->value($row, 'grad_tipo_sitio'),
            'grad_resto' => $this->value($row, 'grad_resto'),
            'grad_longitud' => $this->value($row, 'grad_longitud'),
            'grad_vendido' => $this->nullableInt($row, 'grad_vendido'),
            'grad_reservado' => $this->nullableInt($row, 'grad_reservado'),
            'grad_estado' => $this->nullableInt($row, 'grad_estado'),
            'zona' => [
                'zona_id' => $this->nullableInt($row, 'zona_id'),
                'zona_nombre' => $this->value($row, 'zona_nombre'),
                'zona_color' => $this->value($row, 'zona_color'),
                'zona_color_hexadecimal' => $this->value($row, 'zona_color_hexadecimal'),
            ],
        ];
    }

    private function tipoArmadoPayload(array $row)
    {
        if (!isset($row['tip_arm_id']) || $row['tip_arm_id'] === null) {
            return null;
        }
        return [
            'tip_arm_id' => (int)$row['tip_arm_id'],
            'tip_arm_descricpion' => $this->value($row, 'tip_arm_descricpion'),
            'tip_arm_patente' => $this->value($row, 'tip_arm_patente'),
            'tip_arm_tasa_aseo' => $this->value($row, 'tip_arm_tasa_aseo'),
            'tip_arm_unidad_medida' => $this->value($row, 'tip_arm_unidad_medida'),
            'tip_arm_estado' => $this->nullableInt($row, 'tip_arm_estado'),
        ];
    }

    private function sitioEventualPayload(array $row)
    {
        if (!isset($row['sitios_id']) || $row['sitios_id'] === null) {
            return null;
        }
        return [
            'sitios_id' => (int)$row['sitios_id'],
            'sitios_codigo' => $this->value($row, 'sitios_codigo'),
            'sitios_descripcion' => $this->value($row, 'sitios_descripcion'),
            'sitios_numero_sitio' => $this->nullableInt($row, 'sitios_numero_sitio'),
            'sitios_vendido' => $this->nullableInt($row, 'sitios_vendido'),
            'sitios_es_alasita' => $this->nullableBool($row, 'sitios_es_alasita'),
            'sitios_estado' => $this->nullableInt($row, 'sitios_estado'),
        ];
    }

    private function actividadEconomicaPayload(array $row)
    {
        if (!isset($row['activi_id']) || $row['activi_id'] === null) {
            return null;
        }
        return [
            'activi_id' => (int)$row['activi_id'],
            'categ_id' => $this->nullableInt($row, 'categ_id'),
            'activi_descripcion' => $this->value($row, 'activi_descripcion'),
            'activi_largo_mts' => $this->value($row, 'activi_largo_mts'),
            'activi_ancho_mts' => $this->value($row, 'activi_ancho_mts'),
            'activi_superficie' => $this->value($row, 'activi_superficie'),
            'activi_costo_patente' => $this->value($row, 'activi_costo_patente'),
            'activi_costo_sentaje_dia' => $this->value($row, 'activi_costo_sentaje_dia'),
            'activi_costo_aseo_por_dia' => $this->value($row, 'activi_costo_aseo_por_dia'),
            'activi_costo_aseo_por_sitio' => $this->value($row, 'activi_costo_aseo_por_sitio'),
            'activi_cobro_por_dia' => $this->nullableInt($row, 'activi_cobro_por_dia'),
            'activi_estado' => $this->nullableInt($row, 'activi_estado'),
            'categoria' => $this->categoriaPayload($row),
        ];
    }
    private function categoriaPayload(array $row)
    {
        if (!isset($row['categ_id']) || $row['categ_id'] === null) {
            return null;
        }
        return [
            'categ_id' => (int)$row['categ_id'],
            'categ_nombre' => $this->value($row, 'categ_nombre'),
            'categ_codigo' => $this->value($row, 'categ_codigo'),
            'categ_estado' => $this->nullableInt($row, 'categ_estado'),
        ];
    }

    private function pagoPayload(array $row)
    {
        $row['contribuyente'] = $this->contribuyentePayload($row);
        $row['usuario'] = $this->usuarioPayload($row, 'usua_id');
        $row['usuario_preliquidacion'] = $this->usuarioByIdPayload($this->nullableInt($row, 'pago_id_user_preliquidacion'));
        $row['graderia_silla'] = $this->graderiaSillaRowPayload($row);
        $row['tipo_armado'] = $this->tipoArmadoPayload($row);
        $this->unsetRelatedKeys($row);
        return $row;
    }

    private function pagoEventualPayload(array $row)
    {
        $row['contribuyente'] = $this->contribuyentePayload($row);
        $row['usuario'] = $this->usuarioPayload($row, 'usua_id');
        $row['usuario_preliquidacion'] = $this->usuarioByIdPayload($this->nullableInt($row, 'eventual_user_id_preliquidacion'));
        $row['sitio_eventual'] = $this->sitioEventualPayload($row);
        $row['actividad_economica'] = $this->actividadEconomicaPayload($row);
        $this->unsetRelatedKeys($row);
        return $row;
    }

    private function pagoInfraccionPayload(array $row)
    {
        $row['contribuyente'] = $this->contribuyentePayload($row);
        $row['usuario'] = $this->usuarioPayload($row, 'usua_id');
        $this->unsetRelatedKeys($row);
        return $row;
    }
    private function descargoSentajeroPayload(array $row)
    {
        $razonClasificadorKey = $this->value($row, 'razon_clasificador');
        $clasificadorConfigurado =
            $razonClasificadorKey !== null
            && trim((string)$razonClasificadorKey) !== '';
        if ($clasificadorConfigurado) {
            $razonClasificadorKey = $this->normalizeClasificadorKey(
                $razonClasificadorKey
            );
        } else {
            $razonClasificadorKey = null;
        }
        $defaultKey = $this->clasificadorSentajePorDefectoKey();
        return [
            'desc_id' => $this->nullableInt($row, 'desc_id'),
            'descargo' => [
                'desc_id' => $this->nullableInt($row, 'desc_id'),
                'desc_nro_comprobante' => $this->nullableInt($row, 'desc_nro_comprobante'),
                'desc_responsable' => $this->value($row, 'desc_responsable'),
                'desc_fecha_hora' => $this->value($row, 'desc_fecha_hora'),
                'desc_anulado' => $this->nullableInt($row, 'desc_anulado'),
                'desc_anulado_justificacion' => $this->value($row, 'desc_anulado_justificacion'),
                'desc_anulado_fecha_hora' => $this->value($row, 'desc_anulado_fecha_hora'),
                'desc_impreso' => $this->nullableInt($row, 'desc_impreso'),
                'desc_estado' => $this->nullableInt($row, 'desc_estado'),
                'desc_ci' => $this->value($row, 'desc_ci'),
                'desc_ext' => $this->nullableInt($row, 'desc_ext'),
            ],
            'razon_social' => [
                'razon_id' => $this->nullableInt($row, 'razon_id'),
                'razon_nombre' => $this->value($row, 'razon_nombre'),
                'razon_estado' => $this->nullableInt($row, 'razon_estado'),
                'clasificador_key' => $razonClasificadorKey,
                'clasificador_configurado' => $clasificadorConfigurado,
                'clasificador' => $razonClasificadorKey !== null
                    ? $this->clasificadorPayload($razonClasificadorKey)
                    : null,
            ],
            'usuario' => $this->usuarioPayload($row, 'usua_id'),
            'sentajero' => $this->value($row, 'desc_responsable') ?: '-',
            'documento' => $this->value($row, 'desc_ci') ?: '-',
            'razon_social_nombre' => $this->value($row, 'razon_nombre') ?: '-',
            'clasificador_default_key' => $defaultKey,
            'clasificador_default' => $defaultKey !== null
                ? $this->clasificadorPayload($defaultKey)
                : null,
        ];
    }
    private function sentajePayload(array $row)
    {
        $codigoClasificador = $this->value($row, 'codigo_clasificador');
        $clasificadorKey = $this->clasificadorKeyFromCodigo($codigoClasificador);
        $razonClasificadorKey = $this->value($row, 'razon_clasificador');
        $razonClasificadorConfigurado =
            $razonClasificadorKey !== null
            && trim((string)$razonClasificadorKey) !== '';
        if ($razonClasificadorConfigurado) {
            $razonClasificadorKey = $this->normalizeClasificadorKey(
                $razonClasificadorKey
            );
        } else {
            $razonClasificadorKey = null;
        }
        if ($clasificadorKey === null && $razonClasificadorKey !== null) {
            $clasificadorKey = $razonClasificadorKey;
        }
        return [
            'detalle_id' => $this->nullableInt($row, 'detalle_id'),
            'desc_id' => $this->nullableInt($row, 'desc_id'),
            'numero_tasa' => $this->value($row, 'detalle_tasa'),
            'nro_comprobante' => $this->value($row, 'nro_comprobante'),
            'descargo' => [
                'desc_id' => $this->nullableInt($row, 'desc_id'),
                'desc_nro_comprobante' => $this->nullableInt($row, 'desc_nro_comprobante'),
                'desc_responsable' => $this->value($row, 'desc_responsable'),
                'desc_fecha_hora' => $this->value($row, 'desc_fecha_hora'),
                'desc_anulado' => $this->nullableInt($row, 'desc_anulado'),
                'desc_anulado_justificacion' => $this->value($row, 'desc_anulado_justificacion'),
                'desc_anulado_fecha_hora' => $this->value($row, 'desc_anulado_fecha_hora'),
                'desc_impreso' => $this->nullableInt($row, 'desc_impreso'),
                'desc_estado' => $this->nullableInt($row, 'desc_estado'),
                'desc_ci' => $this->value($row, 'desc_ci'),
                'desc_ext' => $this->nullableInt($row, 'desc_ext'),
            ],
            'detalle' => [
                'detalle_id' => $this->nullableInt($row, 'detalle_id'),
                'detalle_precio' => $this->value($row, 'detalle_precio'),
                'detalle_nro_inicio' => $this->nullableInt($row, 'detalle_nro_inicio'),
                'detalle_nro_limite' => $this->nullableInt($row, 'detalle_nro_limite'),
                'detalle_cantidad' => $this->nullableInt($row, 'detalle_cantidad'),
                'detalle_cantidad_anulado' => $this->nullableInt($row, 'detalle_cantidad_anulado'),
                'detalle_fecha_entrega' => $this->value($row, 'detalle_fecha_entrega'),
                'detalle_importe_bs' => $this->value($row, 'detalle_importe_bs'),
                'detalle_estado' => $this->nullableInt($row, 'detalle_estado'),
                'detalle_estado_pago' => $this->nullableInt($row, 'detalle_estado_pago'),
                'detalle_estado_anulado' => $this->nullableInt($row, 'detalle_estado_anulado'),
                'detalle_tasa' => $this->value($row, 'detalle_tasa'),
                'nro_comprobante' => $this->value($row, 'nro_comprobante'),
                'detalle_observacion' => $this->value($row, 'detalle_observacion'),
                'detalle_feria' => $this->value($row, 'detalle_feria'),
                'codigo_clasificador' => $codigoClasificador,
                'clasificador_key' => $clasificadorKey,
                'clasificador' => $clasificadorKey !== null
                    ? $this->clasificadorPayload($clasificadorKey)
                    : null,
            ],
            'razon_social' => [
                'razon_id' => $this->nullableInt($row, 'razon_id'),
                'razon_nombre' => $this->value($row, 'razon_nombre'),
                'razon_estado' => $this->nullableInt($row, 'razon_estado'),
                'clasificador_key' => $razonClasificadorKey,
                'clasificador_configurado' => $razonClasificadorConfigurado,
                'clasificador' => $razonClasificadorKey !== null
                    ? $this->clasificadorPayload($razonClasificadorKey)
                    : null,
            ],
            'usuario' => $this->usuarioPayload($row, 'usua_id'),
            'recibo_url' => Url::to([
                'api-maps/recibo-sentaje-pdf',
                'id_detalle' => $row['detalle_id'],
            ], true),
        ];
    }

    private function applySentajeFilters(&$query, $hasDetalleTasa)
    {
        $this->filterPositiveInteger($query, 'dd.detalle_id', 'detalle_id');
        $this->filterPositiveInteger($query, 'd.desc_id', 'desc_id');
        $this->filterPositiveInteger($query, 'd.razon_id', 'razon_id');
        $this->filterPositiveInteger($query, 'd.usua_id', 'usua_id');
        $this->filterExact($query, 'dd.detalle_estado_pago', 'detalle_estado_pago');
        $this->filterLike($query, 'rs.razon_nombre', 'razon_nombre');
        $this->filterLike($query, 'd.desc_responsable', 'desc_responsable');
        $this->filterLike($query, 'd.desc_ci', 'desc_ci');
        $this->filterLike($query, 'u.usua_cuenta', 'usua_cuenta');
        $this->filterDateRange($query, 'dd.detalle_fecha_entrega', 'detalle_fecha_entrega_desde', 'detalle_fecha_entrega_hasta');
        $this->filterDateRange($query, 'd.desc_fecha_hora', 'desc_fecha_hora_desde', 'desc_fecha_hora_hasta');
        $search = $this->filterValue('search');
        if ($hasDetalleTasa) {
            $this->filterExact($query, 'dd.detalle_tasa', 'detalle_tasa');
        }
        if ($search !== null) {
            $conditions = [
                'or',
                ['ilike', 'd.desc_responsable', $search],
                ['ilike', 'd.desc_ci', $search],
                ['ilike', 'rs.razon_nombre', $search],
                ['ilike', 'u.usua_nombres', $search],
                ['ilike', 'u.usua_apellidos', $search],
                ['ilike', 'u.usua_cuenta', $search],
            ];
            if ($hasDetalleTasa) {
                $conditions[] = ['ilike', 'dd.detalle_tasa', $search];
            }
            $query->andWhere($conditions);
        }
    }

    private function applyContribuyenteFilters(&$query, $alias)
    {
        $this->filterPositiveInteger($query, $alias . '.contri_id', 'contri_id');
        $this->filterPositiveInteger($query, $alias . '.ext_id', 'ext_id');
        $this->filterPositiveInteger($query, $alias . '.sindi_id', 'sindi_id');
        $this->filterLike($query, $alias . '.contri_nombres', 'contri_nombres');
        $this->filterLike($query, $alias . '.contri_paterno', 'contri_paterno');
        $this->filterLike($query, $alias . '.contri_materno', 'contri_materno');
        $this->filterLike($query, $alias . '.contri_apellidocasada', 'contri_apellidocasada');
        $this->filterLike($query, $alias . '.contri_ci', 'contri_ci');
        $this->filterLike($query, $alias . '.contri_direccion', 'contri_direccion');
        $this->filterPositiveInteger($query, $alias . '.contri_telefono', 'contri_telefono');
        $this->filterPositiveInteger($query, $alias . '.contri_nit', 'contri_nit');
        $this->filterExact($query, $alias . '.contri_sexo', 'contri_sexo');
        $this->filterExact($query, $alias . '.contri_estadocivil', 'contri_estadocivil');
        $this->filterLike($query, $alias . '.contri_codigo_ruat', 'contri_codigo_ruat');
        $this->filterExact($query, $alias . '.contri_tipo_contribuyente_ruat', 'contri_tipo_contribuyente_ruat');
        $this->filterExact($query, $alias . '.contri_tipo_documento_ruat', 'contri_tipo_documento_ruat');
        $this->filterExact($query, $alias . '.contri_estado_ruat', 'contri_estado_ruat');
        $this->filterExact($query, $alias . '.contri_estado_operativo', 'contri_estado_operativo');
        $this->filterDateRange($query, $alias . '.contri_fecharegistro', 'contri_fecharegistro_desde', 'contri_fecharegistro_hasta');
        $this->filterDateRange($query, $alias . '.contri_fechanac', 'contri_fechanac_desde', 'contri_fechanac_hasta');
        $this->filterDateRange($query, $alias . '.contri_ruat_sync_at', 'contri_ruat_sync_at_desde', 'contri_ruat_sync_at_hasta');
    }
    private function applyPagoInfraccionFilters(&$query, $alias)
    {
        $this->filterPositiveInteger($query, $alias . '.infraccion_id', 'infraccion_id');
        $this->filterPositiveInteger($query, $alias . '.contri_id', 'contri_id');
        $this->filterPositiveInteger($query, $alias . '.usua_id', 'usua_id');
        $this->filterExact($query, $alias . '.numero_tasa', 'numero_tasa');
        $this->filterLike($query, $alias . '.numero_documento', 'numero_documento');
        $this->filterLike($query, $alias . '.tipo_infraccion', 'tipo_infraccion');
        $this->filterLike($query, $alias . '.descripcion_infraccion', 'descripcion_infraccion');
        $this->filterLike($query, $alias . '.lugar_infraccion', 'lugar_infraccion');
        $this->filterExact($query, $alias . '.gestion', 'gestion');
        $this->filterBinary($query, $alias . '.infraccion_estado', 'infraccion_estado');
        $this->filterBinary($query, $alias . '.infraccion_pagado', 'infraccion_pagado');
        $this->filterBinary($query, $alias . '.infraccion_anulado', 'infraccion_anulado');
        $this->filterDateRange($query, $alias . '.fecha_infraccion', 'fecha_infraccion_desde', 'fecha_infraccion_hasta');
        $this->filterDateRange($query, $alias . '.fecha_pago', 'fecha_pago_desde', 'fecha_pago_hasta');
        $this->filterDateRange($query, $alias . '.anulado_fecha_hora', 'anulado_fecha_hora_desde', 'anulado_fecha_hora_hasta');
        $search = $this->filterValue('search');
        if ($search !== null) {
            $query->andWhere([
                'or',
                ['ilike', $alias . '.numero_tasa', $search],
                ['ilike', $alias . '.numero_documento', $search],
                ['ilike', $alias . '.codigo_contribuyente', $search],
                ['ilike', $alias . '.tipo_documento', $search],
                ['ilike', $alias . '.tipo_infraccion', $search],
                ['ilike', $alias . '.descripcion_infraccion', $search],
                ['ilike', $alias . '.lugar_infraccion', $search],
                ['ilike', $alias . '.gestion', $search],
                ['ilike', $alias . '.codigo_clasificador', $search],
                ['ilike', $alias . '.observacion', $search],
                ['ilike', 'c.contri_nombres', $search],
                ['ilike', 'c.contri_paterno', $search],
                ['ilike', 'c.contri_materno', $search],
                ['ilike', 'u.usua_nombres', $search],
                ['ilike', 'u.usua_apellidos', $search],
                ['ilike', 'u.usua_cuenta', $search],
            ]);
        }
    }
    private function applyPagoFilters(&$query, $alias)
     {
         $this->filterPositiveInteger($query, $alias . '.pago_id', 'pago_id');
         $this->filterPositiveInteger($query, $alias . '.contri_id', 'contri_id');
         $this->filterPositiveInteger($query, $alias . '.usua_id', 'usua_id');
         $this->filterPositiveInteger($query, $alias . '.grad_id', 'grad_id');
         $this->filterPositiveInteger($query, $alias . '.tip_arm_id', 'tip_arm_id');
         $this->filterExact($query, $alias . '.pago_nro_liquidacion', 'pago_nro_liquidacion');
         $this->filterPositiveInteger($query, $alias . '.pago_nro_comprobante', 'pago_nro_comprobante');
         $this->filterExact($query, $alias . '.pago_tasa', 'pago_tasa');
         $this->filterBinary($query, $alias . '.pago_preliquidacion', 'pago_preliquidacion');
         $this->filterBinary($query, $alias . '.pago_cobrado', 'pago_cobrado');
         $this->filterBinary($query, $alias . '.pago_anulado', 'pago_anulado');
         $this->filterBinary($query, $alias . '.pago_con_exencion', 'pago_con_exencion');
         $this->filterLike($query, 'c.contri_ci', 'contri_ci');
         $this->filterLike($query, 'c.contri_nombres', 'contri_nombres');
         $this->filterLike($query, 'c.contri_paterno', 'contri_paterno');
         $this->filterLike($query, 'c.contri_materno', 'contri_materno');
         $this->filterLike($query, 'gs.grad_codigo', 'grad_codigo');
         $this->filterLike($query, 'gs.grad_direccion', 'grad_direccion');
         $this->filterLike($query, 'z.zona_nombre', 'zona_nombre');
         $this->filterLike($query, 'ta.tip_arm_descricpion', 'tip_arm_descricpion');
         $this->filterDateRange($query, $alias . '.pago_fecha_hora_preliquidacion', 'pago_fecha_hora_preliquidacion_desde', 'pago_fecha_hora_preliquidacion_hasta');
         $this->filterDateRange($query, $alias . '.pago_fecha_hora_cobro', 'pago_fecha_hora_cobro_desde', 'pago_fecha_hora_cobro_hasta');
     }
        private function applyPagoEventualFilters(&$query, $alias)
        {
          $this->filterPositiveInteger($query, $alias . '.eventual_id', 'eventual_id');
          $this->filterPositiveInteger($query, $alias . '.contri_id', 'contri_id');
          $this->filterPositiveInteger($query, $alias . '.usua_id', 'usua_id');
          $this->filterPositiveInteger($query, $alias . '.sitios_id', 'sitios_id');
          $this->filterPositiveInteger($query, $alias . '.activi_id', 'activi_id');
          $this->filterExact($query, $alias . '.eventual_nro_liquidacion', 'eventual_nro_liquidacion');
          $this->filterPositiveInteger($query, $alias . '.eventual_nro_comprobante', 'eventual_nro_comprobante');
          $this->filterExact($query, $alias . '.eventual_tasa', 'eventual_tasa');
          $this->filterBinary($query, $alias . '.eventual_preliquidacion', 'eventual_preliquidacion');
          $this->filterBinary($query, $alias . '.eventual_cobrado', 'eventual_cobrado');
          $this->filterBinary($query, $alias . '.eventual_anulado', 'eventual_anulado');
          $this->filterLike($query, 'c.contri_ci', 'contri_ci');
          $this->filterLike($query, 'c.contri_nombres', 'contri_nombres');
          $this->filterLike($query, 'c.contri_paterno', 'contri_paterno');
          $this->filterLike($query, 'c.contri_materno', 'contri_materno');
          $this->filterLike($query, 's.sitios_codigo', 'sitios_codigo');
          $this->filterLike($query, 's.sitios_descripcion', 'sitios_descripcion');
          $this->filterLike($query, 'a.activi_descripcion', 'activi_descripcion');
          $this->filterLike($query, 'cat.categ_nombre', 'categ_nombre');
          $this->filterDateRange($query, $alias . '.eventual_fecha_hora_liquidacion', 'eventual_fecha_hora_liquidacion_desde', 'eventual_fecha_hora_liquidacion_hasta');
          $this->filterDateRange($query, $alias . '.eventual_fecha_hora_pago', 'eventual_fecha_hora_pago_desde', 'eventual_fecha_hora_pago_hasta');
          $this->filterDateRange($query, $alias . '.eventual_fecha_inicio', 'eventual_fecha_inicio_desde', 'eventual_fecha_inicio_hasta');
          $this->filterDateRange($query, $alias . '.eventual_fecha_limite', 'eventual_fecha_limite_desde', 'eventual_fecha_limite_hasta');
      }
    private function resolvePositiveInteger($value, $paramName)
    {
        if ($value === null || $value === '') {
            $value = Yii::$app->request->get($paramName);
        }
        if ($value === null || $value === '' || !ctype_digit((string)$value) || (int)$value <= 0) {
            throw new BadRequestHttpException('Debe enviar el parametro ' . $paramName . '.');
        }
        return (int)$value;
    }
    private function value(array $row, $key)
    {
        return array_key_exists($key, $row) ? $row[$key] : null;
    }

    private function nullableInt(array $row, $key)
    {
        return isset($row[$key]) && $row[$key] !== '' ? (int)$row[$key] : null;
    }

    private function nullableBool(array $row, $key)
    {
        if (!isset($row[$key])) {
            return null;
        }
        if ($row[$key] === true || $row[$key] === 1 || $row[$key] === '1' || $row[$key] === 't' || $row[$key] === 'true') {
            return true;
        }
        if ($row[$key] === false || $row[$key] === 0 || $row[$key] === '0' || $row[$key] === 'f' || $row[$key] === 'false') {
            return false;
        }
        return (bool)$row[$key];
    }

    private function unsetKeys(array &$row, array $keys)
    {
        foreach ($keys as $key) {
            unset($row[$key]);
        }
    }
private function unsetRelatedKeys(array &$row)
{
    $this->unsetKeys($row, [
        'ext_id',
        'sindi_id',
        'contri_estado',
        'contri_nombres',
        'contri_paterno',
        'contri_materno',
        'contri_apellidocasada',
        'contri_ci',
        'contri_direccion',
        'contri_telefono',
        'contri_nit',
        'contri_fecharegistro',
        'contri_fechanac',
        'contri_sexo',
        'contri_estadocivil',
        'contri_codigo_ruat',
        'contri_tipo_contribuyente_ruat',
        'contri_tipo_documento_ruat',
        'contri_ruat_sync_at',
        'contri_estado_ruat',
        'contri_estado_operativo',

        'usua_nombres',
        'usua_apellidos',
        'usua_ci',
        'usua_cuenta',
        'usua_rol',
        'usua_estado',

        'grad_codigo',
        'grad_direccion',
        'zona_id',
        'gest_id',
        'grad_propietario',
        'grad_codigo_catastral',
        'grad_acera',
        'grad_tipo_armado',
        'grad_tipo_sitio',
        'grad_resto',
        'grad_longitud',
        'grad_vendido',
        'grad_reservado',
        'grad_estado',
        'zona_nombre',
        'zona_color',
        'zona_color_hexadecimal',

        'tip_arm_descricpion',
        'tip_arm_patente',
        'tip_arm_tasa_aseo',
        'tip_arm_unidad_medida',
        'tip_arm_estado',

        'sitios_codigo',
        'sitios_descripcion',
        'sitios_numero_sitio',
        'sitios_vendido',
        'sitios_es_alasita',
        'sitios_estado',

        'activi_descripcion',
        'categ_id',
        'activi_largo_mts',
        'activi_ancho_mts',
        'activi_superficie',
        'activi_costo_patente',
        'activi_costo_sentaje_dia',
        'activi_costo_aseo_por_dia',
        'activi_costo_aseo_por_sitio',
        'activi_cobro_por_dia',
        'activi_estado',

        'categ_nombre',
        'categ_codigo',
        'categ_estado',
    ]);
}
    private function clasificadoresCatalogo()
    {
        $catalogo = Yii::$app->params['clasificadoresCatalogo'] ?? [];
        return is_array($catalogo) ? $catalogo : [];
    }

    private function assertClasificadorSentaje($key)
    {
        $key = $this->normalizeClasificadorKey($key);
        $catalogo = $this->clasificadoresCatalogo();

        if (!isset($catalogo[$key]) || !is_array($catalogo[$key])) {
            throw new BadRequestHttpException(
                'El clasificador "' . $key . '" no existe en la configuracion.'
            );
        }

        $grupo = isset($catalogo[$key]['grupo'])
            ? trim((string)$catalogo[$key]['grupo'])
            : '';

        if ($grupo !== 'sentajes') {
            throw new BadRequestHttpException(
                'El clasificador "' . $key . '" no esta permitido para sentajes.'
            );
        }
    }

    private function clasificadorSentajePorDefectoKey()
    {
        $periodos = Yii::$app->params['eventPeriods'] ?? [];
        $catalogo = $this->clasificadoresCatalogo();

        if (!is_array($periodos) || empty($periodos) || empty($catalogo)) {
            return null;
        }

        try {
            $timezone = new \DateTimeZone(
                Yii::$app->timeZone ?: 'America/La_Paz'
            );
        } catch (\Throwable $exception) {
            $timezone = new \DateTimeZone('America/La_Paz');
        }

        $hoy = new \DateTimeImmutable('today', $timezone);
        $clasificadorMasProximo = null;
        $menorDistancia = null;

        foreach ($periodos as $eventoKey => $periodo) {
            if (!is_array($periodo)) {
                continue;
            }

            $fechaInicio = isset($periodo['start'])
                ? trim((string)$periodo['start'])
                : '';
            $fechaFin = isset($periodo['end'])
                ? trim((string)$periodo['end'])
                : '';

            if ($fechaInicio === '' || $fechaFin === '') {
                continue;
            }

            $inicio = \DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $fechaInicio,
                $timezone
            );
            $fin = \DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $fechaFin,
                $timezone
            );

            if ($inicio === false || $fin === false || $fin < $inicio) {
                Yii::warning(
                    'Periodo invalido configurado para el evento "' .
                    $eventoKey . '".',
                    __METHOD__
                );
                continue;
            }

            $eventoNormalizado = $this->normalizeClasificadorKey($eventoKey);
            $clasificadorKey = $this->normalizeClasificadorKey(
                'sentajes_' . $eventoNormalizado
            );

            if (!isset($catalogo[$clasificadorKey])) {
                Yii::warning(
                    'No existe el clasificador "' . $clasificadorKey .
                    '" para el evento "' . $eventoKey . '".',
                    __METHOD__
                );
                continue;
            }

            $grupo = isset($catalogo[$clasificadorKey]['grupo'])
                ? trim((string)$catalogo[$clasificadorKey]['grupo'])
                : '';

            if ($grupo !== 'sentajes') {
                continue;
            }

            if ($hoy >= $inicio && $hoy <= $fin) {
                return $clasificadorKey;
            }

            $distancia = $hoy < $inicio
                ? $inicio->getTimestamp() - $hoy->getTimestamp()
                : $hoy->getTimestamp() - $fin->getTimestamp();

            if ($menorDistancia === null || $distancia < $menorDistancia) {
                $menorDistancia = $distancia;
                $clasificadorMasProximo = $clasificadorKey;
            }
        }

        return $clasificadorMasProximo;
    }

    private function clasificadorKeyFromCodigo($codigo)
    {
        if ($codigo === null || trim((string)$codigo) === '') {
            return null;
        }
        $codigo = trim((string)$codigo);
        foreach ($this->clasificadoresCatalogo() as $key => $config) {
            if (
                is_array($config)
                && isset($config['codigo'])
                && trim((string)$config['codigo']) === $codigo
            ) {
                return $this->normalizeClasificadorKey($key);
            }
        }
        return null;
    }

    private function clasificadorSentajeFromRazon(RazonSociales $razon, array $data)
    {
        $keySeleccionada = $this->optionalStringFrom(
            $data,
            [
                'clasificador',
                'clasificador_key',
                'clasificadorKey',
                'codigo_clasificador_key',
                'codigoClasificadorKey',
            ],
            null
        );
        if ($keySeleccionada !== null) {
            $keySeleccionada = $this->normalizeClasificadorKey($keySeleccionada);
            $this->assertClasificadorSentaje($keySeleccionada);
            return $keySeleccionada;
        }
        if ($razon->hasAttribute('razon_clasificador')) {
            $value = $razon->getAttribute('razon_clasificador');
            if ($value !== null && trim((string)$value) !== '') {
                $keyRazon = $this->normalizeClasificadorKey($value);
                $this->assertClasificadorSentaje($keyRazon);
                return $keyRazon;
            }
        }
        $defaultKey = $this->clasificadorSentajePorDefectoKey();
        if ($defaultKey === null) {
            throw new BadRequestHttpException(
                'No se pudo determinar un clasificador inicial de sentaje desde la configuracion.'
            );
        }
        $this->assertClasificadorSentaje($defaultKey);
        return $defaultKey;
    }
    private function normalizeClasificadorKey($key)
    {
        return strtolower(trim((string)$key));
    }
    private function codigoClasificadorFromKey($key)
    {
        $key = $this->normalizeClasificadorKey($key);
        $catalogo = $this->clasificadoresCatalogo();
        if (
            !isset($catalogo[$key])
            || !is_array($catalogo[$key])
            || !isset($catalogo[$key]['codigo'])
            || trim((string)$catalogo[$key]['codigo']) === ''
        ) {
            throw new BadRequestHttpException(
                'No existe codigo clasificador configurado para "' . $key . '".'
            );
        }
        return trim((string)$catalogo[$key]['codigo']);
    }
    private function sentajeObservacion($clasificadorKey, $actividad)
    {
        $payload = $this->clasificadorPayload($clasificadorKey);
        if ($payload === null) {
            throw new BadRequestHttpException(
                'No existe configuracion para el clasificador seleccionado.'
            );
        }
        $base = isset($payload['observacion'])
            ? trim((string)$payload['observacion'])
            : '';
        $actividad = trim((string)$actividad);
        $observacion = $base;
        if ($actividad !== '') {
            $observacion = $observacion === ''
                ? $actividad
                : $observacion . ' - ' . $actividad;
        }
        $observacion = mb_substr($observacion, 0, 250, 'UTF-8');
        $textoLimpio = iconv('UTF-8', 'ASCII//TRANSLIT', $observacion);
        if ($textoLimpio === false) {
            $textoLimpio = $observacion;
        }
        return preg_replace('/[^a-zA-Z0-9\s.\-,.:]/u', '', $textoLimpio);
    }

    private function ruatMensaje($response, $default)
    {
        if (!$response) {
            return $default;
        }
        foreach (['mensaje', 'message', 'descripcion', 'error'] as $key) {
            if (isset($response->$key) && trim((string)$response->$key) !== '') {
                return (string)$response->$key;
            }
        }
        if (isset($response->mensajes) && is_array($response->mensajes) && count($response->mensajes) > 0) {
            return implode(' ', array_map('strval', $response->mensajes));
        }
        return $default;
    }

    private function ruatContinuarFlujo($response)
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

    private function ruatResponseArray($response)
    {
        if ($response === null) {
            return [];
        }
        $data = (array)$response;
        unset($data['__ruatHttpOk'], $data['__ruatHttpStatus'], $data['__ruatTechnicalError']);
        return $data;
    }

    private function consultaPagoSentajeResponse($token, $codigoAlcaldia, $numeroTasa)
    {
        $detalles = $this->sentajeDetallesFromTasa($numeroTasa);
        $detalle = reset($detalles);
        $response = Yii::$app->ruatServices->consultaPagoTasa($token, $numeroTasa, $codigoAlcaldia);
        $consultaExitosa = $response !== null && empty($response->__ruatTechnicalError);
        $pagado = $consultaExitosa && $this->ruatContinuarFlujo($response) && isset($response->pagoTasa);
        if ($pagado) {
            $this->actualizarSentajesPagados($detalles, $numeroTasa, $response->pagoTasa);
        } elseif ($this->ruatMensajeIndicaAnulacion($response)) {

            GeneradorDescargos::updateAll(
                ['detalle_estado_anulado' => 1],
                ['detalle_tasa' => $numeroTasa]
            );

            foreach ($detalles as $item) {
                if ($item->hasAttribute('detalle_estado_anulado')) {
                    $item->detalle_estado_anulado = 1;
                }
            }
        }
        return array_merge($this->ruatResponseArray($response), [
            'success' => $consultaExitosa,
            'consultaExitosa' => $consultaExitosa,
            'continuarFlujo' => $this->ruatContinuarFlujo($response),
            'numeroTasa' => $numeroTasa,
            'pagado' => $pagado,
            'detalle' => $detalle->attributes,
            'registrosLocales' => [
                'sentaje' => $detalle->attributes,
                'sentajes' => array_map(function ($item) {
                    return $item->attributes;
                }, $detalles),
            ],
            'registroLocalTipos' => ['sentaje'],
            'ruat' => $response,
        ]);
    }

    private function actualizarSentajesPagados(array $detalles, $numeroTasa, $pagoTasa)
    {
        if (empty($detalles)) {
            return;
        }

        $attributes = [];
        $detalle = reset($detalles);

        if ($detalle->hasAttribute('detalle_estado_pago')) {
            $attributes['detalle_estado_pago'] = 1;
        }

        if ($detalle->hasAttribute('detalle_estado_anulado')) {
            $attributes['detalle_estado_anulado'] = 0;
        }

        if ($detalle->hasAttribute('nro_comprobante')) {
            $attributes['nro_comprobante'] = $numeroTasa;
        }

        if ($detalle->hasAttribute('detalle_observacion')) {
            $attributes['detalle_observacion'] = $this->observacionPagoRuat($pagoTasa);
        }

        if (!empty($attributes)) {

            // UPDATE EVERY DETAIL OF THIS TASA
            GeneradorDescargos::updateAll(
                $attributes,
                ['detalle_tasa' => $numeroTasa]
            );

            // keep returned objects synchronized
            foreach ($detalles as $detalle) {
                foreach ($attributes as $attribute => $value) {
                    $detalle->setAttribute($attribute, $value);
                }
            }
        }
    }
    private function sentajeDetallesFromTasa($numeroTasa)
    {
        $detalles = GeneradorDescargos::find()
            ->where(['detalle_tasa' => $numeroTasa])
            ->andWhere(['detalle_estado' => [0, 1]])
            ->orderBy(['detalle_id' => SORT_DESC])
            ->all();

        if (empty($detalles)) {
            throw new NotFoundHttpException('No existe una preliquidacion de sentaje para la tasa enviada.');
        }

        return $detalles;
    }

    private function sentajeDetalleItemsFromRequest(array $data, $detalleFeria)
    {
        $items = $data['detalles'] ?? null;
        if (!is_array($items) || empty($items)) {
            $items = [$data];
        }

        $detalleItems = [];
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                throw new BadRequestHttpException('Cada detalle debe enviarse como un objeto valido.');
            }

            $precio = $this->requiredNumberFrom($item, ['detalle_precio', 'precio'], 'precio');
            $nroInicio = $this->requiredPositiveIntegerFrom($item, ['detalle_nro_inicio', 'nro_inicio'], 'nro_inicio');
            $nroLimite = $this->requiredPositiveIntegerFrom($item, ['detalle_nro_limite', 'nro_limite'], 'nro_limite');
            $cantidadAnulado = $this->optionalNonNegativeInteger($item, ['detalle_cantidad_anulado', 'cantidad_anulado'], 0);

            if ($nroLimite < $nroInicio) {
                throw new BadRequestHttpException('El nro limite no puede ser menor al nro inicio en el detalle ' . $index . '.');
            }

            $cantidad = ($nroLimite - $nroInicio) + 1;
            if ($cantidadAnulado > $cantidad) {
                throw new BadRequestHttpException('La cantidad de anulados no puede ser mayor a la cantidad en el detalle ' . $index . '.');
            }

            $cantidadFinal = $cantidad - $cantidadAnulado;
            $importe = round($precio * $cantidadFinal, 2);
            if ($importe <= 0) {
                throw new BadRequestHttpException('El importe de la preliquidacion debe ser mayor a cero.');
            }

            $detalleItems[] = [
                'detalle_precio' => $precio,
                'detalle_nro_inicio' => $nroInicio,
                'detalle_nro_limite' => $nroLimite,
                'detalle_cantidad' => $cantidadFinal,
                'detalle_cantidad_anulado' => $cantidadAnulado,
                'detalle_importe_bs' => $importe,
                'detalle_feria' => $this->optionalStringFrom($item, ['detalle_feria', 'feria', 'actividad'], $detalleFeria),
            ];
        }

        if (empty($detalleItems)) {
            throw new BadRequestHttpException('Debe enviar al menos un detalle de preliquidacion.');
        }

        return $detalleItems;
    }

    private function numeroTasasFromRequest(array $data)
    {
        $source = $this->firstScalar($data, ['numerosTasa', 'numeroTasas', 'numeros_tasa', 'tasas']);
        if (!is_array($source)) {
            throw new BadRequestHttpException('Debe enviar numerosTasa como una lista.');
        }

        $numeros = [];
        foreach ($source as $index => $item) {
            $numeroTasa = is_array($item)
                ? $this->firstScalar($item, ['numeroTasa', 'numero_tasa', 'detalle_tasa'])
                : $item;
            $numeroTasa = is_scalar($numeroTasa) ? trim((string)$numeroTasa) : '';

            if ($numeroTasa === '') {
                throw new BadRequestHttpException('La tasa en la posicion ' . $index . ' es requerida.');
            }

            $numeros[] = $numeroTasa;
        }

        if (empty($numeros)) {
            throw new BadRequestHttpException('Debe enviar al menos una tasa para consultar.');
        }

        return $numeros;
    }

    private function observacionPagoRuat($pagoTasa)
    {
        if (!$pagoTasa) {
            return null;
        }

        return 'Folio: ' . ($pagoTasa->folio ?? '-') .
            ', Fecha Pago: ' . ($pagoTasa->fechaPago ?? '-') .
            ', Entidad Financiera: ' . ($pagoTasa->entidadFinanciera ?? '-') .
            ', Monto Pagado: ' . ($pagoTasa->montoPago ?? '-');
    }

    private function ruatMensajeIndicaAnulacion($response)
    {
        if (!$response || !isset($response->mensaje)) {
            return false;
        }
        $mensaje = is_scalar($response->mensaje)
            ? strtolower((string)$response->mensaje)
            : strtolower((string)json_encode($response->mensaje));
        return strpos($mensaje, 'anulada') !== false || strpos($mensaje, 'anulado') !== false;
    }

    private function firstScalar(array $data, array $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                return $data[$key];
            }
        }
        return null;
    }

    private function requiredStringFrom(array $data, array $keys)
    {
        $value = $this->firstScalar($data, $keys);
        if (!is_scalar($value)) {
            throw new BadRequestHttpException('Debe enviar ' . implode(' o ', $keys) . '.');
        }
        $value = trim((string)$value);
        if ($value === '') {
            throw new BadRequestHttpException('Debe enviar ' . implode(' o ', $keys) . '.');
        }
        return $value;
    }

    private function optionalStringFrom(array $data, array $keys, $default)
    {
        $value = $this->firstScalar($data, $keys);
        if ($value === null) {
            return $default;
        }
        if (!is_scalar($value)) {
            throw new BadRequestHttpException(implode(' o ', $keys) . ' debe ser un valor simple.');
        }
        $value = trim((string)$value);
        return $value === '' ? $default : $value;
    }

    private function requiredPositiveIntegerFrom(array $data, array $keys, $label)
    {
        $value = $this->firstScalar($data, $keys);
        if ($value === null || $value === '' || !ctype_digit((string)$value) || (int)$value <= 0) {
            throw new BadRequestHttpException('Debe enviar ' . $label . ' como entero positivo.');
        }
        return (int)$value;
    }

    private function optionalPositiveInteger(array $data, array $keys, $default)
    {
        $value = $this->firstScalar($data, $keys);
        if ($value === null || $value === '') {
            return $default;
        }
        if (!ctype_digit((string)$value) || (int)$value <= 0) {
            throw new BadRequestHttpException(implode(' o ', $keys) . ' debe ser un entero positivo.');
        }
        return (int)$value;
    }

    private function optionalNonNegativeInteger(array $data, array $keys, $default)
    {
        $value = $this->firstScalar($data, $keys);
        if ($value === null || $value === '') {
            return $default;
        }
        if (!ctype_digit((string)$value) || (int)$value < 0) {
            throw new BadRequestHttpException(implode(' o ', $keys) . ' debe ser un entero mayor o igual a cero.');
        }
        return (int)$value;
    }

    private function requiredNumberFrom(array $data, array $keys, $label)
    {
        $value = $this->firstScalar($data, $keys);
        if ($value === null || $value === '' || !is_numeric($value) || (float)$value <= 0) {
            throw new BadRequestHttpException('Debe enviar ' . $label . ' como numero mayor a cero.');
        }
        return (float)$value;
    }

    private function ensureWriteMethod($allowedMethods)
    {
        if (!in_array(Yii::$app->request->method, $allowedMethods, true)) {
            throw new MethodNotAllowedHttpException('Metodo no permitido.');
        }
    }
    private function sendPdfFile($url, $filename)
    {
        $path = Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $url);
        if (!is_file($path)) {
            throw new NotFoundHttpException('No se pudo generar el comprobante.');
        }
        Yii::$app->response->format = Response::FORMAT_RAW;
        return Yii::$app->response->sendFile($path, $filename, [
            'inline' => true,
            'mimeType' => 'application/pdf',
        ]);
    }

    private function filterExact(&$query, $column, $paramName)
    {
        $value = $this->filterValue($paramName);
        if ($value !== null) {
            $query->andWhere([$column => $value]);
        }
    }

    private function filterLike(&$query, $column, $paramName)
    {
        $value = $this->filterValue($paramName);
        if ($value !== null) {
            $query->andWhere(['ilike', $column, $value]);
        }
    }

    private function filterPositiveInteger(&$query, $column, $paramName)
    {
        $value = $this->filterValue($paramName);
        if ($value === null) {
            return;
        }
        if (!ctype_digit($value) || (int)$value < 1) {
            throw new BadRequestHttpException($paramName . ' debe ser un entero positivo.');
        }
        $query->andWhere([$column => (int)$value]);
    }

    private function filterBinary(&$query, $column, $paramName)
    {
        $value = $this->filterValue($paramName);
        if ($value === null) {
            return;
        }
        if ($value !== '0' && $value !== '1') {
            throw new BadRequestHttpException($paramName . ' debe ser 0 o 1.');
        }
        $query->andWhere([$column => (int)$value]);
    }

    private function filterValue($paramName)
    {
        $value = Yii::$app->request->get($paramName);
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_scalar($value)) {
            throw new BadRequestHttpException($paramName . ' debe ser un valor simple.');
        }
        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }

    private function filterDateRange(&$query, $column, $fromParam, $toParam)
    {
        $from = $this->parseDateFilter($fromParam);
        $to = $this->parseDateFilter($toParam);

        if ($from !== null) {
            $query->andWhere(['>=', $column, $from['value']]);
        }

        if ($to !== null) {
            if ($to['date_only']) {
                $query->andWhere(['<', $column, $to['date']->modify('+1 day')->format('Y-m-d')]);
            } else {
                $query->andWhere(['<=', $column, $to['value']]);
            }
        }

        if ($from !== null && $to !== null) {
            $upperDate = $to['date_only'] ? $to['date']->modify('+1 day') : $to['date'];
            $invalidRange = $to['date_only'] ? $from['date'] >= $upperDate : $from['date'] > $upperDate;
            if ($invalidRange) {
                throw new BadRequestHttpException($fromParam . ' no puede ser posterior a ' . $toParam . '.');
            }
        }
    }

    private function parseDateFilter($paramName)
    {
        $value = $this->filterValue($paramName);
        if ($value === null) {
            return null;
        }

        $formats = [
            'Y-m-d' => true,
            'Y-m-d H:i:s' => false,
            'Y-m-d\TH:i:s' => false,
        ];
        foreach ($formats as $format => $dateOnly) {
            $date = \DateTimeImmutable::createFromFormat('!' . $format, $value);
            $errors = \DateTimeImmutable::getLastErrors();
            if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0)) && $date->format($format) === $value) {
                return [
                    'date' => $date,
                    'date_only' => $dateOnly,
                    'value' => $dateOnly ? $date->format('Y-m-d') : $date->format('Y-m-d H:i:s'),
                ];
            }
        }

        throw new BadRequestHttpException($paramName . ' debe usar YYYY-MM-DD o YYYY-MM-DD HH:MM:SS.');
    }
    private function paginatedList($query, $mapper = null)
    {
        $paginate = Yii::$app->request->get('paginate');
        if ($paginate !== '1') {
            $rows = $query->all();
            return $mapper === null
                ? $rows
                : array_map($mapper, $rows);
        }
        $page = $this->paginationInteger('page', 1, null);
        $perPage = $this->paginationInteger('per_page', 50, 200);

        $countQuery = clone $query;
        $total = (int)$countQuery->count();

        $rows = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->all();

        return [
            'items' => $mapper === null ? $rows : array_map($mapper, $rows),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int)ceil($total / $perPage),
                'has_previous' => $page > 1,
                'has_next' => $page * $perPage < $total,
            ],
        ];
    }

    private function paginationInteger($paramName, $default, $maximum)
    {
        $value = Yii::$app->request->get($paramName, $default);
        if (!is_scalar($value) || !ctype_digit((string)$value) || (int)$value < 1) {
            throw new BadRequestHttpException($paramName . ' debe ser un entero positivo.');
        }
        $value = (int)$value;
        if ($maximum !== null && $value > $maximum) {
            throw new BadRequestHttpException($paramName . ' no puede ser mayor a ' . $maximum . '.');
        }
        return $value;
    }
}
