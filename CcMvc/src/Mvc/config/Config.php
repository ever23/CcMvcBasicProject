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
 * </code></pre>
 * @property-read array $Cache Configuracion del Cache Interno <br><pre><code>
 * array(
 * 'debung' => bool,        //indica si el cache esta en modo debung
 * 'class' => string,       // nombre de la clase que manejara el cache 
 * 'File' => string,        //nombre del archivo de cache 
 * 'ExpireTime' => string   // expresion compatible con el metodo {@link \DateTime::modify()}
 * );
 * </code></pre>
 * @property-read array $Controllers CONFIGURACION DE LOS CONTROLADORES <br><pre><code>
 * array(
 * 'Prefijo' => string,              //el prefijo de las clases controladoras 
 * 'DefaultControllers' => string,   // nombre de la clase controladora por defecto
 *
 * 'Dependencias' =>array,           //este índice es un array asociativo que contendrá las clases que
 *                                   // podrán ser inyectadas en los métodos de controladores por parámetros  
 * );
 * </code></pre>
 * @property-read array $Response CONFIGURACION DE LOS OBJETOS DE RESPUESTA <br><pre><code>
 * array(
 * 'OptimizeImages' => bool,    // Indica a CcMvc si optimizara las imagenes o no, esto quiere decir que si la imagen es mas grande que la pantalla del cliente 
 *                              // CcMvc redimencionara la imagen antes de enviarla 
 * 'ExtencionContenType'=>array // MIME-TYPES DE LAS EXTENCIOES DE ARCHIVOS ext=>mime-type
 * 'Accept'=>array              // clases manejadoras de respuesta segun el mime-type,  
 *                              //mime-type=>array('class'=>'clase ','param'=>array(parametros),'layaut'=>'nombre del layaut')
 * 'CompresZlib'=>bool          //indica si la respuesta sera comprimida con zlib
 * );
 * </code></pre>
 * @property-read array $Router  CONFIGURACION DEL ENRUTADO  
 * <br><pre><code>
 * array(
 * 'AutomaticRoute'=>boll       // Indica si el enrutamiento automatico esta activado
 * 'protocol'=>string           // PROTOCOLO EN EL QUE SE EJECUTARA LA APLICACION HAY DOS POSIBILIDADES http o https
 * 'GetControllerFormat'=>int   // modo en el que CcMvc enrutara las peticiones de controladores hay dos posibilidades  Router::Path o Router::Get
 *                                   
 * 'GetControllers'=>string     // en caso de que GetControllerFormat sea Router::Get sera el nombre de la variable _GET que 
 *                              //contendra la informacion del controlador a ejecutar
 * 'OperadorAlcance'=>string    // en caso de que GetControllerFormat sea Router::Get el o los caracteres que separaran los paquetes, controladores y metodos 
 * 
 * 'ExtencionController'=>string // indica el modo en que se trataran las extenciones de los controladores hay 
 *                               //tres posibilidades  Router::UseExtContr, Router::NoExtContr y Router::RequireExtContr
 * 
 * 'DocumentRoot'=>NULL          // la rais de la aplicacion 
 * 
 * 'DefaultOpenFile'=>array      // es un array con los nombres de los archivos que se enrutaran por defecto si no se indica en la ruta de la peticion
 * 
 * 'CacheExpiresTime'=>string    // expresion compatible con el metodo {@link \DateTime::modify()} indicara el tiempo de expiracion del cache en el navegador
 *                               //del cliente 
 * );
 * </code></pre>
 * @property-read array $Autenticate CONFIGURACION SE SESSIONES Y AUTENTICACION  <br><pre><code>
 * array(
 * 'class'=>string         // indica el nombre de la clase que se encargara de la autentificacion 
 * 'param'=>array          //parametros del constructor de la clase de autentificacion 
 * 'SessionName'=>string   // nombre se la session 
 * 'SessionCookie'=>array  // parametros para las cookies de session
 *                         //  ['path' => string,'cahe' => string,'time' => int, 'dominio' => string, 'httponly' => bool,'ReadAndClose' => bool]
 * );
 * </code></pre>
 * @property-read array $debung  CONFIGURACION DE DEPURACION  <br><pre><code>
 * array(
 * 'error_reporting'=>int   //VALOR PARA error_reporting
 * 'ModoExeption'=>int      //MODO EN QUE SE MANEJARAN LAS EXCEPCIONES
 * 'file'=>string           // ARCHIVO EN EL QUE SE GUARDARAN LOS DATOS DE DEPURACION 
 * 'NoReenviarFiles'=>bool  // INDICA SI SE ENVIARAN LOS ARCHIVOS SI YA EXISTEN EN EL CACHE DEL NAVEGADOR
 * );
 *  </code></pre>
 * @property-read array $DB COFIGURACION DE LA CONECCION A LA BASE DE DATOS  <br><pre><code>
 * array(
 * 'UseStmt'=>bool    // indica si se usara la funcion smtp de la clase nanejadora de bases de datos
 * 'class'=>string    //nombre de la clase manejadora de bases de datos 
 * 'param'=>array     //parametros del constructor de la clase manejadora de bases de datos 
 * );
 *  </code></pre>
 * @property-read array $VarAceptXss VARIABLES DE COOKIE, REQUEST, POST Y GET QUE NO SERAN FILTRADA POR ATAQUES XSS
 * <br><pre><code>
 * array(
 * '_GET'=>array    //nombres de los indices de $_GET a los que no se le aplicara el filtro Xss
 * '_POST'=>array   //nombres de los indices de $_POST a los que no se le aplicara el filtro Xss
 * '_COOKIE'=>array //nombres de los indices de $_COOKIE a los que no se le aplicara el filtro Xss
 * );
 *  </code></pre>
 * @property-read array $VarAceptSqlI VARIABLES DE COOKIE, REQUEST, POST Y GET QUE NO SERAN FILTRADA POR ATAQUES SQLI <br><pre><code>
 * array(
 * '_GET'=>array    //nombres de los indices de $_GET a los que no se le aplicara el filtro anti inyeccion de sql 
 * '_POST'=>array   //nombres de los indices de $_POST a los que no se le aplicara el filtro anti inyeccion de sql 
 * '_COOKIE'=>array //nombres de los indices de $_COOKIE a los que no se le aplicara el filtro anti inyeccion de sql 
 * );
 *  </code></pre>
 * @property-read array $AutoloadLibs INFORMACION DEL DESARROLLADOR  <br><pre><code>
 * array(
 * 'AutoloadLibs'=>bool        //INDICA SI SE USA EL AUTOLOADER ESTANDAR PARA AUTOCARGAR LAS CLASES DE LIBRERIAS EXTERNAS
 * 'AutoloadersFiles'=>array   //LISTA FICHEROS .PHP QUE CONTIENE AUTOLOADERS DE LIBRERIAS EXTERNAS  
 * 'NamespacesForDir'=>array   //LISTA DE NAMESPACES Y LOS DIRECTORIOS DONDE PODRAN SER ENCONTRADAS LAS CLASES DE ESTOS 
 * );
 *  </code></pre>
 * @property-read array $WebMaster INFORMACION DEL DESARROLLADOR  <br><pre><code>
 * array(
 * 'name'=>string    //nombre del desarrollador 
 * 'email'=>string   //email del desarrollador 
 * );
 *  </code></pre>
 * @property-read array $Events CONFIGURACION PARA CAPTURAR LOS EVENTOS  <br><pre><code>
 * array(
 * 'class'=>string    //clase extendidad de MvcEvents
 * );
 *  </code></pre>
 *  </code></pre>
 * @property-read array $SEO CONFIGURACION DE SEO  <br><pre><code>
 * array(
 * 'MetaTang'=>array    //etiquetas meta 
 * 'keywords'=>array    // palabras clave
 * );
 *  </code></pre>
 *  </code></pre>
 * @property-read array $ViewLoaders Configuraciones para los views y layauts en general   <br><pre><code>
 * array(
 * 'Default'=>array    //indica el loader por defecto
 * 'Loaders'=>array    // loaders
 * );
 *  </code></pre>
 */
