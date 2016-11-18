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
use Cc\Security;

/**
 * Description of InternalSession
 *
 * @author usuario
 */
class InternalSession extends \Cc\SESSION
{

    /**
     *  CONSTRUCTOR DE LA CLASE
     *  @param array $exet UN ARRAY CON EL NOMBRE DE LOS CONTROLADORES DONDE NO SE ARA UN EXEPCION Y NO SE EJECUTARA LA AUTENTICACION
     *  GENERALMENTE ESTOS NOMBRES DEVEN SER DEFINIDOS EN EL DOCUMENTO DE CONFIGURACION DE LA APP EN LOS PARAMENTROS  DE ATENTICACION
     */
    public function __construct()
    {

        \session_set_save_handler($this, true);
        $conf = Mvc::App()->Config();
        if (!is_dir($conf['App']['Cache']))
            mkdir($conf['App']['Cache']);
        $path = $conf['App']['Cache'] . 'session' . DIRECTORY_SEPARATOR;
        if (!is_dir($path))
            mkdir($path);
        session_save_path($path);
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

    public function &GetRefenece($name)
    {
        return $this->_SESSION[$name];
    }

}
