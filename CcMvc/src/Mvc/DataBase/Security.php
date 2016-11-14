<?php

namespace Cc\Mvc;

use Cc\Mvc;

/**
 * @package CcMvc
 * @subpackage DataBase
 * 
 */
class SQLi
{

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
