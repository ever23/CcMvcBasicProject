<?php

namespace Cc;

/**
 * clase manejadora de errores y excepciones no capturadas
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package Cc
 * @subpackage Exception
 * @internal    
 */
class ErrorHandle
{

    protected $errno;
    protected $errstr;
    protected $errline;
    protected $errfile;
    protected $errcontex;
    protected static $Handle;
    protected static $oldHandel;
    public static $debung;
    protected static $EndTrace = NULL;

    use TranceHtml;

    /**
     * 
     * @param int $errmo
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @param mixes $errcontex
     * @access private
     */
    public function __construct($errmo, $errstr, $errfile, $errline, $errcontex)
    {

        $this->errno = $errmo;
        $this->errstr = $errstr;
        $this->errfile = $errfile;
        $this->errline = $errline;
        $this->errcontex = $errcontex;
    }

    /**
     * inicia la acpturacion de errores y excepciones no capturadas 
     * @param int $end numero de trace que sera mostrado
     */
    public static function SetHandle($end = NULL)
    {

        self::$EndTrace = $end;

        self::$oldHandel = set_error_handler(array(static::class, 'ErrorHandle'), E_ALL | E_STRICT);

        set_exception_handler([static::class, 'ExcetionHandle']);
    }

    /**
     * callable para {@link set_exception_handler()}
     * @param \Exception|\Error $e
     * 
     * @access private
     */
    public static function ExcetionHandle($e)
    {

        return static::ExceptionManager($e, 0, self::$EndTrace);
    }

    /**
     * sera llamado cuando ocurra una {@link \Exception} o {@link \Error} no capturado antes
     * @param \Exception|\Error $e
     * @param int $ini
     * @param iny $end
     * @param array $plustrace
     * @param bool $exit
     */
    public static function ExceptionManager($e, $ini = 0, $end = NULL, $plustrace = NULL, $exit = true)
    {
        if ($end < 0)
        {
            $end = count($e->getTrace()) + $end;
        }
        if (self::$debung['error_reporting'] == 0)
        {
            $error = get_class($e);
        } else
        {
            $trace = $e->getTrace();

            if ($plustrace)
            {
                array_push($trace, $plustrace);
                if (!is_null($end))
                    $end++;
            }
            /** @var $e Exception */
            if (is_null($end))
            {
                $end = count($trace);
            }

            $error = self::TraceAsString([get_class($e), $e->getFile(), $e->getLine(), $e->getCode()], $e->getMessage(), $trace, 0, $end);
        }

        if ($exit)
        {
            echo $error;
            exit;
        }

        return $error;
    }

    /**
     * callable para {@link set_error_handler()}
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @param mixes $errcontext
     * @return bool
     * @internal 
     */
    public static function ErrorHandle($errno = NULL, $errstr = NULL, $errfile = NULL, $errline = NULL, $errcontext = NULl)
    {
        if (!(error_reporting() & $errno))
        {
            // Este código de error no está incluido en error_reporting

            return;
        }
        $e = new static($errno, $errstr, $errfile, $errline, $errcontext);
        return $e->ErrorManager();
    }

    /**
     * deja de capturar lor errores y excepciones
     */
    public static function RecoverHandle()
    {
        self::$EndTrace = NULL;
        restore_error_handler();
        restore_exception_handler();
        //	set_error_handler(self::$oldHandel);
    }

    public function ErrorManager()
    {


        switch ($this->errno)
        {
            case E_WARNING:

                self::warning($this->errstr, 1, $this->errfile, $this->errline);
                break;
            case E_NOTICE:
                self::Notice($this->errstr, 1);
                break;
            case E_USER_ERROR:
                $this->RecoverableError("Fatal Error ");
                break;
            case E_USER_WARNING:
                self::warning("warning ", 1, $this->errfile, $this->errline);
                break;
            case E_USER_NOTICE:
                self::Notice($this->errstr, 1);
                break;
            case E_RECOVERABLE_ERROR:
                $this->RecoverableError("Catchable fatal error ", 1);
                break;
            default:
                echo $this->errstr;
                return false;
        }
        return true;
    }

    public function RecoverableError($type, $fatal = true)
    {

        if (self::$debung['error_reporting'] == 0)
        {

            $error = $type;
        } else
        {
            $trace = array_slice(debug_backtrace(), 4, NULL);
            $end = is_null(self::$EndTrace) ? count($trace) : self::$EndTrace;
            if ($end < 0)
            {
                $end = count($trace) + $end;
            }

            $error = self::TraceAsString([$type, $this->errfile, $this->errline, $this->errno], $this->errstr, $trace, 0, $end);
        }

        if ($fatal)
        {
            echo $error;
            exit;
        } else
        {
            return $error;
        }
    }

    public static function Warning($error, $tr = 0, $file = NULL, $line = NULL)
    {
        if (self::$debung['error_reporting'] === 0)
            return;

        $trance = debug_backtrace();

        $war = 'Warning: ' . $error . ' en ' . (isset($trance[$tr + 1]['file']) ? $trance[$tr + 1]['file'] : $file) . ' linea ' . (isset($trance[$tr + 1]['file']) ? $trance[$tr + 1]['line'] : $line);

        echo $war;
        return $war;
    }

    public static function Notice($error, $tr = 0)
    {
        if (self::$debung['error_reporting'] === 0)
            return;

        $trance = debug_backtrace();

        $not = 'Notice: ' . $error . ' en ' . (isset($trance[$tr + 1]['file']) ? $trance[$tr + 1]['file'] : '') . ' linea ' . (isset($trance[$tr + 1]['file']) ? $trance[$tr + 1]['line'] : '');

        echo $not;
        return $not;
    }

}
