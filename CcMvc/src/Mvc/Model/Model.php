<?php

/*
 * Copyright (C) 2016 Enyerber Franco
 *
 * This program is free sof--+.
 * tware: you can redistribute it and/or modify
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

/**
 * Description of Model
 * clase base para clase de modelo
 * @author Enyerber Franco
 * @package CcMvc
 * @subpackage Modelo
 */
abstract class Model implements \ArrayAccess, \JsonSerializable, \IteratorAggregate, ParseObjectSmartyTpl
{

    protected $_ValuesModel = [];

    public function __construct()
    {
        $this->_ValuesModel = $this->Campos();
    }

    /**
     * 
     */
    abstract protected function Campos();

    public function __debugInfo()
    {
        if (is_object($this->_ValuesModel))
        {
            if (method_exists($this->_ValuesModel, '__debugInfo'))
            {
                return $this->_ValuesModel->__debugInfo();
            } else
            {
                return (array) $this->_ValuesModel;
            }
        }

        return $this->_ValuesModel;
    }

    public function jsonSerialize()
    {
        return $this->_ValuesModel;
    }

    public function offsetExists($offset)
    {
        return (is_array($this->_ValuesModel) || $this->_ValuesModel instanceof \ArrayAccess ) && key_exists($offset, $this->_ValuesModel);
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset))
        {
            return $this->_ValuesModel[$offset];
        } else
        {
            ErrorHandle::Notice("Indice '" . $offset . "' no definido ");
        }
    }

    public function offsetSet($offset, $value)
    {

        $this->_ValuesModel[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->_ValuesModel[$offset]);
    }

    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function __get($name)
    {
        if ($this->offsetExists($name))
        {
            return $this->_ValuesModel[$name];
        } else
        {
            ErrorHandle::Notice("Propiedad '" . $name . "' no definida ");
        }
    }

    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    public function __unset($name)
    {
        $this->offsetUnset($name);
    }

    public function getIterator()
    {
        if ($this->_ValuesModel instanceof \Traversable)
        {
            return $this->_ValuesModel;
        }
        return new \ArrayIterator($this->_ValuesModel);
    }

    private $each = true;
    private $eachend = true;

    public function each($params, $content = NULL, &$smarty, &$repeat)
    {

        $repeat = true;
        if ($this->each)
        {
            if (!$this->eachend)
            {
                $this->rewind();
            }
            $fech = $this->current();
            $key = $this->key();
            $this->eachend = $this->next();

            $this->each = false;
            if (isset($params['row']))
            {
                $smarty->assign($params['row'], $fech);
            } else
            {
                $smarty->assign('row', $fech);
            }
            if (isset($params['key']))
            {
                $smarty->assign($params['key'], $key);
            } else
            {
                $smarty->assign('key', $key);
            }
        } else
        {
            $this->each = true;
            $repeat = $this->eachend;
            return $content;
            // $repeat = $this->next();
        }
    }

    public function ParseSmaryTpl()
    {
        return [
            'allowed' => [],
            'format' => true,
            'block_methods' => ['each']
        ];
    }

//put your code here
}
