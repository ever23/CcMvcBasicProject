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

namespace Cc;

/**
 * Description of CaheFile
 * Clase manejadora de Cache que lo almacena en un archivo local
 * @author Enyerber Franco
 * @version 1.0.0.5
 * @package Cc
 * @subpackage Cache
 */
class MemCached extends AbstracCache
{

    public $VersionCache = '1.0.0.5';

    /**
     * configuracion de la aplicacion 
     * @var \Cc\Config 
     */
    protected $Config;

    /**
     *
     * @var string 
     */
    protected $fileRead;

    /**
     *
     * @var string 
     */
    protected $expiretime = NULL;

    /**
     *
     * @var \Memcache 
     */
    protected $FileCache = NULL;

    /**
     * 
     * @param Config $conf
     */
    public function __construct(Config $conf)
    {
        $this->Config = $conf;
        $this->expiretime = $conf['Cache']['ExpireTime'];
        if (!class_exists('Memcache'))
        {
            throw new Exception("La extencion Memcache no esta instalada en php");
        }
        if (is_dir($conf['App']['app']) && !is_dir($conf['App']['Cache']))
        {
            mkdir($conf['App']['Cache']);
        }
        $this->changed = false;
        if (!isset($conf['Cache']['host']) || !isset($conf['Cache']['port']))
        {
            if (isset($conf['Cache']['servers']))
            {
                $this->FileCache = new \Memcache;
                $se = 0;
                foreach ($conf['Cache']['servers'] as $server)
                {
                    list($host, $port, $persistent) = $server;
                    if ($this->FileCache->addserver($host, $port, $persistent))
                    {
                        $se++;
                    }
                }
                if ($se == 0)
                {
                    $this->FileCache = false;
                }
            } else
            {
                throw new Exception("Debe especificar el host y el port del servidor mencache en el archivo de configuracion ");
            }
        } else
        {
            if (isset($conf['Cache']['persistent']) && $conf['Cache']['persistent'] == true)
            {
                $this->FileCache = memcache_pconnect($conf['Cache']['host'], $conf['Cache']['port']);
            } else
            {
                $this->FileCache = memcache_connect($conf['Cache']['host'], $conf['Cache']['port']);
            }
        }



        if ($this->FileCache)
        {
            ;
            if ($this->FileCache->get('VersionCache') != $this->VersionCache)
            {
                $this->FileCache->flush();
            }
        }
    }

    /**
     * retorna todo el contenido del cache
     * @return array
     */
    public function GetAllCache()
    {
        return $this->CAHCHE;
    }

    public function Set($name, $value, $expire = NULL)
    {


        if ($this->FileCache)
        {
            if (is_null($expire))
            {
                $expire = $this->expiretime;
            }

            if ($expire)
            {
                $time = new \DateTime;
                $time->add(date_interval_create_from_date_string($expire));
                $expire = $time->getTimestamp();
                // echo $time->format('Y/m/d H:i:s'),' ',$time->,' ',time();
            }
            $this->FileCache->set($name, $this->serialize($value), $expire);
        }
    }

    public function Get($name)
    {
        if ($this->FileCache)
        {

            return $this->FileCache->get($name);
        }
    }

    protected function serialize($value)
    {
        if (is_object($value))
        {
            if ($value instanceof \Serializable)
            {
                return $this->serialize($value->serialize());
            } elseif (method_exists($value, '__sleep'))
            {
                return $this->serialize($value->__sleep());
            } else
            {
                return (array) $value;
            }
        } elseif (is_array($value))
        {
            foreach ($value as $i => $v)
            {
                $value[$i] = $this->serialize($v);
            }
            return $value;
        } else
        {
            return $value;
        }
    }

    /**
     * almacena el cache en un archivo
     */
    public function Save()
    {

        if ($this->FileCache)
        {
            //  echo "changed";

            $this->set('VersionCache', $this->VersionCache);
            $this->set('ModifyTime', date('Y-m-d H:i:s'));


            $this->FileCache->close();
        }
    }

    /**
     * limpia el cache
     */
    public function Destruct()
    {
        if ($this->FileCache)
        {
            $this->FileCache->flush();
        }
    }

//put your code here
}
