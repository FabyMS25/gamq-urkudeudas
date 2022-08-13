<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\base\Model;
use app\models\ComprobanteForm;
use yii\bootstrap\ActiveForm;

$this->title = 'Comprobantes';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-contact">
    <?php

    $id_pago = Yii::$app->getRequest()->get('id');
    $nro_comprobante = Yii::$app->getRequest()->get('nro_comprobante');
    
    $post = Yii::$app->request->post(); 
    
    if (count($post) > 0 || $id_pago != null):  
        $nroComprobante = $post["ComprobanteForm"]["nro_comprobante"];

        $sqlQuery = "SELECT pago_nro_comprobante, (contri_nombres||' '||contri_paterno||' '|| contri_materno) AS contribuyente,
        pago_longitud_modificada, pago_importe_patente, pago_aseo, pago_reposicion,
        pago_importe_total, pago_fecha_hora_cobro, grad_codigo, zona_nombre, 
        grad_tipo_sitio, grad_acera, grad_direccion, tip_arm_descricpion,
        (usuario.usua_nombres||' '||usuario.usua_apellidos) AS cajero
        FROM view_graderias_sillas
        INNER JOIN usuario ON usuario.usua_id = view_graderias_sillas.usua_id
        WHERE pago_nro_comprobante = ".$nroComprobante;

        if (!empty($id_pago)):
            $sqlQuery = $sqlQuery." OR pago_id = ".$id_pago;
        endif;  
            
        $command = Yii::$app->db->createCommand($sqlQuery);
        
        $resultSet = $command->queryAll();

        //var_dump($sqlQuery);
    ?>
        <!-- <div class="modal fade" id="myModal" role="dialog"> 
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="panel panel-default p1">
                            <div class="panel-heading">
                                <strong class="">Detalle de verificación</strong>
                            </div> 
        
                            <php echo $this->render('graderias', ['resultSet' => $resultSet]) ?>
        
                        </div>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div> -->
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-sm-8 col-md-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong class="">Detalles de preliquidación</strong>
                        </div>
                        <div class="panel-body">
                        <?php echo $this->render('graderias', ['resultSet' => $resultSet]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-sm-4 col-md-offset-3">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong class="">Verificar Comprobantes</strong>
                        </div>
                        <div class="panel-body">
                            <p>
                                Verifique los datos según el número de Comprobante.
                            </p>

                            <?php $form = ActiveForm::begin([
                                'method' => 'post', 
                                'action' => ['site/comprobante']]); ?>

                            <?= $form->field($model, 'nro_comprobante')->textInput() ?>

                            <div class="form-group">
                                <?= Html::submitButton('Ver detalles', ['class' => 'btn btn-primary']) ?>
                                <!-- <= Html::a('Ver detalles', ['site/comprobante', 'nro_comprobante' => $model], ['class' => 'profile-link']) ?> -->
                            </div>

                            <?php $form = ActiveForm::end(); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>