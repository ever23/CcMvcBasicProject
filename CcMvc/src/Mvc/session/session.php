<?php

namespace Cc\Mvc;

use Cc\Security;

/**
 * maneja las sessiones del servidor
 * @autor ENYREBER FRANCO       <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                    
 * @package CcMvc
 * @subpackage Session 
 */
class SESSION extends \Cc\SESSION
{

    public function __construct()
    {


        \session_set_save_handler($this, true);
    }

    public function read($id)
    {
        $data = parent::read($id);

        if (!$data)
        {
            return "";
        } else
        {
            return Security::decrypt($data, static::class . $id, false);
        }
    }

    public function write($id, $data)
    {
        $data = Security::encrypt($data, static::class . $id, false);

        return parent::write($id, $data);
    }

    public function Start($id = NULL)
    {
        if (!is_null($id))
        {
            session_id($id);
        } else
        {
            if ($this->name == '' && defined("__SESSION_NAME__"))
            {

                $this->name = __SESSION_NAME__;
                session_name(__SESSION_NAME__);
            }

            if (!empty($_REQUEST[$this->name]))
                session_id($_REQUEST[$this->name]);
        }

        session_start();
        $this->_SESSION = & $_SESSION;
        if (!isset($this->_SESSION['Token' . static::class]))
        {

            $md = base64_encode(openssl_random_pseudo_bytes(16));
            $this->_SESSION['Token' . static::class] = $md;
            // output_add_rewrite_var('Token' . static::class, $md);
        } else
        {
            //output_add_rewrite_var('Token' . static::class, $_SESSION['Token' . static::class]);
        }
        //  ob_start([&$this,'Handle']);
        // $this->ID = $this->GetId();
        return array($this->GetName() => $this->GetId());
    }

    public function SetCookie($cache = NULL, $TIME = NULL, $path = NULL, $dominio = NULL, $secure = false, $httponly = false)
    {
        if (!is_numeric($TIME))
        {
            $t = new \DateTime();
            $t3 = new \DateTime();
            $t3->modify($TIME);
            // $interval = $t3->diff($t);
            $TIME = $t3->getTimestamp() - $t->getTimestamp();
            // $TIME = $interval->format('%s') + ($interval->format('%i') * 60) + ($interval->format('%h') * 3600) + ($interval->format('%d') * 24 * 3600) + ( $interval->format('%m') * 30 * 24 * 3600) + ($interval->format('%y') * 12 * 30 * 24 * 3600);
        }
        $path = \Cc\UrlManager::EncodeUrl($path);
        session_set_cookie_params($TIME, $path, $dominio, $secure, $httponly);
        session_cache_limiter($cache);
    }

    public function SetName($name)
    {
        $this->name = $name;
        session_name($name);
    }

    public function Destroy($key = NULL)
    {
        if (!is_null($key))
        {
            return parent::destroy($key);
        }
        $_SESSION = array();
        if (isset($_COOKIE[$this->GetName()]))
        {
            $p = $this->GetCookieParams();
            setcookie($this->GetName(), '', time(), $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }

        session_destroy();
    }

    public function Commit()
    {
        session_write_close();
    }

    public function GetName()
    {
        return session_name();
    }

    public function GetCookieParams()
    {
        return session_get_cookie_params();
    }

    public function GetId()
    {
        return session_id();
    }

}
