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
 * Description of Cache
 * administra las clase manejadoras de cache de forma abstracta 
 * provee una interfas de funciones statics para manejar el cache
 * @author Enyerber Franco
 * @package Cc
 * @subpackage Cache
 */
class Cache
{

    /**
     *
     * @var AbstracCache 
     */
    protected static $CACHE;

    /**
     *
     * @var Config 
     */
    protected static $Conf;
    protected static $debung = false;

    /**
     * inicia el cache 
     * @param Config $c
     * @throws \Exception ocurre si la clase manejadora de cache no existe 
     */
    public static function Start(Config &$c)
    {
        self::$Conf = &$c;

        $class = $c['Cache']['class'];
        if (class_exists($class) && (is_subclass_of($class, AbstracCache::class) || is_subclass_of($class, ICache::class)))
        {
            self::$CACHE = new $class($c);
        } else
        {
            throw new Exception("LA CLASE MANEJADORA DE CACHE NO EXISTE O NO ES VALIDA " . $class);
        }
        if (!isset(self::$Conf['debung'][0]))
        {

            if (!self::$Conf['Cache']['debung'])
            {
                self::$debung = true;
                self::$CACHE->Destruct();
            }
        }
    }

    public static function AutoClearCacheFile($directory)
    {
        $ActTime = new \DateTime();
        $ActTime->add(date_interval_create_from_date_string(self::$Conf['Cache']['ExpireTime']));
        $expire = $ActTime->getTimestamp();
        if (!is_dir($directory))
            return;
        if (rand(0, 1000) == 500 || self::$debung)
        {
            $dir = dir($directory);
            while ($file = $dir->read())
            {
                if ($file != '.' && $file != '..')
                {
                    if (is_file($directory . $file))
                    {
                        $Mtime = filemtime($directory . $file);

                        $age = time() - $Mtime;
                        if ($age > $expire || self::$debung)
                            @unlink($directory . $file);
                    }else
                    {
                        static::AutoClearCacheFile($directory . $file);
                    }
                }
            }
        }
    }

    public static function GetObjectCache()
    {
        return self::$CACHE;
    }

    /**
     * metodo static magico en esta clase funciona para llamar a un metodo no estatico del objeto manejador de cache 
     * @param string $name
     * @param array $arguments
     * @return mixes
     * @throws \Exception si el metodo no existe 
     */
    public static function __callStatic($name, $arguments)
    {
        if (method_exists(self::$CACHE, $name))
        {
            return self::$CACHE->$name(...$arguments);
        } else
        {
            throw new Exception("EL METODOD " . $name . " NO EXISTE EN LA CLASE " . get_class(self::$CACHE));
        }
    }

    /**
     * almacenara el cache 
     */
    public static function Save()
    {
        if (!isset(self::$Conf['debung'][0]))
        {

            if (!self::$Conf['Cache']['debung'])
            {

                self::$CACHE->Destruct();
            }
        }

        self::$CACHE->Save();
    }

    /**
     * indica si un idice existe en el cache
     * @param string $name
     * @return bool
     */
    public static function IsSave($name)
    {
        return self::$CACHE->IsSave($name);
    }

    /**
     * obtiene el valor de un indice del cache 
     * @param string $name
     * @return mixes
     */
    public static function Get($name)
    {
        return self::$CACHE->Get($name);
    }

    /**
     * inserta un valor en el cache
     * @param string $name
     * @param mixes $value
     */
    public static function Set($name, $value, $expire = NULL)
    {

        self::$CACHE->Set($name, $value, $expire);
    }

    /**
     * elimina un indice del cache 
     * @param string $name
     */
    public static function Delete($name)
    {
        self::$CACHE->offsetUnset($name);
    }

    public static function Clean()
    {
        self::$CACHE->Destruct();
    }

//put your code here
}
