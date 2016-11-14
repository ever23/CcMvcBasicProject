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

use Cc\Autoload\SearchClass;
use Cc\Autoload\SearchClassException;
use Cc\Autoload\CoreClass;
use Cc\Autoload;

/**
 * carga automaticamente las clases de librerias externas
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>      
 * @package Cc
 * @subpackage Autoload
 *
 */
class AutoloadExternLib
{

    private $Conf = [];
    private $PathToExternLib;
    protected $lastFiles = [];
    protected $confLoaders = false;
    protected $debung = true;
    protected $loaders = [];

    use CoreClass;

    /**
     * 
     * @param array $conf
     * @param string $dir
     * @param boolean $debung
     */
    public function __construct(array $conf, $dir, $debung = false)
    {
        $this->debung = $debung;
        $this->Conf = $conf;
        $this->PathToExternLib = realpath($dir) . DIRECTORY_SEPARATOR;
    }

    public function StartAutoloader()
    {
        if (!$this->confLoaders)
            foreach ($this->Conf['AutoloadersFiles'] as $file)
            {
                $this->AddAutoloader($file);
            }
        if ($this->Conf['UseStandarAutoloader'])
        {
            if (is_array($this->Conf['UseStandarAutoloader']))
            {
                foreach ($this->Conf['UseStandarAutoloader'] as $d)
                {
                    $dir = is_array($d) ? $d[0] : $d;

                    if (is_dir($dir))
                    {

                        $this->loaders[] = Autoload::Start($dir);
                    } elseif (!is_null($this->PathToExternLib) && is_dir($this->PathToExternLib . $dir))
                    {
                        $this->loaders[] = Autoload::Start($this->PathToExternLib . $dir);
                    } else
                    {
                        throw new Exception("EL DIRECTPRIO " . ($this->PathToExternLib . $dir) . " INDICADO EN UseStandarAutoloader NO EXISTE ");
                    }
                }
            } else
            {
                $this->loaders[] = Autoload::Start($this->PathToExternLib);
            }
        }
        $this->confLoaders = true;
        spl_autoload_register([$this, '__autoload']);
    }

    public function StopAutoloader()
    {
        spl_autoload_unregister([$this, '__autoload']);
    }

    public function AddAutoloader($f)
    {
        if (is_file($f))
        {
            include_once ($f);
        } elseif (!is_null($this->PathToExternLib) && is_file($this->PathToExternLib . $f))
        {
            include_once ($this->PathToExternLib . $f);
        } else
        {
            throw new Exception(" EL ARCHIVO DE LIBRERIA  EXTERNA " . $f . " NO EXISTE");
        }
    }

    public function __autoload($class)
    {

        if ($this->Conf['NamespacesForDir'])
        {
            $c = explode("\\", $class);
            $newClass = array_pop($c);
            $namespaceDir = implode($c, DIRECTORY_SEPARATOR);

            foreach ($this->Conf['NamespacesForDir'] as $name => $v)
            {
                if (0 === strncmp($name, $namespaceDir, strlen($name)))
                {

                    $resNamespace = str_replace("\\", DIRECTORY_SEPARATOR, substr($namespaceDir, strlen($name))) . DIRECTORY_SEPARATOR;
                    if (is_array($v))
                    {
                        foreach ($v as $dir)
                        {
                            $file = realpath($this->PathToExternLib . $dir . $resNamespace) . DIRECTORY_SEPARATOR . $newClass . ".php";
                            if (is_file($file))
                            {

                                require_once $file;
                                break;
                            }
                        }
                    } else
                    {
                        $file = realpath($this->PathToExternLib . $v . $resNamespace) . DIRECTORY_SEPARATOR . $newClass . ".php";
                        if (is_file($file))
                        {
                            require_once $file;
                            break;
                        }
                    }
                }
            }
            if ($this->VerifyExisClass($class))
            {
                $this->lastFiles[$class] = $file;
                return true;
            }
        }
        if ($this->debung && $this->Conf['UseStandarAutoloader'])
        {
            if (is_array($this->Conf['UseStandarAutoloader']))
            {
                foreach ($this->Conf['UseStandarAutoloader'] as $d)
                {
                    $dir = is_array($d) ? $d[0] : $d;
                    if (is_dir($dir))
                    {

                        if ($this->FindClass($class, $dir, $d[1]))
                        {
                            return true;
                        }
                    } elseif (is_dir($this->PathToExternLib . $dir))
                    {
                        if ($this->FindClass($class, $this->PathToExternLib . $dir, $d[1]))
                        {
                            return true;
                        }
                    } else
                    {
                        throw new Exception("EL DIRECTPRIO " . ($dir) . " INDICADO EN UseStandarAutoloader NO EXISTE ");
                    }
                }
            } else
            {
                return $this->FindClass($class, $this->PathToExternLib);
            }
        }
        return false;
    }

    private function FindClass($class, $path, $hard = false)
    {
        $path = realpath($path) . DIRECTORY_SEPARATOR;
        $autoload = new SearchClass($class, $path, $hard);
        if ($autoload->Search())
        {

            try
            {
                $autoload->Include_();
                if ($this->VerifyExisClass($class))
                {
                    if (file_exists($path . \Cc\Autoload\FileCore))
                    {
                        unlink($path . \Cc\Autoload\FileCore);
                    }
                    $this->lastFiles[$class] = $autoload->GetFileName();
                    return true;
                } else
                {
                    
                }
            } catch (SearchClassException $ex)
            {
                return false;
            }
        }
        return false;
    }

    private function VerifyExisClass($class_name)
    {
        if (class_exists($class_name, false) || interface_exists($class_name, false) || trait_exists($class_name, false))
        {
            return true;
        }
        return false;
    }

    public function GetLastLoadFiles()
    {
        foreach ($this->loaders as $autoload)
        {
            if ($autoload instanceof Autoload)
            {
                $this->lastFiles+=$autoload->getLoaded();
            }
        }
        return $this->lastFiles;
    }

    //put your code here
}
