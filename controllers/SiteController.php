<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use \yii\web\Response;
use app\models\CambioContrasenia;
use yii\helpers\Html;


class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    
    /**
     * Displays homepage.
     *
     * @return string
     */

    public function actionCambiocontrasenia()
    {   
       if (! (!Yii::$app->user->isGuest))
       {
            Yii::$app->user->logout();
            return ($this->redirect(['site/login']));
       }
       
       //***************
        $request = Yii::$app->request;

        $model = new CambioContrasenia();  
        if($request->isAjax)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            
            if($request->isGet){
                return [
                    'title'=> "Cambio de contraseña",
                    'content'=>$this->renderAjax('cambiocontrasenia', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                               Html::button('Cambiar contraseña',['class'=>'btn btn-primary','type'=>"submit"])
                ];         
            }
            else 
            {
                if($model->load($request->post()) && $model->validate())
                {
                    $uo=UsuarioOficina::find()->where(['num_sec_usu_ofi'=>Yii::$app->user->identity->num_sec_usu_ofi])->one();
                    $email= $uo->numSecUsuario->email;
                    $usuario= $uo->username;
                    $password= $model->contrasenia_nueva;
                    
                    $uo->password=$password;
                    $uo->save();

                    $resultado=Yii::$app->mailer->compose()
                        ->setFrom(Yii::$app->params['adminEmail'])
                        ->setTo($email)
                        ->setSubject('Cambio de contraseña del sistema SisCorso')
                        ->setHtmlBody("<b>El cambio de contraseña de su cuenta de usuario del sistema SisCorso ha sido realizada correctamente, con los siguientes datos de autentificación: <br> Usuario: $usuario<br> Password: $password<br></b>")
                        ->send();



                   if ($resultado)  
                        {
                            $msj="La información de cambio de contraseña de su cuenta ha sido enviada al correo electrónico: $email";
                            return [
                            
                            'title'=> "Mensaje",
                            'content'=>$this->renderAjax('error', [
                                'msj'=>$msj,
                            ]),
                            'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"])
                            ];         
                        }    
                        else
                        {
                            $msj="Hubo un error en el envío del correo electrónico de datos, al email: $email, pero su cuenta ha sido cambiada exitosamente.";
                            return [
                            
                            'title'=> "Mensaje",
                            'content'=>$this->renderAjax('error', [
                                'msj'=>$msj,
                            ]),
                            'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"])
                            ];         
                        }

                     
                }
                else
                {           
             return [
                    'title'=> "Cambio de contraseña",
                    'content'=>$this->renderAjax('cambiocontrasenia', [
                        'model' => $model,
                    ]),
                    'footer'=> Html::button('Cerrar',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                               Html::button('Cambiar contraseña',['class'=>'btn btn-primary','type'=>"submit"])
        
                ];         
                }
            }
        }
        else
        {
       
            if ($model->load($request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->cod_sitio]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
//***************
        
    }




    public function actionIndex()
    {
           
           /*
           $session = Yii::$app->session;
           $logged = $session->get('logged');
           if ($logged==null)
           {
                //Yii::$app->user->logout();             
                //exit(1);
                return return ($this->redirect(['site/login']));// expiro la sesion
           }
           */
        

           return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) 
        {
            return ($this->redirect(['site/login']));
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) 
        {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return ($this->redirect(['site/login']));
    }

    /**
     * Displays contact page.
     *
     * @return string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