class Config extends \Cc\Config
{

    public function LoadConfig($name, $config_name)
    {

        if (is_array($config_name))
        {
            $conf = $config_name;
        } else
        {
            $File = new \SplFileInfo(realpath($config_name));

            if (!$File->isFile())
            {
                throw new Exception("el archivo de configuracion " . realpath($config_name) . " no existe");
            }
            if ($File->getExtension() == 'ini')
            {
                $conf = $this->LoadIni($config_name, true);
            } else
            {

                $conf = include($config_name);
            }
        }

        $config = $this->EvalueateIndice($name, $this->config);


        $this->LoadConf($config, $conf);
    }

    private function &EvalueateIndice($name, &$array)
    {
        $ind = explode('.', $name);
        if (count($ind) > 1)
        {
            $id0 = $ind[0];
            unset($ind[0]);
            if (!isset($array[$id0]))
            {
                $array[$id0] = [];
            }
            return $this->EvalueateIndice(implode('.', $ind), $array[$id0]);
        }
        if (!isset($array[$name]))
        {
            $array[$name] = [];
        }
        return $array[$name];
    }

    public function Load($config_name)
    {
        if (is_string($config_name))
        {
            $File = new \SplFileInfo(realpath($config_name));
            $this->config = $this->default;
            if (!$File->isFile())
            {
                throw new Exception("el archivo de configuracion " . realpath($config_name) . " no existe");
            }
            if ($File->getExtension() == 'ini')
            {
                $conf = $this->LoadIni($config_name, true);
            } else
            {

                $this->orig = include($config_name);
            }
        } elseif (is_array($config_name))
        {
            $this->orig = $config_name;
        }
        if (isset($this->config['Response']) && isset($this->config['Response']['Accept']))
            foreach ($this->config['Response']['Accept'] as $accept => $conf)
            {
                unset($this->config['Response']['Accept'][$accept]);
                foreach (explode(',', $accept) as $v)
                {

                    $this->config['Response']['Accept'][trim($v)] = $conf;
                }
            }
        if (isset($this->orig['Response']) && isset($this->orig['Response']['Accept']))
            foreach ($this->orig['Response']['Accept'] as $accept => $conf)
            {
                unset($this->orig['Response']['Accept'][$accept]);
                foreach (explode(',', $accept) as $v)
                {

                    $this->orig['Response']['Accept'][trim($v)] = $conf;
                }
            }
        $this->LoadConf($this->config, $this->orig);
        $this->config['App'] = $this->RemplaceApp($this->config['App']);
    }

}
