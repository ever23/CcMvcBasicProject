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
 *
 *  
 */

namespace Cc\Mvc\Twig;

/**
 * cargador de templetes para twig
 *
 * @author Enyerber Franco
 * @package CcMvc
 * @subpackage Adapters
 */
class Loader implements \Twig_LoaderInterface
{

    public function getCacheKey($name)
    {

        return $name;
    }

    public function getSource($name)
    {
        if (!file_exists($name))
        {
            throw new \Twig_Error_Loader("El archivo " . $name . " no existe");
        }
        return file_get_contents($name);
    }

    public function isFresh($name, $time)
    {
        if (!file_exists($name))
        {
            throw new \Twig_Error_Loader("El archivo " . $name . " no existe");
        }
        return filemtime($name) <= $time;
    }

//put your code here
}
