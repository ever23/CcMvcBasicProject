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
 * Interface ICache para implementar en clases manejadoras de cache 
 *
 * @author Enyerber Franco
 * @package Cc
 * @subpackage Cache
 */
interface ICache extends \ArrayAccess
{

    /**
     * 
     * @param \Cc\Config $c
     */
    public function __construct(Config $c);

    /**
     * realiza las operacion de almacenado de todo el cache sera ejecutado al final
     */
    public function Save();

    /**
     * obterndra un valor del cache
     * @param string $name
     * @return mixes valor del indice
     */
    public function Get($name);

    /**
     * debe insertar un valor en el cache 
     * @param string $name
     * @param mixes $value
     */
    public function Set($name, $value);

    /**
     * debe borra el cache de el indice indicado
     * @param string $name
     */
    public function Delete($name);

    /**
     * indicara si el indice existe en el cache
     * @param string $name
     * @return bool 
     */
    public function IsSave($name);

    /**
     * deve eliminar todo el cache existente 
     */
    public function Destruct();
}
