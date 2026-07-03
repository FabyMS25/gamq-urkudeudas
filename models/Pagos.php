<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pagos".
 *
 * @property integer $pago_id
 * @property integer $grad_id
 * @property integer $usua_id
 * @property integer $contri_id
 * @property integer $tip_arm_id
 * @property string $pago_nro_liquidacion
 * @property string $pago_longitud_modificada
 * @property integer $pago_nro_comprobante
 * @property string $pago_descuento_porcentaje
 * @property string $pago_descuento_monto
 * @property string $pago_importe_patente
 * @property string $pago_aseo
 * @property string $pago_reposicion
 * @property string $pago_importe_total
 * @property integer $pago_anulado
 * @property string $pago_anulado_detalle
 * @property string $pago_anulado_fecha_hora
 * @property integer $pago_id_user_preliquidacion
 * @property integer $pago_preliquidacion
 * @property string $pago_fecha_hora_preliquidacion
 * @property string $pago_fecha_hora_cobro
 * @property integer $pago_cobrado
 * @property integer $pago_estado
 * @property integer $pago_tasa
 * @property Contribuyentes $contri
 * @property GraderiasSillas $grad
 * @property TipoArmados $tipArm
 * @property Usuario $usua
 */
class Pagos extends \yii\db\ActiveRecord
{
    public $longitud;
    public $codigo;
    public $nombre;
    public $paterno;
    public $materno;
    public $ci;
    public $fecha_rango, $tipo;
    
    public static function getComprobanteCosto()
    {
        return Yii::$app->params['costos']['comprobante'];
    }

    public static function tableName()
    {
        return 'pagos';
    }

