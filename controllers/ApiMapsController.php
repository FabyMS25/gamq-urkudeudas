<?php
namespace app\controllers;

use Yii;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use app\models\Gestiones;
use app\models\GraderiasSillas;
use app\models\Pagos;
use app\models\PagosEventuales;
use app\models\Usuario;
use yii\web\Controller;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;

class ApiMapsController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => Cors::className(),
                'cors' => [   
                    'Origin' => [
                        'http://localhost:4200',
                        'http://localhost:5200',
                        'http://localhost:5173',
                        'http://127.0.0.1:4200',
                        'http://127.0.0.1:5200',
                        'http://127.0.0.1:5173',
                        'http://181.177.143.185:4205'
                    ],
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
                    'categorias' => ['GET', 'OPTIONS'],
                    'tipo-armados' => ['GET', 'OPTIONS'],
                    'actividades-economicas' => ['GET', 'OPTIONS'],
                    'graderias-sillas' => ['GET', 'OPTIONS'],
                    'actualizar-reserva-graderia-silla' => ['POST', 'OPTIONS'],
                    'sitios-eventuales' => ['GET', 'OPTIONS'],
                    'contribuyentes' => ['GET', 'OPTIONS'],
                    'usuarios' => ['GET', 'OPTIONS'],
                    'pagos' => ['GET', 'OPTIONS'],
                    'pagos-eventuales' => ['GET', 'OPTIONS'],
                    'comprobante-pago' => ['GET', 'OPTIONS'],
                    'comprobante-pago-pdf' => ['GET', 'OPTIONS'],
                    'comprobante-pago-eventual' => ['GET', 'OPTIONS'],
                    'comprobante-pago-eventual-pdf' => ['GET', 'OPTIONS'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
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
            ->select([
                'contri_id',
                'ext_id',
                'sindi_id',
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
                'contri_estado',
            ])
            ->from('contribuyentes')
            ->where(['contri_estado' => 1])
            ->orderBy([
                'contri_nombres' => SORT_ASC,
                'contri_paterno' => SORT_ASC,
                'contri_materno' => SORT_ASC,
            ]);
        $id = Yii::$app->request->get('id');
        if ($id !== null && $id !== '') {
            return $query->andWhere(['contri_id' => $id])->one();
        }
        return $query->all();
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

    public function actionPagos()
    {
        $query = (new \yii\db\Query())
            ->select([
                'p.pago_id',
                'p.pago_estado',
                'p.pago_id_user_preliquidacion',
                'p.usua_id',
                'p.contri_id',
                'p.grad_id',
                'p.tip_arm_id',
                'p.pago_nro_comprobante',
                'p.pago_nro_liquidacion',
                'p.pago_tasa',
                'p.pago_longitud_modificada',
                'p.pago_descuento_porcentaje',
                'p.pago_descuento_monto',
                'p.pago_importe_patente',
                'p.pago_aseo',
                'p.pago_reposicion',
                'p.pago_importe_total',
                'p.pago_preliquidacion',
                'p.pago_fecha_hora_preliquidacion',
                'p.pago_fecha_hora_cobro',
                'p.pago_cobrado',
                'p.pago_con_exencion',
                'p.pago_anulado',
                'p.pago_anulado_detalle',
                'p.pago_anulado_fecha_hora',
                'p.pago_observaciones',

                'u.usua_nombres',
                'u.usua_apellidos',
                'u.usua_ci',
                'u.usua_cuenta',
                'u.usua_rol',
                'u.usua_estado',

                'c.contri_nombres',
                'c.contri_paterno',
                'c.contri_materno',
                'c.contri_ci',
                'c.contri_id',
                'c.ext_id',
                'c.sindi_id',
                'c.contri_apellidocasada',
                'c.contri_direccion',
                'c.contri_telefono',
                'c.contri_nit',
                'c.contri_fecharegistro',
                'c.contri_fechanac',
                'c.contri_sexo',
                'c.contri_estadocivil',
                'c.contri_estado',

                'gs.grad_codigo',
                'gs.grad_direccion',
                'gs.zona_id',
                'gs.gest_id',
                'gs.grad_propietario',
                'gs.grad_codigo_catastral',
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

                'ta.tip_arm_descricpion',
                'ta.tip_arm_patente',
                'ta.tip_arm_tasa_aseo',
                'ta.tip_arm_unidad_medida',
                'ta.tip_arm_estado',
            ])
            ->from(['p' => 'pagos'])
            ->leftJoin(['c' => 'contribuyentes'], 'c.contri_id = p.contri_id')
            ->leftJoin(['gs' => 'graderias_sillas'], 'gs.grad_id = p.grad_id')
            ->leftJoin(['z' => 'zonas'], 'z.zona_id = gs.zona_id')
            ->leftJoin(['ta' => 'tipo_armados'], 'ta.tip_arm_id = p.tip_arm_id')
            ->leftJoin(['u' => 'usuario'], 'u.usua_id = p.usua_id')
            ->where(['p.pago_estado' => 1])
            ->orderBy(['p.pago_fecha_hora_preliquidacion' => SORT_DESC, 'p.pago_id' => SORT_DESC]);

        $id = Yii::$app->request->get('id');
        if ($id !== null && $id !== '') {
            $row = $query->andWhere(['p.pago_id' => $id])->one();
            return $row === false ? null : $this->pagoPayload($row);
        }

        return array_map([$this, 'pagoPayload'], $query->all() );
    }

    public function actionPagosEventuales()
    {
        $query = (new \yii\db\Query())
            ->select([
                'pe.eventual_id',
                'pe.eventual_estado',
                'pe.eventual_user_id_preliquidacion',
                'pe.usua_id',
                'pe.contri_id',
                'pe.sitios_id',
                'pe.activi_id',
                'pe.eventual_nro_comprobante',
                'pe.eventual_nro_liquidacion',
                'pe.eventual_tasa',
                'pe.eventual_fecha_hora_pago',
                'pe.eventual_fecha_inicio',
                'pe.eventual_fecha_limite',
                'pe.eventual_cantidad_dia',
                'pe.eventual_importe_patente',
                'pe.eventual_costo_comprobante',
                'pe.eventual_costo_sentaje',
                'pe.eventual_costo_aseo',
                'pe.eventual_importe_total',
                'pe.eventual_preliquidacion',
                'pe.eventual_fecha_hora_liquidacion',
                'pe.eventual_cantidad_sitio',
                'pe.eventual_cobrado',
                'pe.eventual_anulado',
                'pe.eventual_anulado_detalle',
                'pe.eventual_anulado_fecha_hora',
                'pe.eventual_descripcion',

                'u.usua_nombres',
                'u.usua_apellidos',
                'u.usua_ci',
                'u.usua_cuenta',
                'u.usua_rol',
                'u.usua_estado',

                'c.contri_nombres',
                'c.contri_paterno',
                'c.contri_materno',
                'c.contri_ci',
                'c.contri_id',
                'c.ext_id',
                'c.sindi_id',
                'c.contri_apellidocasada',
                'c.contri_direccion',
                'c.contri_telefono',
                'c.contri_nit',
                'c.contri_fecharegistro',
                'c.contri_fechanac',
                'c.contri_sexo',
                'c.contri_estadocivil',
                'c.contri_estado',

                's.sitios_codigo',
                's.sitios_descripcion',
                's.sitios_numero_sitio',
                's.sitios_vendido',
                's.sitios_es_alasita',
                's.sitios_estado',

                'a.activi_descripcion',
                'a.categ_id',
                'a.activi_largo_mts',
                'a.activi_ancho_mts',
                'a.activi_superficie',
                'a.activi_costo_patente',
                'a.activi_costo_sentaje_dia',
                'a.activi_costo_aseo_por_dia',
                'a.activi_costo_aseo_por_sitio',
                'a.activi_cobro_por_dia',
                'a.activi_estado',

                'cat.categ_id',
                'cat.categ_nombre',
                'cat.categ_codigo',
                'cat.categ_estado',
            ])
            ->from(['pe' => 'pagos_eventuales'])
            ->leftJoin(['c' => 'contribuyentes'], 'c.contri_id = pe.contri_id')
            ->leftJoin(['s' => 'sitios_eventuales'], 's.sitios_id = pe.sitios_id')
            ->leftJoin(['a' => 'actividades_economicas'], 'a.activi_id = pe.activi_id')
            ->leftJoin(['cat' => 'categorias'], 'cat.categ_id = a.categ_id')
            ->leftJoin(['u' => 'usuario'], 'u.usua_id = pe.usua_id')
            ->where(['pe.eventual_estado' => 1])
            ->orderBy(['pe.eventual_fecha_hora_liquidacion' => SORT_DESC, 'pe.eventual_id' => SORT_DESC]);

        $id = Yii::$app->request->get('id');
        if ($id !== null && $id !== '') {
            $row = $query->andWhere(['pe.eventual_id' => $id])->one();
            return $row === false ? null : $this->pagoEventualPayload($row);
        }

        return array_map([$this, 'pagoEventualPayload'], $query->all());
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

        return $this->generarReportePdf('reportes', 'comprobante_graderia_silla', [
            'id_pago' => $id,
            'monto_literal' => '"' . $model->montoTotalLiteral() . '"',
            'image_path' => '"' . Yii::getAlias('@webroot') . '"',
        ]);
    }

    private function generarComprobanteEventualPdf($id)
    {
        $model = PagosEventuales::findOne($id);

        if ($model === null || (int)$model->eventual_estado !== 1) {
            throw new NotFoundHttpException('El pago eventual no existe o no esta activo.');
        }

        return $this->generarReportePdf('reportes', 'comprobante_eventuales2', [
            'id_pago' => $id,
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

    private function pagoPayload(array $row)
    {
        $row['contribuyente'] = $this->contribuyentePayload($row);
        $row['usuario'] = $this->usuarioPayload($row, 'usua_id');
        $row['usuario_preliquidacion'] = $this->usuarioByIdPayload($this->nullableInt($row, 'pago_id_user_preliquidacion'));
        $row['graderia_silla'] = $this->graderiaSillaRowPayload($row);
        $row['tipo_armado'] = $this->tipoArmadoPayload($row);
        $this->unsetKeys($row, [
            'observaciones',
            'contri_nombres',
            'contri_paterno',
            'contri_materno',
            'contri_ci',
            'ext_id',
            'sindi_id',
            'contri_apellidocasada',
            'contri_direccion',
            'contri_telefono',
            'contri_nit',
            'contri_fecharegistro',
            'contri_fechanac',
            'contri_sexo',
            'contri_estadocivil',
            'contri_estado',
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
            'usua_nombres',
            'usua_apellidos',
            'usua_ci',
            'usua_cuenta',
            'usua_rol',
            'usua_estado',
        ]);

        return $row;
    }

    private function pagoEventualPayload(array $row)
    {
        $row['contribuyente'] = $this->contribuyentePayload($row);
        $row['usuario'] = $this->usuarioPayload($row, 'usua_id');
        $row['usuario_preliquidacion'] = $this->usuarioByIdPayload($this->nullableInt($row, 'eventual_user_id_preliquidacion'));
        $row['sitio_eventual'] = $this->sitioEventualPayload($row);
        $row['actividad_economica'] = $this->actividadEconomicaPayload($row);
        $this->unsetKeys($row, [
            'observaciones',
            'contri_nombres',
            'contri_paterno',
            'contri_materno',
            'contri_ci',
            'ext_id',
            'sindi_id',
            'contri_apellidocasada',
            'contri_direccion',
            'contri_telefono',
            'contri_nit',
            'contri_fecharegistro',
            'contri_fechanac',
            'contri_sexo',
            'contri_estadocivil',
            'contri_estado',
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
            'usua_nombres',
            'usua_apellidos',
            'usua_ci',
            'usua_cuenta',
            'usua_rol',
            'usua_estado',
        ]);

        return $row;
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

    private function ensureWriteMethod($allowedMethods)
    {
        if (!in_array(Yii::$app->request->method, $allowedMethods, true)) {
            throw new MethodNotAllowedHttpException('Metodo no permitido.');
        }
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
}
