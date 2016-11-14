<?php

/*
 * Copyright (C) 2016 Enyerber Franco
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Cc\Mvc;

use Cc\iDataBase;
use Cc\Mvc;

/**
 * Description of DBtablaModel
 * FACILITA LAS OPERACIONES BASICAS SOBRE LAS TABLAS A DIFERENCIA DE {@link \Cc\DBtabla} 
 * QUE OBTIENE LOS METADATOS DE LA BASE DE DATOS ESTA LOS OBTENIENEDE 
 * DE UNA CLASE CREADA POR EL USUARIO PARA ESTE PROPOSITO DICHA CLASE 
 * DEBE SER EXTENDIDA DE LA CLASE {@link Cc\Mvc\DBtablaModel\TablaModel} 
 * ESTA CLASE DEBE SER USADA CUANDO SE USE UNA BASE DE DATOS DE LA CUAL CcMvc
 * NO PROPORCIONE EL DRIVER CORRESPONDIENTE Y NO SE PUEDA USAR  {@link \Cc\DBtabla} 
 * 
 * <code>
 * <?php
 * // EJEMPLO DE UNA CLASE DE MODELO DE DATOS PARA UNA TABLA 
 * namespace Cc\Mvc\DBtablaModel;
 * class usuarios extends TablaModel
 * {
 *      protected function Campos()
 *      {
 *          return [
 *                  'codi_admin' => [ 'Type' => 'INT', 'KEY' => self::PrimaryKey, 'Null' => '', 'Extra' => self::AutoIncrement],
 *                  'nomb_admin' => [ 'Type' => 'varchar(45)'],
 *                  'apel_admin' => [ 'Type' => 'varchar(45)'],
 *                  'email_admin' => [ 'Type' => 'varchar(45)'],
 *                  'clave_admin' => [ 'Type' => 'varchar(250)'],
 *                  'perm_admin' => [ 'Type' => "enum('creador', 'colaborador', 'visitante')	"]
 *                  ];
 *      }
 *
 * }
 * 
 * <code>                           
 *                                                                              
 *                                                       
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc
 * @subpackage Modelo
 * @category DBtablaModel
 * @see iDataBase
 * @see DB_MySQLi
 * @see DB_PDO  
 */
class DBtablaModel extends DBtabla
{

    /**
     *
     * @var Config 
     */
    private $config;

    /**
     *
     * @var \Cc\Mvc\DBtablaModel\Model 
     */
    private $model;

    /**
     * 
     * @param iDataBase $db OBJETO MANEJADOR DE BASES DE DATOS 
     * @param string $tabla NOMBRE DE LA TABLA EN LA BASE DE DATOS 
     * @param bool $DBMetadata SI ES TRUE Y NO EXISTE EL MODELO DE DATOS PARA LA TABLA SE TRATARA DE BUSCAR LOS METADATOS EN LA BASE DE DATOS 
     * @throws Exception EN CASO DE QUE LA TABLA O EL MODELO DE DATOS NO EXISTA 
     */
    public function __construct(iDataBase &$db, $tabla, $DBMetadata = false)
    {
        $this->config = Mvc::App()->Config();
        $class = '\\Cc\\Mvc\\DBtablaModel\\' . $tabla;
        $this->db = &$db;
        $this->tabla = $this->FilterSqlI($tabla);
        $this->CacheName = get_class($db) . static::class . $tabla;

        if(class_exists($class))
        {
            $this->model = new $class();
            if(!($this->model instanceof DBtablaModel\TablaModel))
            {
                throw new Exception("LA CLASE " . $class . " DEBE SER EXTENDIDA DE " . DBtablaModel\TablaModel::class);
            }
            $cache = [];

            $cache['colum'] = $this->model->jsonSerialize();
            $cache['primary'] = [];
            $cache['autoinrement'] = '';
            foreach($cache['colum'] as $i => $campo)
            {

                if(isset($campo['Key']) && $campo['Key'] == DBtablaModel\TablaModel::PrimaryKey && !in_array($i, $cache['primary']))
                {
                    array_push($cache['primary'], $i);
                }
                if(isset($campo['Extra']) && $campo['Extra'] === DBtablaModel\TablaModel::AutoIncrement)
                {
                    $cache['autoinrement'] = $i;
                }
            }

            $cache['Ttipe'] = self::table;
            $cache['tabla'] = $tabla;
            $cache['Ttipe'] = self::table;
            $cache['tabla'] = $tabla;
            $cache['typeDB'] = $this->GetTypeDB();
            $cache['OrderColum'] = array_keys($cache['colum']);
            $this->unserialize($cache);
        } elseif($DBMetadata)
        {
            parent::__construct($db, $tabla);
        } else
        {
            throw new Exception("EL MODELO DE DATOS DE LA TABLA " . $tabla . " NO EXISTE, DEBERIA CREAR LA CLASE " . $class);
        }
    }

}
