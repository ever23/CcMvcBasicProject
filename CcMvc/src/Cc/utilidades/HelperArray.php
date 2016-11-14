<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cc;

/**
 * 
 * @autor ENYREBER FRANCO       <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                    
 * @package Cc
 * @subpackage Helper 
 */
class HelperArray
{

    /**
     * convierte todo el texto del array en minusculas recursivamente
     * @param array $array
     * @return array
     */
    public static function TolowerRecursive(array $array)
    {
        $f = function ($v) use (&$f)
        {
            if (is_array($v))
            {
                return array_map($f, $v);
            }
            return strtolower($v);
        };
        return array_map($f, $array);
    }

    /**
     * convierte todo el texto del array en mayusculas recursivamente
     * @param array $array
     * @return array
     */
    public static function ToupperrRecursive(array $array)
    {
        $f = function ($v) use (&$f)
        {
            if (is_array($v))
            {
                return array_map($f, $v);
            }
            return strtoupper($v);
        };
        return array_map($f, $array);
    }

    /**
     * une dos array recursivamente
     * @param array $default
     * @param array $conf
     * @return array
     */
    public static function MergeRecursive(&$default, &$conf)
    {

        foreach ($conf as $i => $v)
        {
            if (is_array($v))
            {
                if (!isset($default[$i]))
                    $default[$i] = array();
                $default[$i] = self::MergeRecursive($default[$i], $conf[$i]);
            }else
            {
                $default[$i] = $conf[$i];
            }
        }
        return $default;
    }

}
