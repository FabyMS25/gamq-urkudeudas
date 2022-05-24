<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\models\Usuario;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
        <?php $this->beginBody() ?>

        <div class="wrap">
            <?php
            NavBar::begin
                    (
                    [
                        'brandLabel' => 'URKUPIÑA', // '<img src="' . Url::to('@web/img/sisim_small.png') . '" style="width:48px;">',
                        'brandUrl' => Yii::$app->homeUrl,
                        'options' => [
                            'class' => 'navbar-inverse navbar-fixed-top',
                        ],
                    ]
            );


            echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right'],  
            'items' => [
                
                ['label' => 'Principal', 'url' => ['/site/index']],
                
                [
                    'label' => 'Parametros',
                    'visible' => !Yii::$app->user->isGuest && Usuario::getRolAdmin(),
                    'items' =>
                        [
                            ['label' => 'Gestiones', 'url' => ['/gestiones/index'],  'visible' => !Yii::$app->user->isGuest],
                            ['label' => 'Expedido', 'url' => ['/extensiones/index'], 'visible' => !Yii::$app->user->isGuest],
                            ['label' => 'Sindicatos', 'url' => ['/sindicatos/index'],'visible' => !Yii::$app->user->isGuest],
                            ['label' => 'Contribuyentes', 
                                'url' => ['/contribuyentes/index'], 
                                'visible' => !Yii::$app->user->isGuest
                            ],
                            ['label' => 'Usuarios', 'url' => ['/usuario/index'], 'visible' => !Yii::$app->user->isGuest],
                             '<li class="divider"></li>',
                            '<li class="dropdown-header">Sillas y graderias</li>',
                            ['label' => 'Zonas', 'url' => ['/zonas/index']],
                            '<li class="divider"></li>',
                            '<li class="dropdown-header">Actividades eventuales</li>',
                            ['label' => 'Sitios eventuales', 'url' => ['/sitios-eventuales/index'],  'visible' => !Yii::$app->user->isGuest,],
                            ['label' => 'Categorias', 'url' => ['/categorias/index']],
                            ['label' => 'Actividades economicas ', 'url' => ['/actividades-economicas/index'],  'visible' => !Yii::$app->user->isGuest,],
                            
                            
                        ]
                ] ,
                
                ['label' => 'Contribuyentes', 
                    'url' => ['/contribuyentes/index'], 
                    'visible' => !Yii::$app->user->isGuest && Usuario::getRolPreli(),
                ],
                // SITIOS PARA SILLAS Y GRADERIAS
               
                [
                    'label' => 'Sillas y graderias',
                    'visible' => !Yii::$app->user->isGuest,
                    'items' =>
                        [                
                       
                            ['label' => 'Preliquidar', 
                                'url' => ['/pagos/index'],
                                'visible' => !Yii::$app->user->isGuest && (Usuario::getRolAdmin() or Usuario::getRolPreli()),
                                ],
                            ['label' => 'Preliquidaciones', 
                                'url' => ['/pagos/preliquidaciones'],
                                'visible' => !Yii::$app->user->isGuest,
                                ],
                            ['label' => 'Pagados',
                                'url' => ['/pagos/pagados'],
                                'visible' => !Yii::$app->user->isGuest && (Usuario::getRolAdmin() or Usuario::getRolCajero())],
                            ['label' => 'Anulados', 
                                'url' => ['/pagos/anulados'],
                                'visible' => !Yii::$app->user->isGuest && (Usuario::getRolAdmin() or Usuario::getRolCajero())],
                            /*['label' => 'General',
                                'url' => ['/pagos/general'],
                                'visible' => !Yii::$app->user->isGuest && Usuario::getRolAdmin(),
                                ]*/
                        ],
                ] ,
                // ACTIVIDADE EVENTUALES              
                [
                    'label' => 'Eventuales',
                     'visible' => !Yii::$app->user->isGuest,
                    'items' =>
                        [                            
                            ['label' => 'Preliquidar', 
                                'url' => ['/pagos-eventuales/eventuales-alasitas'], 
                                'visible' => !Yii::$app->user->isGuest && (Usuario::getRolAdmin() or Usuario::getRolPreli()),
                                ],
                            ['label' => 'Preliquidaciones',
                                'url' => ['/pagos-eventuales/index'], 
                                'visible' => !Yii::$app->user->isGuest,
                                ],
                            ['label' => 'Pagados', 
                                'url' => ['/pagos-eventuales/pagados'], 
                                'visible' => !Yii::$app->user->isGuest && (Usuario::getRolAdmin() or Usuario::getRolCajero()),
                                ],
                            ['label' => 'Anulados',
                                'url' => ['/pagos-eventuales/anulados'], 
                                'visible' => !Yii::$app->user->isGuest && (Usuario::getRolAdmin() or Usuario::getRolCajero()),
                                ]
                        ],
                ] ,
                
                // SENTAJES               
                [
                    'label' => 'Sentajes',
                    'visible' => !Yii::$app->user->isGuest && (Usuario::getRolAdmin()),
                    'items' =>
                    [
                        ['label' => 'Razon social', 'url' => ['/razon-sociales/index'], 'visible' => !Yii::$app->user->isGuest,],
                        ['label' => 'Descargos', 'url' => ['/descargos/index'], 'visible' => !Yii::$app->user->isGuest,]
                    ],
                ],
                [
                'label' => 'Reportes',
                'visible' => !Yii::$app->user->isGuest && (Usuario::getRolAdmin()),
                    'items' =>
                    [
                        ['label' => 'Pagos y anulados de graderia o sillas',
                        'url' => ['pagos/reporte-general'],
                        'linkOptions' => ['role' => 'modal-remote'],
                        'visible' => !Yii::$app->user->isGuest && (Usuario::getRolAdmin()),
                        ],
               
                    ],
                ],
                
                [
                    'label' => 'Iniciar sesión',
                    'url' => ['/site/login'],
                    'visible' => Yii::$app->user->isGuest
                ],
                (!Yii::$app->user->isGuest)?(

                 [
                    'label' => 'Mi cuenta',
                    'visible' => !Yii::$app->user->isGuest,
                    'items' =>
                        [

                            '<li>'
                            . Html::beginForm(['/site/logout'], 'post', ['class' => 'navbar-form'])
                                . Html::submitButton(
                                'Cerrar sesión (' . Yii::$app->user->identity->username . ' - ' . Yii::$app->user->identity->gestionLiteral . ')', 
                                  ['class' => 'btn ']
                                )
                            . Html::endForm()
                            . '</li>',
                            //['label' => 'Cambiar contraseña', 'url' => ['/usuario/update-pass'],  'visible' => !Yii::$app->user->isGuest],
                        ]
                 ]):"",          
                
                ],
            'options' => ['class' => 'nav navbar-nav'],
            ]);
            NavBar::end();
            ?>

            <div class="container" style="width: 98%">
            <?=
            Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ])
            ?>
                <?= $content ?>
            </div>
        </div>

        <footer class="footer">
            <div class="container" >
                <p class="pull-left">&copy; Gobierno Autónomo Municipal de Quillacollo</p>
                <p class="pull-right"><?= date('Y') ?></p>
            </div>
        </footer>



<?php
yii\bootstrap\Modal::begin([
    'header' => '<span id="modalHeaderTitle"></span>',
    'headerOptions' => ['id' => 'modalHeader'],
    'id' => 'modal',
    'size' => 'modal-lg',
    //keeps from closing modal with esc key or by clicking out of the modal.
    // user must click cancel or X to close
    'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE]
]);
//echo '<div id="modalContent"><div style="text-align:center"><img src="' . Url::to('@web/img/loader.gif') . '"></div></div>';
yii\bootstrap\Modal::end();
?>





<?php $this->endBody() ?>
    </body>
</html>
        <?php $this->endPage()
        ?>
