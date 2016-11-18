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
 * clase manejadora de errores
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc
 * @subpackage Excepciones
 * @internal    
 */
class ErrorHandle extends \Cc\ErrorHandle
{

    /**
     * 
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param object $errcontex
     */
    public function __construct($errno, $errstr, $errfile, $errline, $errcontex)
    {
        parent::__construct($errno, $errstr, $errfile, $errline, $errcontex);
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
