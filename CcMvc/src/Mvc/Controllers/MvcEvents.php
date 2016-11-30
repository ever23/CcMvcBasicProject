<?php

/**
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
 *
 *  
 */

namespace Cc\Mvc;

use Cc\Mvc;

/**
 * MvcEvents                                                       
 * Captura los eventos importantes de CcMvc como errores                     
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc  
 * @subpackage Controladores
 *
 *
 * @property ViewController $view Controlador de vistas   
 * @property LayautManager $Layaut Controlador de layauts                                             
 */
class MvcEvents
{

    /**
     *
     * @var MvcEvents
     */
    protected static $Event;

    /**
     *
     * @var ViewController 
     */
    public static $View;

    /**
     *
     * @var LayautManager 
     */
    public static $Layaut;

    public function __construct()
    {
        
    }

    public final function &__get($name)
    {
        $NULL = NULL;
        if (strtolower($name) == 'view')
        {
            return static::$View;
        } elseif (strtolower($name) == 'layaut')
        {
            return static::$Layaut;
        } else
        {
            ErrorHandle::Notice("EL ATRIBUTO " . static::class . '::$' . $name . " NO ESTA DEFINIDO ");
            return $NULL;
        }
    }

    public static function Start(Config $conf)
    {
        $class = $conf->Events['class'];
        static::$Event = new $class();
    }

    public static function Tinger($events, ...$params)
    {
        return static::$Event->{$events}(...$params);
    }

    public static function TingerAndDependence($events)
    {
        if (method_exists(static::$Event, $events))
        {
            return static::$Event->{$events}(...Mvc::App()->DependenceInyector->ParamFunction([static::$Event, $events]));
        }
    }

    public function Error401($mensaje)
    {
        $this->View->LoadInternalView('Error401.php');
    }

    public function Error403($mensaje)
    {
        $this->View->LoadInternalView('Error403.php');
    }

    public function Error404($mensaje)
    {
        $this->View->LoadInternalView('Error404.php');
    }

    public function Error500($mensaje)
    {
        $this->View->LoadInternalView('Error500.php');
    }

}
