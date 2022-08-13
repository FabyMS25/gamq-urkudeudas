<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class ComprobanteForm extends Model
{
    public $nro_comprobante;
    
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['nro_comprobante'], 'required'],
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     * @param string $email the target email address
     * @return boolean whether the model passes validation
     */
    public function findComprobante($nro_comprobante, $id_pago)
    {
        $sqlQuery = "SELECT pago_nro_comprobante, contri_nombres, contri_paterno, contri_materno,
        pago_longitud_modificada, pago_importe_patente, pago_aseo, pago_reposicion,
        pago_importe_total, pago_fecha_hora_cobro, grad_codigo, zona_nombre, 
        grad_tipo_sitio, grad_acera, grad_direccion, tip_arm_descricpion, usua_id
        FROM view_graderias_sillas
        WHERE pago_nro_comprobante = ".$nro_comprobante." OR pago_id = ".$id_pago;
        //WHERE pago_nro_comprobante = 7891111";
        $command = Yii::$app->db->createCommand($sqlQuery);
                                //  ->bindValue(':id', $id_pago)
                                //  ->bindValue(':comprobante', $nro_comprobante);
        $resultSet = $command->queryAll();
        //$resultSet = null;
        // if ($nro_comprobante) {
        //     $command = Yii::$app->db->createCommand($sqlQuery);
        //     $resultSet = $command->queryAll();
        // }

        return $resultSet;
    }

    public function getNroComprobante() {
        return $this->nro_comprobante;
    }
}
