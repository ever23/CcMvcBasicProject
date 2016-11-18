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

namespace Cc\Mvc;

use Cc\Mvc;

/**
 * fultro sqli 
 * @package CcMvc
 * @subpackage DataBase
 * 
 */
class SQLi
{

    /**
     * filtra las posibles inyecciones sql
     * @param string|array $text
     * @param array $exept
     * @return string|array
     */
    public static function Filter($text, $exept = [])
    {

        if (is_string($text))
        {
            try
            {

                return Mvc::App()->DataBase()->real_escape_string($text);
            } catch (\Exception $ex)
            {

                return $text;
            } catch (\Error $ex)
            {

                return $text;
            }
        } elseif (is_array($text))
        {
            foreach ($text as $i => $v)
            {
                $text[$i] = !in_array($i, $exept) ? self::Filter($v) : $v;
            }
            return $text;
        }
    }

}
