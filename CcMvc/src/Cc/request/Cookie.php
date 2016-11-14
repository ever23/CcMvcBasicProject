<?php

namespace Cc;

/**
 * clase Cookie para administrar las cokies mas efisientemente
 * <code>
 * <?php
 * // @var $conf Config
 * $cookie= new Cookie($conf);
 * 
 * echo $cookie['micookie1'];// leyendo la cookie 
 * echo $cookie->micookie1;// leyendo la cookie 
 * 
 * $cookie->Set('micookie2','holacookie');// enviando una cookie
 * $cookie['micookie2']='holacookie';// enviando una cookie
 * $cookie->micookie2='holacookie';// enviando una cookie
 * 
 * 
 * unset($cookie['micookie1']); // eliminando la cookie 
 * ?>
 * </code>
 * @autor ENYREBER FRANCO       <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                    
 * @package Cc
 * @subpackage Request
 */
abstract class Cookie implements \ArrayAccess, \Countable, \IteratorAggregate
{

    protected $path = '/';
    protected $host = NULL;
    protected $secure = false;
    protected $httponly = false;
    protected $Cookie = [];
    protected $padre = NULL;

    /**
     * @access private
     * @return type
     */
    public function __debugInfo()
    {
        return $this->Cookie;
    }

    /**
     * ENVIA UNA COOKIE AL NAVEGADOR 
     * @param string $name
     * @param mixes $value tambien se pueden enviar array mediante cookies
     * @param int $expire
     * @param strig $path
     * @param string $dominio
     * @param bool $secure
     * @param bool $httponly
     */
    public function Set($name, $value, $expire = NULL, $path = NULL, $dominio = NULL, $secure = NULL, $httponly = NULL)
    {
        if(is_null($path))
        {
            $path = $this->path;
        }
        if(is_null($dominio))
        {
            $dominio = $this->host;
        }
        if(is_null($secure))
        {
            $secure = $this->secure;
        }
        if(is_null($httponly))
        {
            $httponly = $this->httponly;
        }


        if(is_array($value) || $value instanceof \Traversable)
        {
            foreach($value as $i => $v)
            {
                $this->Set($name . '[' . $i . ']', $v, $expire, $path, $dominio, $secure, $httponly);
            }
        }

        $this->SaveCookie($name, $value, $expire, $path, $dominio, $secure, $httponly);
    }

    abstract protected function SaveCookie($name, $value, $expire = NULL, $path = NULL, $dominio = NULL, $secure = NULL, $httponly = NULL);

    /**
     * filtra una cookie
     * @param string $name nombre de la cookie
     * @param int $filter
     * @param mixes $option
     * @return type
     * @uses filter_var()
     */
    public function Filter($name, $filter = FILTER_DEFAULT, $option = NULL)
    {
        return filter_var($this->offsetGet($name), $filter, $option);
    }

    /**
     * @access private
     * @param type $offset
     * @param type $value
     */
    public function offsetSet($offset, $value)
    {
        if(is_array($value) || $value instanceof \Traversable)
        {
            foreach($value as $i => $v)
            {
                $this->offsetSet($offset . '[' . $i . ']', $v);
            }
        } else
        {
            $this->Set($offset, $value);
            $this->Cookie[$offset] = $value;
        }
    }

    /**
     *  @access private
     * @param type $offset
     * @return type
     */
    public function offsetExists($offset)
    {
        return isset($this->Cookie[$offset]);
    }

    /**
     *  @access private
     * @param type $offset
     */
    public function offsetUnset($offset)
    {

        if(isset($this->Cookie[$offset]))
        {
            $this->CookieUnset($offset, $this->Cookie[$offset]);

            unset($this->Cookie[$offset]);
        }
    }

    /**
     *  @access private
     * @param type $name
     * @param type $cookie
     */
    protected function CookieUnset($name, $cookie)
    {

        if(is_array($cookie) || $cookie instanceof \Traversable)
        {

            foreach($cookie as $i => $v)
            {
                $this->CookieUnset($name . '[' . $i . ']', $v);
            }
        } else
        {
            $this->Set($name, NULL, time() - 1000);
        }
    }

    /**
     *  @access private
     * @param type $offset
     * @return type
     */
    public function offsetGet($offset)
    {
        if(!$this->offsetExists($offset))
        {
            ErrorHandle::Notice("Undefined index: " . $offset);
            return;
        }

        return $this->Cookie[$offset];
    }

    /**
     *  @access private
     * @return type
     */
    public function count()
    {
        return count($this->Cookie);
    }

    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __unset($name)
    {
        $this->offsetUnset($name);
    }

    public function __isset($name)
    {
        $this->offsetExists($name);
    }

    /**
     *  @access private
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->Cookie);
    }

}
