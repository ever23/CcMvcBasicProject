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
class CacheFile extends AbstracCache
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
     * @var \SplFileInfo 
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
        if (is_dir($conf['App']['app']) && !is_dir($conf['App']['Cache']))
        {
            mkdir($conf['App']['Cache']);
        }
        $this->changed = false;
        if (file_exists($conf['App']['Cache'] . $conf['Cache']['File'] . '.php'))
        {
            $this->FileCache = new \SplFileInfo($conf['App']['Cache'] . $conf['Cache']['File'] . '.php');
            //  $time = new \DateTime(date('Y/m/d H:i:s', $this->FileCache->getMTime()));
            ob_start();
            $this->fileRead = include($this->FileCache);
            // echo $this->fileRead;

            $this->CAHCHE = @unserialize($this->fileRead);
            ob_end_clean();
            if (!$this->CAHCHE)
            {
                $this->CAHCHE = [];
                $this->changed = true;
            }
            if (!isset($this->CAHCHE['VersionCache']) || $this->CAHCHE['VersionCache'] != $this->VersionCache)
            {
                $this->CAHCHE = [];
                $this->changed = true;
                // unlink($this->FileCache);
            }
        } else
        {
            $this->FileCache = $conf['App']['Cache'] . $conf['Cache']['File'] . '.php';

            $this->CAHCHE = [];
            $this->changed = true;
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
        if (is_null($expire))
        {
            $expire = $this->expiretime;
        }
        parent::Set($name, $value, $expire);
    }

    /**
     * almacena el cache en un archivo
     */
    public function Save()
    {

        if ($this->changed)
        {
            //  echo "changed";

            $this->CAHCHE['VersionCache'] = $this->VersionCache;
            $save = '<?php return ' . var_export(serialize($this->CAHCHE), true) . ';';

            @file_put_contents($this->FileCache, $save, LOCK_EX);
        }
    }

    /**
     * limpia el cache
     */
    public function Destruct()
    {
        $this->CAHCHE = [];
        $this->changed = true;
    }

//put your code here
}
