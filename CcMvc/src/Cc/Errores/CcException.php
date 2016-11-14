<?php

namespace Cc;

/**
 * clase manejadora de exceptions
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package Cc
 * @subpackage Exception 
 * @internal    
 */
class CcException extends \Exception
{

    const USER = 0;
    const DEBUNG = 1;
    const DEBUNG_DATABASE = 2;

    private static $MODE = 1;

    /**
     * CONTIENE TODAS LAS EXCEPTIONS ANTERIORES
     * @var array
     */
    public static $msjs = array();

    public function __construct($msj = NULL, $code = NULL, $object = NULL)
    {

        if(is_object($code))
        {
            $object = $code;
            $code = NULL;
            if(method_exists($object, 'getMessage'))
            {
                $mysql_error = $object->getMessage();
            }
        }

        parent::__construct((is_array($msj) ? $msj['msj'] : $msj), $code, $object);
        $mysql_error = NULL;
        $mysql_errno = NULL;
        if(is_object($object))
        {

            if(method_exists($object, 'connect_error') && method_exists($object, 'error'))
            {
                $mysql_error = $object->connect_error ? $object->connect_error : $object->error;
            } else
            {
                if(method_exists($object, 'error'))
                {
                    $mysql_error = $object->error;
                }
                if(method_exists($object, 'errores'))
                {
                    $object->errores.=',';
                }
            }

            if(method_exists($object, 'errno'))
            {
                $mysql_errno = $object->errno;
            }
        } else
        {
            $mysql_error = $object;
        }
        $trace = $this->getTrace();

        //array_unshift($trace,array('file'=>$this->getFile(),'line'=>$this->getLine(),'function'=>'','class'=>'','args'=>array()));

        $this->PushMsj($this->getMessage(), $trace, $mysql_error, $mysql_errno);
    }

    public static function GetMode()
    {
        return self::$MODE;
    }

    public static function SetMode($md)
    {
        self::$MODE = $md;
    }

    protected function PushMsj($msj, $trace, $mysql_error, $mysql_errno)
    {
        if(is_array($msj))
        {
            $mesaje = array_merge([

                'error' => $mysql_error != '' ? "error: " . $mysql_error : NULL,
                'errno' => $mysql_errno != '' ? "errno: " . $mysql_errno : NULL,
                'trace' => $trace,
                'line' => $this->getLine(),
                'code' => $this->getCode(),
                'file' => $this->getFile()
                    ], $msj);
        } else
        {
            $mesaje = [
                'msj' => $msj,
                'error' => $mysql_error != '' ? "error: " . $mysql_error : NULL,
                'errno' => $mysql_errno != '' ? "errno: " . $mysql_errno : NULL,
                'trace' => $trace,
                'line' => $this->getLine(),
                'code' => $this->getCode(),
                'file' => $this->getFile()
            ];
        }
        array_push(static::$msjs, $mesaje);
    }

    public function SliceThisTrance($offset, $length = NULL)
    {
        $n = count(static::$msjs) - 1;
        $trace = static::$msjs[$n]['trace'];
        if($length < 0)
        {
            $length = count($trace) + $length;
        }
        static::$msjs[$n]['trace'] = array_slice($trace, $offset, $length);
    }

    public static function _Empty()
    {
        return empty(static::$msjs);
    }

    public static function GetExeptionS($str = false)
    {

        switch(self::$MODE)
        {
            case static::USER:
                $msj = '';
                foreach(static::$msjs as $msjs)
                {
                    $msj.=$msjs['msj'] . "<br>";
                }
                break;
            case static::DEBUNG_DATABASE:
                $msj = '';
                foreach(static::$msjs as $i => $exti)
                {

                    if(empty($exti['msj']))
                    {
                        $exti['msj'] = '';
                    }
                    if(empty($exti['error']))
                    {
                        $exti['error'] = '';
                    }
                    if(empty($exti['errno']))
                    {
                        $exti['errno'] = '';
                    }

                    $msj.=static::TraceAsString($exti['msj'] . '<br>' . $exti['error'] . " " . $exti['errno'] . "", $i) . '<br>';
                }
                break;
            case static::DEBUNG:
            default:
                $msj = '';
                foreach(static::$msjs as $i => $msjs)
                {
                    $msj.=static::TraceAsString($msjs['msj'], $i) . '<br>';
                }
                break;
        }
        if($str)
        {
            $msj = str_replace("'", "\"", $msj);
            $msj = addcslashes($msj, "\\");
        }
        return $msj;
    }

    public static function DieExeptionS()
    {
        static::_Empty() || die(static::GetExeptionS());
    }

    public function NumExeption()
    {

        return count(static::$msjs);
    }

    public function AddMsjMysql($error, $errno)
    {
        static::$msjs[$this->NumExeption() - 1]['error'] = $error;
        static::$msjs[$this->NumExeption() - 1]['errno'] = $errno;
    }

use TranceHtml
    {
        TraceAsString as HtmlTraceAsString;
    }

    protected static function TraceAsString($mjs = NULL, $trace = NULL)
    {
        $exeption = static::class;
        if(is_null($mjs))
        {
            $mjs = static::$msjs[count(static::$msjs) - 1]['msj'];
        }
        if(is_null($trace))
        {
            $trace = static::$msjs[count(static::$msjs) - 1]['trace'];
        } else
        {
            $n = $trace;
            $trace = static::$msjs[$n]['trace'];
            $exeption = [static::class, static::$msjs[$n]['file'], static::$msjs[$n]['line'], static::$msjs[$n]['code']];
        }
        return static::HtmlTraceAsString($exeption, $mjs, $trace);
    }

    /*
      public function __toString()
      {
      return static::TraceAsString();
      } */
}
