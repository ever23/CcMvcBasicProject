<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cc\Ws;

use Cc\Security;

/**
 * maneja las sessiones del servidor
 * @autor ENYREBER FRANCO       <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                    
 * @package CcWs
 * @subpackage Session 
 */
class Session extends \Cc\SESSION
{

    protected $conf;
    protected $Event;
    protected $Ccreate = '';
    protected $name = '';
    protected $id = '';
    protected $savePath;

    public function __construct(Event &$e, $sessionName, $classCreate = false, $savepath = '')
    {
        $this->name = $sessionName;
        $this->Event = &$e;
        $this->Ccreate = $classCreate;
        if (isset($this->Event->Cookie[$sessionName]))
        {
            $this->id = $this->Event->Cookie[$sessionName];
        } elseif (isset($this->Event->GET[$sessionName]))
        {
            $this->id = $this->Event->GET[$sessionName];
        }
        $this->savePath = realpath($savepath);
        session_set_save_handler($this);
    }

    public function read($id)
    {
        $data = @file_get_contents($this->savePath . "/sess_$id");
        if (!$data)
        {
            return "";
        } elseif ($this->Ccreate)
        {
            return Security::decrypt($data, $this->Ccreate . $id, false);
        } else
        {
            return $data;
        }
    }

    public function write($id, $data)
    {
        if ($this->Ccreate)
        {
            $data = Security::encrypt($data, $this->Ccreate . $id, false);
        }
        return file_put_contents($this->savePath . "/sess_$id", $data) === false ? false : true;
    }

    public function destroy($id)
    {
        $file = "$this->savePath/sess_$id";
        if (file_exists($file))
        {
            unlink($file);
        }
        return true;
    }

    public function Start($id = NULL)
    {
        if (!is_null($id))
        {
            $this->id = $id;
        }

        session_id($this->id);
        if (!file_exists($this->savePath . "/sess_" . $this->id))
        {
            $this->_SESSION = [];
            return false;
        }
        session_save_path($this->savePath);
        @session_start();
        $this->_SESSION = $_SESSION;
        return true;
    }

    public function SetCookie($cache = NULL, $TIME = NULL, $path = NULL, $dominio = NULL, $secure = false, $httponly = false)
    {
        $this->Event->Cookie->Set($this->name, $this->id, $TIME, $path, $dominio, $secure, $httponly);
    }

    public function SetName($name)
    {

        $this->name = $name;
    }

    public function Commit()
    {
        $_SESSION = $this->_SESSION;
        if ($_SESSION !== [])
            session_commit();
        $_SESSION = [];
    }

    public function GetName()
    {
        return $this->name;
    }

    public function GetCookieParams()
    {
        //return session_get_cookie_params();
    }

    public function GetId()
    {
        return $this->id;
    }

}
