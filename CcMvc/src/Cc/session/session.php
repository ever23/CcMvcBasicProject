<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cc;

/**
 * maneja las sessiones del servidor
 * @autor ENYREBER FRANCO       <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                    
 * @package Cc
 * @subpackage Session 
 */
abstract class SESSION extends \SessionHandler implements \ArrayAccess
{

    protected $_SESSION = [];

    // private $ID = NULL;



    public function __debugInfo()
    {
        return $this->_SESSION;
    }

    public abstract function Start($id = NULL);

    public abstract function SetCookie($cache = NULL, $TIME = NULL, $path = NULL, $dominio = NULL, $secure = false, $httponly = false);

    public abstract function SetName($name);

    public abstract function Commit();

    public abstract function GetName();

    public abstract function GetCookieParams();

    public abstract function GetId();

    public function Del()
    {
        $session = $this->_SESSION;
        foreach ($session as $i => $see)
        {
            unset($this->_SESSION[$i]);
        }

        $this->_SESSION = array();
    }

    public function GetVar($var)
    {
        if (!empty($this->_SESSION[$var]))
        {
            return $this->_SESSION[$var];
        }
        return NULL;
    }

    public function __get($var)
    {
        return self::GetVar($var);
    }

    public function SetVar($var, $value)
    {
        $this->_SESSION[$var] = $value;
    }

    public function __set($n, $v)
    {
        self::SetVar($n, $v);
    }

    public function DelVar($var)
    {
        if (!empty($this->_SESSION[$var]))
        {
            unset($this->_SESSION[$var]);
        }
    }

    public function _empty($var)
    {

        return empty($this->_SESSION[$var]);
    }

    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    public function offsetExists($offset)
    {
        return isset($this->_SESSION[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_SESSION[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

}
