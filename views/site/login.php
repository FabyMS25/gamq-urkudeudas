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

                        <?php 
                         function generateCod($n) {
                            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                            $randomString = '';
                            for ($i = 0; $i < $n; $i++) {
                                $index = rand(0, strlen($characters) - 1);
                                $randomString .= $characters[$index];
                            }
                            return $randomString;
                         }
                        ?>

                        <div class="form-group"> 
                            <input type="text" class="captcha" 
                                    readonly disabled value="<?=generateCod(4);?>" name="cadena" id="cadena" /> 
                            <button type="button" id="actualizar" value="Actualizar" >
                                <span style='font-size:40px;' class="icon">&#128259;</span>    
                            </button>
                        </div>
                        <div class="form-group">
                            <label for="text">Captcha</label>
                            <input class="form-control" id="txt" type="text" required>
                        </div>

                        <div class="form-group last">
                            <div class="col-md-4 col-sm-4 col-md-offset-4">
                                <?= 
                                    Html::submitButton('Ingresar', ['id'=> 'enviar', 'class' => 'btn btn-primary',
                                    'name' => 'login-button', 'disabled' => 'disabled']);                 
                                ?>
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

<style>
    .captcha {
        border: none; text-align: center; background-color: #632127; 
        color: white; font-size:35px;
        width: 250px;
    }
    #actualizar {
        border: none;
        background-color: white;
        margin: 0;
        padding: 0;
    }
</style>

<script type="text/javascript">
        var boton=document.getElementById('actualizar'),
            cadena= document.getElementById('cadena'),
            intro= document.getElementById('txt');

      function Textrandom(length) {
          key="";
          str = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
          for(i=0; i<length; i++) {
            var h = parseInt(Math.random() * 35);
            key =key+str[h];
          }
          return key;
      }

        function verificar() {  
           if (cadena.value==intro.value) {
              document.getElementById('enviar').disabled=false;
           }
        }
        function obtener() {
          cadena.value=Textrandom(4);                  
        }

        document.getElementById('actualizar').onclick= function(){
          obtener();
        };

        document.getElementById('txt').onkeyup= function(){
          verificar();
        };
      </script>