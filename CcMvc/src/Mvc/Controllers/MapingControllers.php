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

namespace Cc\Mvc;

use Cc\Autoload\SearchClass;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

/**
 * MAPEA LOS CONTROLADORES EXISTENTES SE PUEDE USAR PARA VERIFICAR QUE LINKS TIENE ACTIVOS LA APLICACION
 * @package CcMvc
 * @subpackage Controladores
 * @todo se nesesita actualizar 
 */
class MapingControllers
{

    public $Controllres;
    protected $conf;
    protected $NoMethod;

    /**
     * 
     * @param Config $config
     * @uses MapingControllers 
     */
    public function __construct($config)
    {
        $this->NoMethod = [ '__call', '__callStatic', '__get', '__set', '__isset', '__unset', '__sleep', '__wakeup', '__toString', '__invoke', '__set_state', '__clone', '__debugInfo'];
        $this->conf = $config;
        $this->Controllres = $this->MapingControllers($this->conf['App']['controllers']);
    }

    /**
     * retorna un array con los posibles links de la aplicacion 
     * @return array
     */
    public function GetListLink()
    {
        $link = [];
        $plus = '';
        if ($this->conf['Router']['GetControllerFormat'] == Router::Get)
        {
            $plus = '?' . $this->conf['Router']['GetControllers'] . '=';
            $op = $this->conf['Router']['OperadorAlcance'];
        } else
        {
            $op = '/';
            $plus = $this->conf['Router']['DocumentRoot'];
        }

        foreach ($this->Controllres as $i => $v)
        {
            $paquete = $i;
            if ($i != '')
            {
                $paquete.= $op;
            }
            $class = '';
            foreach ($v as $ic => $vc)
            {
                $class = $ic;
                foreach ($vc as $im => $vm)
                {
                    $link[] = $plus . $paquete . $class . $op . $im;
                }
            }
        }
        return $link;
    }

    public function GetControllers()
    {
        return $this->Controllres;
    }

    /**
     * crea un mapa de los controladores
     * @param string $dirname
     * @return array
     */
    protected function MapingControllers($dirname)
    {
        SearchClass::ClearListClass();
        $Map = SearchClass::GetListAllClass($dirname);
        // echo '<pre>',$dirname, var_dump($Map);exit;
        $conf = $this->conf;
        $paquetes = $controllres = [];
        foreach ($Map as $i => $v)
        {
            if (!class_exists($i, true))
                include ($dirname . $v);

            $ex = explode(DIRECTORY_SEPARATOR, $v);
            $paquete = NULL;
            if (count($ex) > 1)
            {
                $paquete = $ex[0] == '' ? NULL : $ex[0];
            }

            $ref = new \ReflectionClass($i);
            if ($ref->isSubclassOf(Controllers::class))
            {
                $i = str_replace(__NAMESPACE__ . '\\', "", $i);

                $pac = explode('\\', $i);
                $class = array_pop($pac);
                if (!isset($paquetes[$paquete]))
                {
                    $paquetes[$paquete] = [];
                }
                $paquetes[$paquete][$class] = $this->MapingMethodController($ref);
            }
        }
        return $paquetes;
    }

    /**
     * crea un mapa de los metodos 
     * @param ReflectionClass $class
     * @return array
     */
    protected function MapingMethodController(ReflectionClass &$class)
    {
        $met = [];
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        $protectedMethod = [];
        /* @var $interface ReflectionClass */
        foreach ($class->getInterfaces()as $interface)
        {

            if ($interface->isSubclassOf(iProtected::class))
            {
                foreach ($interface->getMethods() as $mi)
                {
                    $protectedMethod[] = str_replace($interface->name, "", $mi->name);
                }
            }
        }
        if ($class->implementsInterface(ProtectedMetodHttp::class))
        {
            $c = $class->name;
            foreach ($c::MethodsNoHttp() as $ms)
            {
                $protectedMethod[] = $ms;
            }
        }

        /* @var $m ReflectionMethod */
        foreach ($methods as $m)
        {


            //echo str_replace($class->name, "", $m->name);
            if ($m->isConstructor() || $m->isDestructor() || in_array(str_replace($class->name, "", $m->name), $this->NoMethod) || in_array(str_replace($class->name, "", $m->name), $protectedMethod))
                continue;
            $p = [];
            /* @var $v ReflectionParameter */
            foreach ($m->getParameters() as $i => $v)
            {
                $type = '';
                $c = '';
                try
                {
                    $c = $v->getClass();
                } catch (\ReflectionException $ex)
                {
                    $c = (object) ['name' => '(undefined_class)'];
                }

                if (is_object($c))
                {
                    $type = $c->name;
                } else
                {
                    if (method_exists($v, 'getType') && $v->getType())
                    {
                        $type = $v->getType();
                    } elseif ($v->isArray())
                    {
                        $type = 'array';
                    }
                }
                $default = NULL;
                if ($v->isDefaultValueAvailable())
                {
                    $default = $v->getDefaultValue();
                }
                $p[$i] = ['name' => $v->name, 'type' => $type, 'default' => $default];
            }
            $met[$m->name] = $p;
        }
        return $met;
    }

}
