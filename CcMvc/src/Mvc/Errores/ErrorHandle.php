<?php

namespace Cc\Mvc;

use Cc\Mvc;

/**
 * clase manejadora de errores
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc
 * @subpackage Excepciones
 * @internal    
 */
class ErrorHandle extends \Cc\ErrorHandle
{

    public function __construct($errmo, $errstr, $errfile, $errline, $errcontex)
    {
        parent::__construct($errmo, $errstr, $errfile, $errline, $errcontex);
    }

    public static function ExceptionManager($e, $ini = 0, $end = NULL, $plustrace = NULL, $fatal = NULL)
    {
        /* @var $e \Exception */
        DocumentBuffer::Clear();
        $error = parent::ExceptionManager($e, $ini, $end, $plustrace, false);
        Mvc::App()->LoadError(500, $error);


        exit;
    }

    public function RecoverableError($type, $fatal = true)
    {
        $error = parent::RecoverableError($type, false);
        Mvc::App()->Log($error);

        Mvc::App()->LoadError(500, $error);
        if ($fatal)
        {

            exit;
        }
    }

    public static function Warning($error, $tr = 0, $file = NULL, $line = NULL)
    {
        if (Mvc::App()->IsDebung())
            Mvc::App()->Log(parent::Warning($error, $tr + 1, $file, $line));
    }

    public static function Notice($error, $tr = 0)
    {

        if (Mvc::App()->IsDebung())
            Mvc::App()->Log(parent::Notice($error, $tr + 1));
    }

}

/**
 * @package CcMvc
 * @subpackage Excepciones
 */
class Exception extends \Cc\Exception
{
    
}
