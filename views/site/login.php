<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;

$this->title = 'Autentificación';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin([
        'id' => 'login-form',
    ]); ?>



<div class="container">
    <div class="row">
        <div class="col-md-4 col-sm-4 col-md-offset-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong class="">Autentificación</strong>
                </div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                             <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
                        </div>
                        <div class="form-group">
                             <?= $form->field($model, 'password')->passwordInput() ?>
                        </div>
                        
                        <div class="form-group">
                             <?= $cod ?>
                        </div>

                        <div class="form-group">
                             <?= $form->field($model, 'cod')->textInput() ?>
                        </div>


                        <div class="form-group last">
                            <div class="col-md-4 col-sm-4 col-md-offset-4">
                                <?= Html::submitButton('Ingresar', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                            </div>
                        </div>
                    </form>
                </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>