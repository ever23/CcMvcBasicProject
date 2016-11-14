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
 * Description of AbstracCache
 *  clase abstracta para usar como base para clases manejadoras de cache
 * 
 * @author Enyerber Franco
 * @package Cc
 * @subpackage Cache
 */
abstract class AbstracCache implements ICache
{

    /**
     * indica si se realizaron cambios al cache
     * @var bool 
     */
    protected $changed = false;

    /**
     * almacenamiento en memoria de cache
     * @var array 
     */
    protected $CAHCHE = [];

    /**
     * 
     * @param string $name
     * @param mixes $value
     * @see ICache::Set()
     */
    public function Set($name, $value, $expire = NULL)
    {


        if ($expire)
        {
            $time = new \DateTime;
            $time->add(date_interval_create_from_date_string($expire));
            $expire = $time->getTimestamp();
            // echo $time->format('Y/m/d H:i:s'),' ',$time->,' ',time();
        }

        $this->changed = true;
        //echo var_dump($value);
        /* @var $expire \DateTime */
        $this->CAHCHE[$name] = [$value, $expire];
    }

    /**
     * 
     * @param string $name
     * @return mixes
     * @see ICache::Get()
     */
    public function Get($name)
    {
        $expire = new \DateTime('now');
        if (!is_null($this->CAHCHE[$name][1]) && $expire->getTimestamp() >= $this->CAHCHE[$name][1])
        {

            $this->Delete($name);

            //  $expire->diff($object, $absolute)
        //
        }
        //echo $name, date('Y/m/d H:i:s',$this->CAHCHE[$name][1]),' '. $expire->format('Y/m/d H:i:s');exit;
        return $this->CAHCHE[$name][0];
    }

    /**
     * 
     * @param string $name
     * @return bool
     * @see ICache::IsSave()
     */
    public function IsSave($name)
    {

        $expire = new \DateTime;
        return isset($this->CAHCHE[$name]) && (is_null($this->CAHCHE[$name][1]) || $expire->getTimestamp() < $this->CAHCHE[$name][1]);
    }

    public function offsetExists($offset)
    {
        return $this->IsSave($offset);
    }

    public function offsetGet($offset)
    {
        return $this->Get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->Set($offset, $value);
    }

    public function offsetUnset($offset)
    {

        $this->Delete($offset);
    }

    /**
     * 
     * @param string $name
     * @see ICache::Delete()
     */
    public function Delete($name)
    {
        $this->changed = true;
        unset($this->CAHCHE[$name]);
    }

}

class NoCache extends AbstracCache
{

    public function Save()
    {
        
    }

    public function __construct(Config $c)
    {
        $this->CAHCHE = [];
    }

    public function Destruct()
    {
        
    }

}
