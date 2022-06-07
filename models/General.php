<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\models;

/**
 * Description of General
 *
 * @author mflorest
 */
class General extends \yii\db\ActiveRecord{
    
    public  function verificarFechaVigente($fecha_registro=null){
        //$horaInicio = "08:00:00";
        $resultado = false;
        if($fecha_registro != NULL):                             
            
            $fecha_registro = substr($fecha_registro, 0,10);        
            $fechaActual = (new \yii\db\Query())->createCommand()
                                                ->setSql("SELECT to_char(now(), 'YYYY-MM-DD')")
                                                ->queryScalar();

            $date1 = new \DateTime($fecha_registro); // fecha del registro en tabla            
            $date2 = new \DateTime($fechaActual);   // fecha actual obtenida del servidor de base de datos   
            
            $diff = $date1->diff($date2);           
            $dias = $diff->days;            
            /*$hora = $diff->h;       $minuto = $diff->i;*/     
            if($dias == 0):  // vigencia en el dia
                $resultado = true;
            endif;
        endif;
        
        return $resultado;                     
    } 

    

}
