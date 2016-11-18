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

use Cc\Mvc;

/**
 * Administra el almacenamiento de las propiedades publicas de los controladores en la session 
 *
 * @author Enyerber Franco
 */
class SessionSaveController implements \ArrayAccess
{

    /**
     * instancia del controlador 
     * @var Controllers 
     */
    protected $controller;

    /**
     * instancia de ReflectionClass del controlador 
     * @var  \ReflectionClass 
     */
    protected $ReflectionClass;

    /**
     * variables que se almacenaran en la session 
     * @var array
     */
    protected $_SESSION;

    /**
     * nombre del indice en session 
     * @var string 
     */
    protected $sessionName = '';

    /**
     * indica si el controlador usara esta funcion
     * @var bool 
     */
    private $Use = false;

    /**
     * lista de propiedades del controlador 
     * @var array 
     */
    protected $Properties = [];

    /**
     * 
     * @param \Cc\Mvc\Controllers $controller
     * @param \ReflectionClass $class
     */
    public function __construct(Controllers &$controller, \ReflectionClass $class)
    {
        $this->controller = &$controller;
        $this->ReflectionClass = $class;
        if (is_array($this->ReflectionClass->getTraitNames()) && in_array(PropertiesInSession::class, $this->ReflectionClass->getTraitNames()))
        {
            $session = Mvc::App()->GetInternalSession();
            $this->sessionName = $class->name;
            if (!isset($session[static::class]))
            {
                $session[static::class] = [];
            }

            $session = &$session->GetRefenece(static::class);
            if (!isset($session[$this->sessionName]))
            {
                $session[$this->sessionName] = [];
            }
            $this->_SESSION = &$session[$this->sessionName];
            $this->Use = true;
        } else
        {
            $this->Use = false;
        }
    }

    /**
     * asigna una valor a la propiedad almacenada en sesion de una clase especifica 
     * @param string $property
     * @param mixes $value
     * @param string $controller nombre de la clase controladora
     */
    public function Set($property, $value, $controller)
    {
        $session = Mvc::App()->GetInternalSession();
        if (isset($session[static::class]) && isset($session[static::class][$controller]))
        {
            if ($controller == $this->ReflectionClass->name)
            {
                $this->offsetSet($property, $value);
            } else
            {
                $c = &$session->GetRefenece(static::class);
                $c[$controller][$property] = $value;
            }
        }
    }

    /**
     * obtiene una el valor de una propiedad almacenada en session de una clase controladora
     * @param string $property
     * @param string $controller nombre de la clase controladora
     * @return mixes
     */
    public function Get($property, $controller)
    {
        $session = Mvc::App()->GetInternalSession();
        if (isset($session[static::class]) && isset($session[static::class][$controller]) && isset($session[static::class][$controller][$property]))
        {
            if ($controller == $this->ReflectionClass->name)
            {
                return $this->offsetGet($property);
            } else
            {
                return $session[static::class][$controller][$property];
            }
        }
    }

    /**
     * indica si en el controlador actual se usa la session para propiedades
     * @return bool
     */
    public function IsUseSession()
    {
        return $this->Use;
    }

    /**
     * obtiene las propiedades de el controlador y si existe en la session se asinan los valores
     */
    public function ParseAttrs()
    {
        if ($this->Use)
        {
            $Properties = $this->ReflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC);
            /* @var $Property \ReflectionProperty */
            foreach ($Properties as $Property)
            {
                if ($Property->isStatic())
                    continue;
                $name = $Property->name;
                $this->Properties[] = $name;
                if (key_exists($name, $this->_SESSION))
                {
                    $this->controller->{$name} = &$this->_SESSION[$name];
                } else
                {
                    $this->_SESSION[$name] = $this->controller->{$name};
                    $this->controller->{$name} = &$this->_SESSION[$name];
                }
            }
        }
    }

    /**
     * elimina todo los datos de la session 
     */
    public static function Clean()
    {
        $session = Mvc::App()->GetInternalSession();
        unset($session[static::class]);
    }

    public function offsetExists($offset)
    {
        return key_exists($offset, $this->_SESSION);
    }

    public function offsetGet($offset)
    {
        return $this->_SESSION[$offset];
    }

    public function offsetSet($offset, $value)
    {

        if (is_object($value) && (!($value instanceof \Serializable) || !(method_exists($value, '') && method_exists($value, ''))))
        {
            throw new Exception("El valor insertado en la propiedad " . $this->ReflectionClass->name . "->" . $offset . " no es valido debe ser Serializable");
        }
        $this->_SESSION[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->_SESSION[$offset]);
        unset($this->controller->{$offset});
    }

}

/**
 * establece metodos para el manejo de session en las propiedades de los controladores 
 */
trait PropertiesInSession
{

    /**
     * elimina todas las propiedades del controlador en la session 
     */
    protected final function CleanPropertiesInSession()
    {
        Mvc::App()->SelectorController->sessionController->Clean();
    }

    /**
     * obtiene una el valor de una propiedad almacenada en session de una clase controladora
     * @param string $property
     * @param string $controller nombre de la clase controladora 
     * @return mixes
     */
    protected final function GetProperty($property, $controller)
    {
        return Mvc::App()->SelectorController->sessionController->Get($property, $controller);
    }

    /**
     * asigna una valor a la propiedad almacenada en sesion de una clase especifica 
     * @param string $property
     * @param mixes $value
     * @param string $controller nombre de la clase controladora 
     */
    protected final function SetProperty($property, $value, $controller)
    {
        Mvc::App()->SelectorController->sessionController->Set($property, $value, $controller);
    }

}
