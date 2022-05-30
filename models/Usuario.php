<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "usuario".
 *
 * @property integer $usua_id
 * @property string $usua_nombres
 * @property string $usua_apellidos
 * @property string $usua_ci
 * @property string $usua_cuenta
 * @property string $usua_password
 * @property string $usua_rol
 * @property integer $usua_estado
 *
 * @property Descargos[] $descargos
 * @property Pagos[] $pagos
 * @property PagosEventuales[] $pagosEventuales
 */
class Usuario extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'usuario';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                [['usua_nombres', 'usua_apellidos', 'usua_ci', 'usua_cuenta', 'usua_password', 'usua_rol', 'usua_estado'], 'required'],
                [['usua_estado'], 'integer'],
                [['usua_nombres'], 'string', 'max' => 100],
                [['usua_apellidos'], 'string', 'max' => 150],
                [['usua_ci'], 'string', 'min' => 3 ,'max' => 15],
                [['usua_password','usua_cuenta',], 'string', 'min' => 5 ,'max' => 50],
                [['usua_cuenta'], 'unique', 'targetAttribute' => ['usua_cuenta'], 'message' => 'La Cuenta del usuario ya esta registrado. verifique por favor'],    
                [['usua_rol'], 'string', 'max' => 50],
              ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'usua_id' => 'Usua ID',
            'usua_nombres' => 'Nombres',
            'usua_apellidos' => 'Apellidos',
            'usua_ci' => 'Carnet',
            'usua_cuenta' => 'Cuenta',
            'usua_password' => 'Contraseña',
            'usua_rol' => 'Rol',
            'usua_estado' => 'Estado',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDescargos() {
        return $this->hasMany(Descargos::className(), ['usua_id' => 'usua_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPagos() {
        return $this->hasMany(Pagos::className(), ['usua_id' => 'usua_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPagosEventuales() {
        return $this->hasMany(PagosEventuales::className(), ['usua_id' => 'usua_id']);
    }

    // nombre completo del usuario
    public function nombreCompletoUsuario($idUsuario) {
        $resultado = "Sin datos";
        $aux = $this->findOne($idUsuario);
        if ($aux  !== null):
            $resultado = $aux->usua_nombres . " " . $aux->usua_apellidos;
        endif;

        return $resultado;
    }

    //para el inicion o cierre  de sesion 

    public function getGestionLiteral() {
        $resultado = " - ";
        $model = Gestiones::find()->where(['gest_vigente' => 1, 'gest_estado' => 1])->one();
        return ($model !== null) ? $model->gest_nombre : $resultado;
    }

    public function getNumSecUsuario() {
        return $this->hasOne(Usuario::className(), ['num_sec_usuario' => 'num_sec_usuario']);
    }

    //---------------------------------------------metodos de la autentificacion

    public static function findIdentity($id) {
        //return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
        return self::findOne($id);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        /* foreach (self::$users as $user) {
          if ($user['accessToken'] === $token) {
          return new static($user);
          }
          }

          return null;
         */
        throw new \yii\base\NotSupportedException;
    }

    /**
     * Finds user by username     
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username) {
        return self::findOne(['usua_cuenta' => $username, 'usua_estado' => 1]);
    }

    /**
     * @inheritdoc
     */
    public function getId() {
        return $this->usua_id;
    }

    public function getUsername() {
        return $this->usua_cuenta;
    }
    
    
    
    public static function getRolAdmin() {
        $model = (new Usuario())->findOne(Yii::$app->user->id);
        if(!empty($model)){
             return $model->usua_rol === "administrador";
        }else{
            return FALSE;
        }
       
    }
    
    public static function getRolPreli() {
        $model = (new Usuario())->findOne(Yii::$app->user->id);
        if(!empty($model)){
             return $model->usua_rol === "preliquidador";
        }else{
            return FALSE;
        }
     
    }
    public static function getRolCajero() {
         $model = (new Usuario())->findOne(Yii::$app->user->id);
        if(!empty($model)){
             return $model->usua_rol === "cajero";
        }else{
            return FALSE;
        }
            
    }
    public static function getRolSupervisor() {
        $model = (new Usuario())->findOne(Yii::$app->user->id);
       if(!empty($model)){
            return $model->usua_rol === "supervisor";
       }else{
           return FALSE;
       }
           
   }
    
    /**
     * @inheritdoc
     */
    public function getAuthKey() {
        return $this->authKey; //no tengo aun en la tabla de UsuarioOficina
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey) {
        return $this->authKey === $authKey; //no tengo aun en la tabla de UsuarioOficina
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password) {
        return $this->usua_password === $password;
    }

}
