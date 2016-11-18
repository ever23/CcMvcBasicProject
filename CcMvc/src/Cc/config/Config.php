<?php

namespace Cc;

/**
 * carga un archivo de configuracion
 * @autor ENYREBER FRANCO       <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                    
 * @package Cc
 * @subpackage Configuracion
 * @property array $App 
 */
class Config implements \ArrayAccess
{

    protected $config = array();
    public $default = array();
    protected $orig = [];

    /**
     * 
     * @param string $DEF nombre del archivo de configuracion por defecto
     */
    public function __construct($DEF = 'DefaultConfig.php')
    {
        $this->default = include($DEF);
    }

    public function __debugInfo()
    {
        return $this->config;
    }

    /**
     * carga un archivo de configuracion en formato de array retornado
     * @param string|array $config_name nombre del archivo
     */
    public function Load($config_name)
    {

        if (is_string($config_name))
        {
            $File = new \SplFileInfo(realpath($config_name));
            $this->config = $this->default;
            if (!$File->isFile())
            {
                throw new Exception("el archivo de configuracion " . realpath($config_name) . " no existe");
            }
            if ($File->getExtension() == 'ini')
            {
                $conf = $this->LoadIni($config_name, true);
            } else
            {

                $this->orig = include($config_name);
            }
        } elseif (is_array($config_name))
        {
            $this->orig = $config_name;
        }


        $this->LoadConf($this->config, $this->orig);



        $this->config['App'] = $this->RemplaceApp($this->config['App']);
    }

    protected function RemplaceApp($AppC)
    {
        $app = [];
        if (($realPath = realpath($AppC['app'])) !== false)
        {
            $dirApp = $realPath . DIRECTORY_SEPARATOR;
        }

        foreach ($AppC as $i => $v)
        {

            if ($i == 'Cache')
            {
                $app[$i] = str_replace("{App}", $dirApp, $v);
                if (!is_dir($app[$i]))
                    mkdir($app[$i]);
                if (($realPath = realpath($app[$i])) !== false)
                {
                    $app[$i] = $realPath . DIRECTORY_SEPARATOR;
                }
            } elseif ($i == 'procedimientos')
            {
                $app[$i] = str_replace("{App}", $dirApp, $v);

                if (is_dir($app[$i]))
                {
                    $app[$i] = realpath($app[$i]) . DIRECTORY_SEPARATOR;
                }
            } else
            {
                $app[$i] = str_replace("{App}", $dirApp, $v);
                if (($realPath = realpath($app[$i])) !== false)
                {
                    $app[$i] = $realPath . DIRECTORY_SEPARATOR;
                }
            }
        }
        return $app;
    }

    /**
     *  @access private
     * @param type $default
     * @param type $conf
     * @return type
     */
    protected function LoadConf(&$default, &$conf)
    {
        foreach ($conf as $i => $v)
        {
            if (is_array($v))
            {
                if (!isset($default[$i]))
                    $default[$i] = array();
                $default[$i] = $this->LoadConf($default[$i], $conf[$i]);
            }else
            {
                $default[$i] = $conf[$i];
            }
        }
        return $default;
    }

    protected function LoadIni($filename)
    {
        return parse_ini_file($filename, true);
    }

    /**
     *  @access private
     * @param type $offset
     * @param type $value
     */
    public function offsetSet($offset, $value)
    {
        $this->config[$offset] = $value;
    }

    /**
     *  @access private
     * @param type $offset
     * @return type
     */
    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    /**
     *  @access private
     * @param type $offset
     */
    public function offsetUnset($offset)
    {
        //	$this->Set($offset,NULL,time()-1000);
        unset($this->config[$offset]);
    }

    /**
     * @access private
     * @param type $offset
     * @return type
     */
    public function offsetGet($offset)
    {
        if (!isset($this->config[$offset]))
        {
            ErrorHandle::Notice(" Undefined index " . $offset);
            $offset = NULL;
            return $offset;
        }
        return $this->config[$offset];
    }

    public function __get($offset)
    {
        if (!isset($this->config[$offset]))
        {
            ErrorHandle::Notice(" Undefined index " . $offset);
            $offset = NULL;
            return $offset;
        }
        return $this->config[$offset];
    }

    public function __set($name, $value)
    {
        $this->config[$name] = $value;
    }

    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

}
