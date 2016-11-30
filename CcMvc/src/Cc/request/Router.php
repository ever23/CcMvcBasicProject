<?php

/**
 * enruta controladores y archivos 
 * @autor ENYREBER FRANCO       <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                    
 * @package Cc
 * @subpackage Request
 */

namespace Cc;

use Cc\Autoload\SearchClass;

class Router
{

    protected $config;

    /**
     *
     * @var \SplFileInfo 
     */
    public $InfoFile = NULL;

    public function __construct($Conf)
    {
        $this->config = $Conf;
    }

    /**
     * INDICA SI EL ARCHIVO REQUERIDO EXISTE Y ES ENRUTAR CON RouterFile
     * @param string $orig_path OPCIONAL EL DOCUMENT ROOT DE LA APLIACION
     * @param strig $path OPCIONAL EL ARCHIVO REQUERIDO 
     * @return bool
     */
    public function &IsEnrutableFile($orig_path = NULL, $path = NULL)
    {
        $orig_path = is_null($orig_path) ? $_SERVER["DOCUMENT_ROOT"] : $orig_path;

        //$path = is_null($path) ? $this->RequestFilename : $path;
        if (($p = realpath($orig_path)) !== false)
        {
            $orig_path = $p;
        }
        if ($path[0] != '/')
            $path = '/' . $path;
        $this->InfoFile = NULL;
        $this->InfoFile = new \SplFileInfo(str_replace("/", DIRECTORY_SEPARATOR, $orig_path . $path));

        $ret = NULL;
        // echo $this->InfoFile;
        try
        {

            if ($this->InfoFile->isFile())
            {
                $ret = $this->InfoFile;
            } else
            {
                foreach ($this->config['DefaultOpenFile'] as $file)
                {

                    $f = realpath($this->InfoFile->__toString()) . DIRECTORY_SEPARATOR . $file;

                    if (is_file($f))
                    {
                        $ret = $this->InfoFile = new \SplFileInfo($f);

                        break;
                    } else
                    {
                        $ret = NULL;
                    }
                }
            }
        } catch (\RuntimeException $e)
        {
            //Mvc::App()->Log($e);
            $ret = NULL;
        }
        return $ret;
    }

    /**
     * carga el achivo requerido y aplica las headers correspondientes al archivo
     * @param SplFileInfo $splinfo
     */
    public function RouterFile(\SplFileInfo &$splinfo = NULL)
    {
        if (is_null($splinfo))
        {
            $splinfo = &$this->InfoFile;
        }
        if ($splinfo->getExtension() == 'php')
        {

            SearchClass::StopAutoloadClass();
            self::LoadFilePhp($splinfo);
        } else
        {

            if (self::HeadersReponseFiles($splinfo, '', NULL))
                readfile($splinfo);
            exit;
        }
    }

    /**
     * 
     * @param \SplFileInfo $spl
     */
    protected static function LoadFilePhp(\SplFileInfo &$spl)
    {


        $_SERVER['SCRIPT_FILENAME'] = $spl->getLinkTarget();


        foreach (headers_list() as $v)
        {
            $e = explode(':', $v);
            header_remove($e[0]);
        }
        unset($e);

        //echo '<pre>';print_r($_SERVER);
        header("X-Powered-By: PHP/" . PHP_VERSION . "  ");
        if (class_exists('\\Runkit_Sandbox', false))
        {
            $php = new \Runkit_Sandbox();
            $php->include($spl->getLinkTarget());
            unset($php);
        } else
        {
            include $spl->getLinkTarget();
        }




        // exit;
    }

    public static function HeadersReponseFiles($spl, $ContentType, $caheExpire = NULL, $reenv = false)
    {
        if ($spl instanceof \SplFileInfo)
        {
            $Mtime = $spl->getMTime();
        } else
        {
            $Mtime = $spl;
        }

        header('Content-type: ' . $ContentType);
        $last = [0];
        $LType = 'Wed';
        if ($caheExpire)
        {
            $ActTime = new \DateTime();

            $time = new \DateTime();
            $edad = $time->getTimestamp() - $Mtime;

            $ActTime->add(date_interval_create_from_date_string($caheExpire));
            header("Cache-Control: public, max-age=" . $ActTime->getTimestamp(), true);
            header("Age:" . $edad, true);
            header("Expires:" . $ActTime->format("D, d M y H:i:s"), true);
        }

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
        {
            $last = explode(',', $_SERVER['HTTP_IF_MODIFIED_SINCE']);

            $LastTime = new \DateTime(trim(array_pop($last)));
            if (error_get_last())
            {
                $reenv = true;
            }

            if ($Mtime === $LastTime->getTimestamp() && !$reenv)
            {

                // header("ETag: " . $_SERVER['HTTP_IF_NONE_MATCH']);
                header("Last-Modified:" . date("D, d M y H:i:s", $Mtime), true);
                header("Date:" . date("D, d M y H:i:s"), true);
                header('304 Not Modified', true, 304);
                return false;
            }
        }
        header("Last-Modified: " . date("D, d M y H:i:s", $Mtime), true);
        header("Date: " . date("D, d M y H:i:s"), true);
        return true;
    }

    public function is_aceptable($Controller, $Method = NULL, $Paquete = NULL, $ext = NULL)
    {
        $match = "/^[0-9a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/";
        return !preg_match($match, $Controller) || (!is_null($Method) && !preg_match($match, $Method)) || (!is_null($Paquete) && !preg_match($match, $Paquete)) || (!is_null($ext) && !preg_match($match, $ext));
    }

}
