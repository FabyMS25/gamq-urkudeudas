<?php

namespace app\commands;

use app\models\Contribuyentes;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class RuatController extends Controller
{
    public function actionSyncContribuyentes($username, $password, $limit = 0)
    {
        $token = Yii::$app->ruatServices->login($username, $password);

        if (!$token) {
            $this->stderr("No se pudo autenticar en RUAT.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $query = Contribuyentes::find()
            ->where(['contri_estado' => 1])
            ->andWhere(['or', ['contri_codigo_ruat' => null], ['contri_codigo_ruat' => '']])
            ->orderBy(['contri_id' => SORT_ASC]);

        if ((int)$limit > 0) {
            $query->limit((int)$limit);
        }

        $processed = 0;
        $updated = 0;
        $notFound = 0;
        $errors = 0;

        foreach ($query->each() as $model) {
            $processed++;
            $tipoDocumento = (int)$model->ext_id === 12 ? 'CE' : 'CI';
            $response = Yii::$app->ruatServices->getContribuyentePorCiResponse(
                $token,
                $model->contri_ci,
                $tipoDocumento
            );

            if (!$response || !isset($response->codigoContribuyente)) {
                $notFound++;
                $this->stdout("SIN RUAT: {$model->contri_id} {$model->contri_ci}\n");
                continue;
            }

            $this->applyRuatData($model, $response);

            if ($model->save(false)) {
                $updated++;
                $this->stdout("OK: {$model->contri_id} {$model->contri_ci} {$model->contri_codigo_ruat}\n");
            } else {
                $errors++;
                $this->stderr("ERROR DB: {$model->contri_id} {$model->contri_ci}\n");
            }
        }

        $this->stdout("Procesados: $processed, actualizados: $updated, sin RUAT: $notFound, errores: $errors\n");

        return $errors > 0 ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }

    private function applyRuatData(Contribuyentes $model, $response)
    {
        $contribuyente = isset($response->contribuyente) ? $response->contribuyente : null;

        $model->contri_codigo_ruat = $response->codigoContribuyente;
        $model->contri_tipo_contribuyente_ruat = $contribuyente && isset($contribuyente->tipoContribuyente)
            ? $contribuyente->tipoContribuyente
            : null;
        $model->contri_tipo_documento_ruat = $contribuyente && isset($contribuyente->tipoDocumento)
            ? $contribuyente->tipoDocumento
            : null;
        $model->contri_estado_ruat = $contribuyente && isset($contribuyente->estado)
            ? $contribuyente->estado
            : null;
        $model->contri_ruat_sync_at = date('Y-m-d H:i:s');
        $model->contri_ruat_payload = json_encode($response);
    }
}
