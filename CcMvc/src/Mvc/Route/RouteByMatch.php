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
 * Description of RouteByMatch
 *
 * @author usuario
 */
class RouteByMatch
{

    protected $routes;
    protected $path;
    protected $PartsPath = [];
    protected $NpartsPath = 0;
    protected $params = [];
    protected $replace = [];
    protected $isCalable = false;
    protected $origRegex = '';

    public function __construct($path, $routes)
    {
        $this->routes = $routes;
        $this->path = $path;
    }

    public function GetParams()
    {
        return $this->params;
    }

    public function GetOrigRegex()
    {
        return $this->origRegex;
    }

    public function IsCalableRoute()
    {
        return $this->isCalable;
    }

    public function compile()
    {
        $this->PartsPath = preg_split('/\/|\./', $this->path);
        $this->NpartsPath = count($this->PartsPath);
        $this->params = [];
        $this->replace = [];
        $this->isCalable = false;
        $this->origRegex = '';
        $v = false;
        foreach ($this->routes as $path => $contr)
        {
            list($controller, $repl, $mathvar) = $contr;

            $Rpath = substr($path, 1);
            $pathRegex = preg_split('/\/|\./', $Rpath);
            $verifi = false;
            $param = [];
            $this->replace = [];
            if (count($this->PartsPath) != count($pathRegex))
            {
                continue;
            } elseif ($this->CompileRegex($pathRegex, $controller, $mathvar))
            {

                $this->origRegex = $path;

                if (is_callable($controller))
                {
                    $this->isCalable = true;
                    return $controller;
                } else
                {
                    if (is_numeric($controller))
                    {
                        if (in_array($controller, [404, 403]))
                        {
                            Mvc::App()->LoadError($controller, " Via Enrutamiento manual");
                            exit;
                        }
                    }

                    if (preg_match('/\.\{.*\}$/U', $controller))
                    {

                        $ext = (new \SplFileInfo($this->path))->getExtension();
                        $controller = preg_replace('/\.\{.*\}$/U', '.' . $ext, $controller);
                    }

                    foreach ($this->replace as $r => $p2)
                    {

                        $controller = preg_replace($r, $p2, $controller);
                        // var_dump($p2);
                    }


                    return $controller;
                }
                return false;
            }
        }
        return false;
    }

    public function CompileRegex($pathRegex, $controller, $mathvar)
    {
        foreach ($this->PartsPath as $i => $p)
        {
            if (isset($pathRegex[$i]))
            {

                if ($p == $pathRegex[$i])
                {

                    continue;
                } elseif (preg_match('/(\{.*\})/U', $pathRegex[$i]))
                {

                    if ($this->EvalueRouteVars($pathRegex[$i], $p, $controller, $mathvar))
                    {

                        continue;
                    } else
                    {
                        return false;
                    }
                } else
                {
                    return false;
                }
            } else
            {
                return false;
            }
        }
        return true;
    }

    private function EvalueRouteVars($PathT, $pathP, $c, $mathvar, $match = ['\{', '\}'])
    {
        $split = preg_split('/(' . $match[0] . '.*' . $match[1] . ')/U', $PathT, PREG_SPLIT_DELIM_CAPTURE, -1);
        $explo = '';
        foreach ($split as $j => $sp)
        {
            if ($j % 2 != 0)
            {
                $explo = preg_quote($sp[0], '/') . '|';
            }
        }
        if ($explo == '')
        {
            $Pexplo = [$pathP];
        } else
        {
            $Pexplo = preg_split('/' . substr($explo, 0, -1) . '/', $pathP);
            $PExpAth = preg_split('/' . substr($explo, 0, -1) . '/', $PathT);
            if (count($Pexplo) != count($PExpAth))
            {
                return false;
            }
        }
        $z = 0;
        foreach ($split as $j => $sp)
        {
            if ($j % 2 == 0)
            {

                $name = preg_replace('/' . $match[0] . '|' . $match[1] . '/', '', $sp[0]);

                if (isset($mathvar[$name]) && !preg_match('/' . $mathvar[$name] . '/i', $Pexplo[$z]))
                {
                    return false;
                }

                $this->params[$name] = $Pexplo[$z];
                if (is_string($c) && preg_match('/(' . $match[0] . $name . $match[1] . ')/', $c))
                {
                    $m = '/' . preg_quote($sp[0], '/') . '/';
                    $this->replace[$m] = $Pexplo[$z];
                }
                $z++;
            }
        }
        return true;
    }

}
