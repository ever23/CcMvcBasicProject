<?php

namespace Cc\Mvc;

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
 * @package CcMvc
 * @subpackage Request
 */
class Cookie extends \Cc\Cookie
{

    protected $path = '/';
    protected $host = NULL;
    protected $secure = false;
    protected $httponly = false;
    protected $Cookie = [];
    protected $padre = NULL;

    /**
     * contrctor de la clase
     * @param Config $conf
     */
    public function __construct(Config $conf)
    {


        $this->Cookie = &$_COOKIE;

        if(isset($conf['Autenticate']) && isset($conf['Autenticate']['SessionCookie']))
        {
            $this->path = $conf['Autenticate']['SessionCookie']['path'];
            $this->host = $conf['Autenticate']['SessionCookie']['dominio'];
        }
        if(isset($conf['protocol']))
            $this->secure = $conf['protocol'] == 'https';
        $this->httponly = false;
    }

    protected function SaveCookie($name, $value, $expire = NULL, $path = NULL, $dominio = NULL, $secure = NULL, $httponly = NULL)
    {
        setcookie($name, $value, $expire, $path, $dominio, $secure, $httponly);
    }

}