    public function beforeValidate()
    {
        foreach ($this->decimalAttributes() as $attribute) {
            $this->$attribute = $this->normalizeDecimalValue($this->$attribute);
        }

        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'grad_id', 'usua_id', 'contri_id', 'tip_arm_id', 'pago_nro_comprobante', 'pago_anulado', 'pago_id_user_preliquidacion',
                'pago_preliquidacion', 'pago_cobrado', 'pago_estado'
            ], 'integer'],
            [[
                'contri_id', 'tip_arm_id',  'pago_aseo', 'pago_reposicion',
                'pago_fecha_hora_preliquidacion',  'pago_estado'
            ], 'required'],
            [[
                'pago_longitud_modificada', 'pago_descuento_porcentaje', 'pago_descuento_monto', 'pago_importe_patente', 'pago_aseo',
                'pago_reposicion', 'pago_importe_total'
            ], 'number'],
            [['pago_anulado_fecha_hora', 'pago_fecha_hora_preliquidacion', 'pago_fecha_hora_cobro'], 'safe'],
            [['pago_nro_liquidacion'], 'string', 'max' => 15],
            [['pago_anulado_detalle'], 'string', 'max' => 250],
            [['pago_observaciones'], 'string'],
            //
            //'usua_id'
            // personlaizado
            [['pago_descuento_porcentaje'], 'number', 'min' => 0, 'max' => 100],
            [['pago_longitud_modificada'], 'number', 'min' => 0,],
            [['pago_longitud_modificada'], 'required'],
            [['contri_id'], 'exist', 'skipOnError' => true, 'targetClass' => Contribuyentes::className(), 'targetAttribute' => ['contri_id' => 'contri_id']],
            [['grad_id'], 'exist', 'skipOnError' => true, 'targetClass' => GraderiasSillas::className(), 'targetAttribute' => ['grad_id' => 'grad_id']],
            [['tip_arm_id'], 'exist', 'skipOnError' => true, 'targetClass' => TipoArmados::className(), 'targetAttribute' => ['tip_arm_id' => 'tip_arm_id']],
            [['usua_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuario::className(), 'targetAttribute' => ['usua_id' => 'usua_id']],

            //personalizado           
            [['pago_importe_total', 'pago_importe_patente', 'pago_con_exencion'], 'required'],
            ['pago_tasa', 'required', 'on' => ['cobrar_graderias_sillas']],
            //['pago_nro_comprobante', 'validateComprobante' ,'on'=>['cobrar_graderias_sillas']],

            ['pago_anulado_detalle', 'required', 'on' => ['anular-pago']],
            // para reportes
            [['fecha_rango', 'tipo'], 'required', 'on' => ['reporte_pagos_anulados']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pago_id' => 'Pago ID',
            'grad_id' => 'Codigo Grad/Silla',
            'usua_id' => 'Cajero',
            'contri_id' => 'Contribuyentes', // id
            'tip_arm_id' => 'Tipo de armado',
            'pago_nro_liquidacion' => 'N° preliquidacion',
            'pago_longitud_modificada' => 'Longitud a Vender(metros lineales)',
            'pago_nro_comprobante' => 'Comprobante',
            //'pago_descuento_porcentaje' => 'Descuento Porcentaje(%)',
            //'pago_descuento_monto' => 'Descuento Bs.',
            'pago_importe_patente' => 'Patente Bs.',
            'pago_aseo' => 'Tasa de aseo',
            'pago_reposicion' => 'Comprobante',
            'pago_importe_total' => 'Importe Total Bs.',
            'pago_anulado' => 'Anulado',
            'pago_anulado_detalle' => 'Justificacion de la anulacion',
            'pago_anulado_fecha_hora' => 'Fecha hora anulacion',
            'pago_id_user_preliquidacion' => 'Preliquidador', //Cajero
            'pago_preliquidacion' => 'Preliquidacion',
            'pago_fecha_hora_preliquidacion' => 'Fecha/hora preliq.',
            'pago_fecha_hora_cobro' => 'Fecha y hora cobro',
            'pago_cobrado' => 'Pagado',
            'pago_estado' => 'Pago Estado',
            'pago_con_exencion' => 'Exencion',
            'pago_observaciones' => 'Observaciones',
            // personalizado           
            'longitud' => 'Longitud Disponible(metros lineales)',
            'codigo' => 'Codigo',
            'nombre' => 'Nombres',
            'paterno' => 'Ap. paterno',
            'materno' => 'Ap. materno',
            'ci' => 'Doc. identidad',
            'pago_tasa' => 'Tasa de ruat',
        ];
    }

    private function decimalAttributes()
    {
        return [
            'pago_longitud_modificada',
            'pago_descuento_porcentaje',
            'pago_descuento_monto',
            'pago_importe_patente',
            'pago_aseo',
            'pago_reposicion',
            'pago_importe_total',
        ];
    }

    private function normalizeDecimalValue($value)
    {
        $value = trim((string)$value);

        if ($value === '') {
            return $value;
        }

        $value = str_replace(' ', '', $value);
        $hasComma = strpos($value, ',') !== false;
        $hasDot = strpos($value, '.') !== false;

        if ($hasComma && $hasDot) {
            if (strrpos($value, ',') > strrpos($value, '.')) {
                return str_replace(',', '.', str_replace('.', '', $value));
            }

            return str_replace(',', '', $value);
        }

        if ($hasComma) {
            return str_replace(',', '.', $value);
        }

        return $value;
    }

    /**
     * Get the human-readable estado (status) of a pago
     * Combines pago_cobrado and pago_anulado to determine status
     */
    public function getEstadoText()
    {
        if ($this->pago_anulado == 1) {
            return 'Anulada';
        } elseif ($this->pago_cobrado == 1) {
            return 'Pagada';
        } else {
            return 'Pendiente';
        }
    }

    /**
     * Get the CSS badge class for the estado
     */
    public function getEstadoBadgeClass()
    {
        if ($this->pago_anulado == 1) {
            return 'badge-danger';
        } elseif ($this->pago_cobrado == 1) {
            return 'badge-success';
        } else {
            return 'badge-warning';
        }
    }

    /**
     * Get array of all available estados for filtering
     */
    public static function getEstadoOptions()
    {
        return [
            '' => '-- Todas --',
            'pendiente' => 'Pendiente',
            'pagada' => 'Pagada',
            'anulada' => 'Anulada',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContribuyente()
    {
        return $this->hasOne(Contribuyentes::className(), ['contri_id' => 'contri_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGraderiaSilla()
    {
        return $this->hasOne(GraderiasSillas::className(), ['grad_id' => 'grad_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTipoArmado()
    {
        return $this->hasOne(TipoArmados::className(), ['tip_arm_id' => 'tip_arm_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuario::className(), ['usua_id' => 'usua_id']);
    }

    // validaciones personalizados
    public function validateComprobante($attribute, $params, $validator)
    {
        $comprobante = $this->pago_nro_comprobante;
        $total = Pagos::find()->select('pago_nro_comprobante')
            ->where(['pago_estado' => 1, 'pago_nro_comprobante' => $comprobante])->count();

        if ($total > 0) {
            $this->addError($attribute, 'El comprobante "' . $comprobante . '" ya existe.');
        }
    }


    // lista de graderias y sillas preliquidados
    public  function listaIdGraderiasSillasPreliquidados()
    {
        return $this->find()
            ->select('grad_id')
            ->where(['pago_estado' => 1, 'pago_preliquidacion' => 1])
            ->column();
    }

    public function montoTotalLiteral()
    {
        $montoTotal = $this->pago_importe_total;
        $modelAux = new NumeroALetras();
        return $modelAux->convertir($montoTotal);
    }
}
