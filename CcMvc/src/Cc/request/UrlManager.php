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
 * Description of UrlManager
 * @autor ENYREBER FRANCO       <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                    
 * @package Cc
 * @subpackage Request
 */
class UrlManager
{

    /**
     * CREA UNA URL VALIDA AGREGANDOLE LAS VALIABLES GET PASADAS EN UN ARRAY
     * @param string $url url
     * @param array $get variables get
     * @return string
     */
    public static function Href($url = NULL, array $get = array())
    {
        if (filter_var($url, FILTER_VALIDATE_EMAIL))
        {
            $url = 'mailto:' . $url;
        }

        $req = '';
        if (is_array($url))
        {
            $get = $url;
            $url = NULL;
        }
        if (!is_null($url))
        {
            $ex = explode('?', $url);
            if (count($ex) >= 1)
            {
                $req = '?';

                $url = $ex[0];
                unset($ex[0]);

                parse_str(urldecode(implode('?', $ex)), $agrs);
                $get = $get + $agrs;
            } else
            {
                $req = '&';
            }
            $cont = http_build_query($get);
            return $url . (($cont == '') ? '' : $req ) . $cont;
        } else
        {
            return self::Href($_SERVER['REQUEST_URI'], $get);
        }
    }

    public static function EncodeUrl($url)
    {

        $u = explode('/', $url);
        foreach ($u as $i => $v)
        {
            $u[$i] = rawurlencode($v);
        }
        $url2 = implode('/', $u);
        return $url2;
    }

    public static function BuildUrl($protocol, $host, $base_path, $url = '')
    {
        $base_path = self::EncodeUrl($base_path);


        $protocol = mb_strtolower($protocol) . '://';
        if (strlen($url) == 0)
        {
            //return $protocol . $host . rtrim($base_path, "/\\") . "/";
            return $protocol . $host . $base_path;
        }

        // Is the url already fully qualified, a Data URI, or a reference to a named anchor?
        if (mb_strpos($url, "://") !== false || mb_substr($url, 0, 1) === "#" || mb_strpos($url, "data:") === 0 || mb_strpos($url, "mailto:") === 0)
        {
            return $url;
        }

        $ret = $protocol;

        if (!in_array(mb_strtolower($protocol), array("http://", "https://", "ftp://", "ftps://", "ws://")))
        {
            //On Windows local file, an abs path can begin also with a '\' or a drive letter and colon
            //drive: followed by a relative path would be a drive specific default folder.
            //not known in php app code, treat as abs path
            //($url[1] !== ':' || ($url[2]!=='\\' && $url[2]!=='/'))
            if ($url[0] !== '/' && (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' || ($url[0] !== '\\' && $url[1] !== ':')))
            {
                // For rel path and local acess we ignore the host, and run the path through realpath()
                $ret .= realpath($base_path) . '/';
            }
            $ret .= $url;
            $ret = preg_replace('/\?(.*)$/', "", $ret);
            return $ret;
        }

        // Protocol relative urls (e.g. "//example.org/style.css")
        if (strpos($url, '//') === 0)
        {
            $ret .= substr($url, 2);
            //remote urls with backslash in html/css are not really correct, but lets be genereous
        } elseif ($url[0] === '/' || $url[0] === '\\')
        {
            // Absolute path
            $ret .= $host . $url;
        } else
        {
            // Relative path
            //$base_path = $base_path !== "" ? rtrim($base_path, "/\\") . "/" : "";
            $ret .= $host . $base_path . $url;
        }

        return $ret;
    }

}
