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
 * Description of ValidFilename
 * realiza validacion a nombre de archivos 
 * @author Enyerber Franco
 * @package Cc
 * @subpackage Dependencias
 */
class ValidFilename extends ValidDependence
{

    public function Validate(&$value)
    {
        return self::ValidName($value, true);
    }

    /**
     * realizara la validacion de un nombre de archivo para evitar ataques al sistema de archivos 
     * @param string $str nombre del archivo
     * @param bool $relative_path indica si de permitira direccion relativa 
     * @return string nombre del archivo saneado 
     */
    public static function ValidName($str, $relative_path = FALSE)
    {
        $bad = array(
            '../', '<!--', '-->', '<', '>',
            "'", '"', '&', '$', '#',
            '{', '}', '[', ']', '=',
            ';', '?', '%20', '%22',
            '%3c', // <
            '%253c', // <
            '%3e', // >
            '%0e', // >
            '%28', // (
            '%29', // )
            '%2528', // (
            '%26', // &
            '%24', // $
            '%3f', // ?
            '%3b', // ;
            '%3d'  // =
        );

        if(!$relative_path)
        {
            $bad[] = './';
            $bad[] = '/';
        }

        $str = (new Security())->remove_invisible_characters($str, FALSE);

        do
        {
            $old = $str;
            $str = str_replace($bad, '', $str);
        } while($old !== $str);

        return stripslashes($str);
    }

//put your code here
}
