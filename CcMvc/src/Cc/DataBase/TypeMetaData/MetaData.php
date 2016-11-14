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

namespace Cc\DB\MetaData;

/**
 * INTERFACE QUE IMPLEMENTARAN LAS CLASES DESTINADAS A MANEJAR LOS METADATOS DE LAS BASES DE DATOS
 *
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>
 * @package Cc
 * @subpackage DataBase  
 * @category MetaData
 */
interface iMetaData extends \JsonSerializable
{

    public function __toString();
}

/**
 * Description of Date
 *
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>
 * @package Cc
 * @subpackage DataBase  
 * @category MetaData
 */
class date extends \DateTime implements iMetaData
{

    protected $key;

    public function __construct($time, $key)
    {
        $this->key = $key;
        if ($time instanceof \DateTime)
        {
            $time = $time->format(self::W3C);
        }
        parent::__construct($time);
    }

    public function __toString()
    {
        return $this->format("Y-m-d");
    }

    public function jsonSerialize()
    {
        return $this->format("Y-m-d");
    }

}

/**
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>
 * @package Cc
 * @subpackage DataBase  
 * @category MetaData
 */
class time extends \DateTime implements iMetaData
{

    protected $key;

    public function __construct($time, $key)
    {
        $this->key = $key;
        if ($time instanceof \DateTime)
        {
            $time = $time->format(self::W3C);
        }
        parent::__construct($time);
    }

    public function __toString()
    {
        return $this->format("H:i:s");
    }

    public function jsonSerialize()
    {
        return $this->format("H:i:s");
    }

}

/**
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>
 * @package Cc
 * @subpackage DataBase  
 * @category MetaData
 */
class datetime extends \DateTime implements iMetaData
{

    protected $key;

    public function __construct($time, $key)
    {
        $this->key = $key;
        if ($time instanceof \DateTime)
        {
            $time = $time->format(self::W3C);
        }
        parent::__construct($time);
    }

    public function __toString()
    {
        return $this->format("Y-m-d H:i:s");
    }

    public function jsonSerialize()
    {
        return $this->format("Y-m-d H:i:s");
    }

}

/**
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>
 * @package Cc
 * @subpackage DataBase  
 * @category MetaData
 */
class timestamp extends \DateTime implements iMetaData
{

    protected $key;

    public function __construct($time, $key)
    {
        $this->key = $key;
        if ($time instanceof \DateTime)
        {
            $time = $time->format(self::W3C);
        }
        parent::__construct($time);
    }

    public function __toString()
    {
        return $this->getTimestamp();
    }

    public function jsonSerialize()
    {
        return $this->getTimestamp();
    }

}
