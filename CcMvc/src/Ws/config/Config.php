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

namespace Cc\Ws;

/**
 * Representacion de la configuracion de CcMvc 
 * @autor ENYREBER FRANCO       <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                    
 * @package CcMvc
 * @subpackage Configuracion
 * @uses DefaultConfig.php CONFIGURACION POR DEFECTO
 * @property-read array $App DIRECTORIOS DE LA APLICACION <br><pre><code>
 * array(
 * 'app'=>string ,              //directorio de la aplicacion 
 * 'controllers' => string,     //directorio de los controladores
 * 'extern' => string,          //directorio que contiene las librerias externas
 * 'procedimientos' => string,  //directorios que contienes las funciones y procedimientos 
 * 'model' => string,           //directorio que contiene las clases de modelo
 * 'view' => string,            //directorio que contiene los views
 * 'layauts' => string,         //directorio que contiene los layauts 
 * 'Cache' => string,           //directorio donde se almacena el cache 
 * );

 */
class Config extends \Cc\Config
{
    
}
