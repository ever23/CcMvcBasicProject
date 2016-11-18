<?php

/**
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

namespace Cc\Mvc\Minify;

/**
 * Adaptacion de \MatthiasMullie\Minify\CSS para el uso en el framework
 *
 * @author  Enyerber Franco
 */
class CSS extends \MatthiasMullie\Minify\CSS
{

    protected $importExtensions = array(
    );

    /**
     * agrega un string css añadiendole un nombre de archivo en el contexto
     * @param string $data
     * @param string $contex
     */
    public function addContext($data, $contex)
    {
        // redefine var
        $data = (string) $data;
        $contex = (string) $contex;
        // load data
        $value = $this->load($data);
        $key = ($data != $value) ? $data : count($this->data);

        // replace CR linefeeds etc.
        // @see https://github.com/matthiasmullie/minify/pull/139
        $value = str_replace(array("\r\n", "\r"), "\n", $value);

        // store data

        $this->data[$contex] = $value;
    }

}
