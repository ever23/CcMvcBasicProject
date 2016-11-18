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
 * LA INTERFACE iProtected PUEDE SER EXTENDIDA POR OTRAS INTERFACES 
 * QUE ESTEN DISEÑADAS PARA IMPLEMEMTARSE EN CONTROLADORES 
 * Y QUE NESESITEN QUE SUS METODOS PUBLICOS NO PUEDAN SER LLAMADOS 
 * MEDIANTE HTTP
 *
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc  
 * @subpackage Controladores
 */
interface iProtected
{
//put your code here
}

/**
 * PROVEE INFORMACION SOBRE EL CONTROLADOR 
 *
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc  
 * @subpackage Controladores 
 */
interface InfoController extends iProtected
{

    /**
     * @return \ReflectionClass 
     */
    public static function GetReflectionClass();

    public static function SelfHref($page = [], $get = []);

    public static function SelfControler();

    public static function Href($page, $get = []);
}

/**
 * CARGA LAS LIBRERIAS EXTERNAS 
 * CUANDO UN CONTROLADOR IMPLEMENTA ESTA INTERFACE CARGA LAS 
 * LIBRERIAS EXTERNAS QUE {@link LoadExternLib} RETORNE
 * ESTO ES UTIL PARA CARGAR Y ANTEPONER A EL CARGADOR DE CLASES DE CcMvc LOS AUTOCARGADORES DE LIBRERIAS EXTERNAS YA QUE ESTOS 
 * SUELEN ESTAR MAS PREPARADOS PARA CARGAR SU PROPIA LIBRERIA
 * QUE EL AUTOCARGADOR DE CcMvc
 * 
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc  
 * @subpackage Controladores
 */
interface AutoloaderLibs extends iProtected
{

    /**
     * DEBE RETORNAR UN ARRAY CON LOS NOMBRES DE LOS ARCHIVOS DE LIBRERIAS 
     * EXTERNAS QUE DESEA CARGAR 
     * <code>
     * public static function LoadExternLib()
     * {
     *     return [ method=>'dompdf-master/autoload.inc.php'];
     * }
     * </code>
     * @return array 
     */
    public static function LoadExternLib();
}

/**
 * ESTABLECE LAS EXTENCIONES QUE REQUERIRA O ACEPTA EL CONTROLADOR
 *
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc  
 * @subpackage Controladores 
 */
interface ExtByController extends iProtected
{

    /**
     * 
     * @return array ['require'=>[method=>[..ext]],'accept'=>[method=>[..ext]]]
     */
    public static function ExtAccept();
}

/**
 * SEGURIDAD
 *
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc  
 * @subpackage Controladores 
 */
interface SecurityRequest extends iProtected
{

    /**
     * establece las variables que no se pasaran por del filtro Xss
     * <code>
     * public static function XssAcept()
     * {
     *     return [ 
     *      '_GET'=>[...list vars],
     *      '_POST'=>[...list vars],
     *      '_COOKIE'=>[...list vars],
     *      ];
     * }
     * </code>
     * @return array 
     */
    public static function XssAcept();

    /**
     * establece las variables que no se pasaran por del filtro SQLi
     * <code>
     * public static function SQliAcept()
     * {
     *     return [ 
     *      '_GET'=>[...list vars],
     *      '_POST'=>[...list vars],
     *      '_COOKIE'=>[...list vars],
     *      ];
     * }
     * </code>
     * @return array 
     */
    public static function SQliAcept();
}

/**
 * SEGURIDAD DE METODOS PUBLICOS 
 * 
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc  
 * @subpackage Controladores 
 */
interface ProtectedMetodHttp extends iProtected
{

    /**
     * establece los metodos publicos que no podran ser accesados via enrutamiento automatico
     * @return array 
     */
    public static function MethodsNoHttp();
}

/**
 * @deprecated since version 8.8.3.8 
 */
interface ReRouterMethod extends iProtected
{

    public function __RouterMethod($name);
}
