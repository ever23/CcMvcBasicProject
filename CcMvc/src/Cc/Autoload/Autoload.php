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

include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'CoreClass.php';

/**
 * carga automaticamente las clases del directorio indicado
 * <code>
 * <?php
 * include_once 'Autoload/Autoload.php';
 * new Cc\Autoload(dirname(__FILE__).'/clases');
 * </code>
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com> 
 * @version 1.0     
 * @package Cc
 * @subpackage Autoload
 * 
 */
class Autoload
{

    use \Cc\Autoload\CoreClass
    {
        autoloadCore as private Autoload;
    }

    protected $loaded = [];
    protected $debung = false;

    /**
     * INICIA EL AUTOCARGADO DE CLASES, TRAITS Y INTERFACES
     * @param string $dirname DIRECTORIO DONDE EL AUTOCARGADOR BUSCARA LAS CLASES
     * @param boolean $restart indica que si el cache se reinicia el sitio debe recargarse 
     * @return \self
     */
    public static function Start($dirname, $restart = true, $remap = false)
    {
        return new self($dirname, $restart);
    }

    /**
     * 
     * @param string $dirname DIRECTORIO DONDE EL AUTOCARGADOR BUSCARA LAS CLASES
     * @param boolean $restart indica que si el cache se reinicia el sitio debe recargarse 
     */
    public function __construct($dirname, $restart = true, $debung = false)
    {
        $this->debung = $debung;
        $this->FileCoreClass = DIRECTORY_SEPARATOR . \Cc\Autoload\FileCore;
        $this->AppDir = realpath($dirname);

        $this->StartAutoloadCore($dirname, $restart);
    }

    /**
     * CARGA LAS CLASES DEL CORE DEL FRAMEWORK
     * @param string $class
     * @return boolean
     * @internal callback para spl_autoload_register
     */
    public function autoloadCore($class)
    {
        if (isset($this->CoreClass[$class]) && file_exists($this->AppDir . DIRECTORY_SEPARATOR . $this->CoreClass[$class]))
        {
            include_once($this->AppDir . DIRECTORY_SEPARATOR . $this->CoreClass[$class]);
            if (class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false))
            {
                $this->loaded[$class] = $this->AppDir . DIRECTORY_SEPARATOR . $this->CoreClass[$class];
                return true;
            }
        }

        return false;
    }

    public function getLoaded()
    {
        return $this->loaded;
    }

    /**
     * 
     */
    public function Stop()
    {
        $this->StopAutoloadCore();
    }

}
