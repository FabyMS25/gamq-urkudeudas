<?php
use yii\helpers\Html;
//use yii\bootstrap\Modal;
?>

<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}

</style>

<!-- <div class="modal fade" id="myModal" role="dialog"> -->
    <!-- <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="panel panel-default p1">
                    <div class="panel-heading">
                        <strong class="">Detalle de verificación</strong>
                    </div> -->
                    <div class="panel-body">
                        <div class="row">
                            <table>
                                <tr>
                                    <th width="40%">Concepto</th>
                                    <th width="60%">Resultado</th>
                                </tr>
                                <?php 
                                    if ($resultSet) {
                                        foreach ($resultSet as $row):                 
                                ?>
                                <tr>
                                    <td><strong>Comprobante</strong></td>
                                    <td><?= $row['pago_nro_comprobante'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Contribuyente</strong></td>
                                    <td><?= $row['contribuyente'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Código gradería</strong></td>
                                    <td><?= $row['grad_codigo'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Longitud comprada (metros)</strong></td>
                                    <td><?= $row['pago_longitud_modificada'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Patente Bs.</strong></td>
                                    <td><?= $row['pago_importe_patente'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tasa Bs.</strong></td>
                                    <td><?= $row['pago_aseo'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Reposición Bs.</strong></td>
                                    <td><?= $row['pago_reposicion'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Importe Total Bs.</strong></td>
                                    <td><?= $row['pago_importe_total'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Fecha de pago</strong></td>
                                    <td><?= $row['pago_fecha_hora_cobro'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Cajero</strong></td>
                                    <td><?= $row['cajero'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Zona</strong></td>
                                    <td><?= $row['zona_nombre'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Acera</strong></td>
                                    <td><?= $row['grad_acera'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Dirección</strong></td>
                                    <td><?= $row['grad_direccion'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tipo sitio</strong></td>
                                    <td><?= $row['grad_tipo_sitio'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Sitio descripción</strong></td>
                                    <td><?= $row['tip_arm_descricpion'] ?></td>
                                </tr>
                                <?php 
                                    endforeach;
                                }
                                ?>
                            </table>
                        </div>
                    </div>
                <!-- </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div> -->
<!-- </div> -->
