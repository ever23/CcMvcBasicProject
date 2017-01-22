<?php

/*
 * 
 * Powered by CcMvc 
 * 
 */

namespace Cc\Mvc\TablaModel;

use Cc\Mvc\TablaModel;

/*
 * Clase modelo para la tabla test
 *
 */

class test extends TablaModel
{

    /**
     * Este metod sera llamado cuando se este por crear la tabla test
     * Ejemplo del codigo 
     * <code>
     * <?php //Ejemplo del codigo 
     * $this->Colum('mi_campo')->PrimaryKey(); // UN CAMPO PARA LA TABLA
     * $this->Colum('mi_otro_campo')->VARCHAR(50); // OTRO CAMPO PARA LA TABLA
     * $this->ForeingKey('mi_campo')->References('mi_otra_tabla')->OnUpdate('CASCADE'); // UNA CLAVE FORANEA  
     * ?>
     * </code>
     */
    public function Create()
    {
        // Columnas de la tabla 
        $this->Colum('id')->INT()->PrimaryKey()->autoincrement();
        $this->Colum('campo1')->VARCHAR(10)->DefaultNull();
        $this->Colum('campo2')->VARCHAR(60)->DefaultNull();
        $this->Colum('campo3')->DATE()->DefaultNull();
    }

    /**
     * Este metod sera llamado cuando se inicialize la tabla test
     * Ejemplo del codigo 
     * <code>
     * <?php //Ejemplo del codigo
     * $this->Insert('hola1','hola2');//insertando usando el formato de parametros
     * $this->Insert(['hola1','hola2']);//insertando usando el formato arrays simples
     * $this->Insert(['campo1'=>'hola1','campo2'=>'hola2']);//insertando usando el formato arrays asociativos o diccionario
     * ?>
     * </code>
     */
    public function Initialized()
    {
        $this->Insert(1, "texto de prueva 1 campo1", "texto de prueva 1 campo2", "2016-12-11");
        $this->Insert(2, "dfssdf", "id2", "2016-11-17");
        $this->Insert(3, "dfgdfgdfg", "id2", "2016-11-10");
    }

}
