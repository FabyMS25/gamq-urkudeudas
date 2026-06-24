<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pagos_infracciones".
 *
 * @property integer $infraccion_id
 * @property integer $usua_id
 * @property integer $contri_id
 * @property string $codigo_usuario
 * @property string $codigo_contribuyente
 * @property string $numero_documento
 * @property string $tipo_documento
 * @property string $expedido
 * @property string $tipo_infraccion
 * @property string $descripcion_infraccion
 * @property string $lugar_infraccion
 * @property string $fecha_infraccion
 * @property string $gestion
 * @property string $codigo_clasificador
 * @property string $monto
 * @property string $observacion
 * @property string $numero_tasa
 * @property integer $infraccion_estado
 * @property integer $infraccion_pagado
 * @property integer $infraccion_anulado
 * @property string $fecha_pago
 * @property string $pago_ruat_payload
 * @property string $registro_ruat_payload
 * @property string $anulado_motivo
 * @property string $anulado_observacion
 * @property string $anulado_fecha_hora
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Contribuyentes $contribuyente
 * @property Usuario $usuario
 */
class PagosInfracciones extends \yii\db\ActiveRecord
{
    const ESTADO_ACTIVO = 1;
    const ESTADO_ANULADO = 0;

    public static function tableName()
    {
        return 'pagos_infracciones';
    }

    public function rules()
    {
        return [
            [[
                'codigo_usuario',
                'codigo_contribuyente',
                'numero_documento',
                'tipo_documento',
                'tipo_infraccion',
                'codigo_clasificador',
                'monto',
                'observacion',
                'numero_tasa',
                'created_at',
                'updated_at',
            ], 'required'],
            [['usua_id', 'contri_id', 'infraccion_estado', 'infraccion_pagado', 'infraccion_anulado'], 'integer'],
            [['monto'], 'number', 'min' => 0.01],
            [['descripcion_infraccion', 'observacion', 'pago_ruat_payload', 'registro_ruat_payload', 'anulado_observacion'], 'string'],
            [['fecha_infraccion', 'fecha_pago', 'anulado_fecha_hora', 'created_at', 'updated_at'], 'safe'],
            [['codigo_usuario', 'codigo_contribuyente', 'codigo_clasificador', 'numero_tasa'], 'string', 'max' => 64],
            [['numero_documento', 'expedido'], 'string', 'max' => 20],
            [['tipo_documento'], 'string', 'max' => 2],
            [['tipo_infraccion'], 'string', 'max' => 100],
            [['lugar_infraccion', 'anulado_motivo'], 'string', 'max' => 250],
            [['gestion'], 'string', 'max' => 10],
            [['numero_tasa'], 'unique'],
            [['tipo_documento'], 'in', 'range' => ['CI', 'CE']],
            [['infraccion_pagado', 'infraccion_anulado'], 'default', 'value' => 0],
            [['infraccion_estado'], 'default', 'value' => self::ESTADO_ACTIVO],
            [['contri_id'], 'exist', 'skipOnError' => true, 'targetClass' => Contribuyentes::className(), 'targetAttribute' => ['contri_id' => 'contri_id']],
            [['usua_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::className(), 'targetAttribute' => ['usua_id' => 'usua_id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'infraccion_id' => 'Infraccion ID',
            'usua_id' => 'Usuario',
            'contri_id' => 'Contribuyente',
            'codigo_usuario' => 'Codigo Usuario RUAT',
            'codigo_contribuyente' => 'Codigo Contribuyente RUAT',
            'numero_documento' => 'Numero Documento',
            'tipo_documento' => 'Tipo Documento',
            'expedido' => 'Expedido',
            'tipo_infraccion' => 'Tipo Infraccion',
            'descripcion_infraccion' => 'Descripcion Infraccion',
            'lugar_infraccion' => 'Lugar Infraccion',
            'fecha_infraccion' => 'Fecha Infraccion',
            'gestion' => 'Gestion',
            'codigo_clasificador' => 'Codigo Clasificador',
            'monto' => 'Monto',
            'observacion' => 'Observacion',
            'numero_tasa' => 'Numero Tasa',
            'infraccion_estado' => 'Estado',
            'infraccion_pagado' => 'Pagado',
            'infraccion_anulado' => 'Anulado',
            'fecha_pago' => 'Fecha Pago',
            'pago_ruat_payload' => 'Datos Pago RUAT',
            'registro_ruat_payload' => 'Datos Registro RUAT',
            'anulado_motivo' => 'Motivo Anulacion',
            'anulado_observacion' => 'Observacion Anulacion',
            'anulado_fecha_hora' => 'Fecha Anulacion',
            'created_at' => 'Fecha Registro',
            'updated_at' => 'Fecha Actualizacion',
        ];
    }

    public function getContribuyente()
    {
        return $this->hasOne(Contribuyentes::className(), ['contri_id' => 'contri_id']);
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuario::className(), ['usua_id' => 'usua_id']);
    }

    public function montoTotalLiteral()
    {
        $modelAux = new NumeroALetras();
        return $modelAux->convertir($this->monto);
    }
}
