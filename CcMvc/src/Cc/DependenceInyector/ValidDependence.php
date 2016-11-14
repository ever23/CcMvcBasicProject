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
 * Description of ValidDependence
 *
 * @author Enyerber Franco
 * @package Cc
 * @subpackage Dependencias
 * @todo hay que cambiar el nombre de la clase a ParamDependence 
 */
abstract class ValidDependence implements \ArrayAccess, \IteratorAggregate
{

    /**
     *
     * @var mixes valor validado 
     */
    protected $value;

    /**
     *
     * @var string valor a validar  
     */
    protected $RecValue;

    /**
     * indica si se valido
     * @var bool 
     */
    protected $valido = false;

    /**
     * para utilizar en el metodo {@see ValidDependence::Filter()} en el tercer parametro 
     * idica que sera realizada retornando los valores validados estrictamente 
     */
    const StrictValidValues = 0x111;

    /**
     * para utilizar en el metodo {@see ValidDependence::Filter()} en el tercer parametro 
     * validado por defecto
     */
    const DefaultValid = 0x000;

    /**
     * para utilizar en el metodo {@see ValidDependence::Filter()} en el tercer parametro 
     * si la validacion falla el metodo deve retornar un booleano false
     */
    const ReturnedBool = 0x112;

    /**
     * para utilizar en el metodo {@see ValidDependence::Filter()} en el segundo parametro 
     * indica que se validara como entero
     */
    const ValidInt = 0x1;

    /**
     * para utilizar en el metodo {@see ValidDependence::Filter()} en el segundo parametro 
     * indica que se validara como string
     */
    const ValidString = 0x2;

    /**
     * para utilizar en el metodo {@see ValidDependence::Filter()} en el segundo parametro 
     * indica que se validara como flotante
     */
    const ValidFloat = 0x3;

    /**
     * para utilizar en el metodo {@see ValidDependence::Filter()} en el segundo parametro 
     * indica que se validara como array
     */
    const ValidArray = 0x4;

    public $option = ['requiered' => true];

    /**
     * 
     * @param mixes $value
     */
    public function __construct($value, $option = [])
    {
        $this->RecValue = $value;
        $this->option = $option + $this->option;
        $this->value = $this->Validate($value);

        if ($this->value == false || is_null($this->value))
        {
            if (isset($this->option['required']) && !$this->option['required'])
            {
                $this->valido = true;
                $this->value = '';
                $this->RecValue = NULL;
            } else
                $this->value = '';
        } else
        {
            $this->valido = true;
            $this->RecValue = NULL;
        }
    }

    public static function GetOptions($valid)
    {
        list($class, $p) = $valid;
        if ($class = self::getTypevalid($class))
        {
            $o = new $class("", ...$p);
            return $o->option;
        } else
        {
            return false;
        }
    }

    public static function CreateValid($options = [])
    {
        return [static::class, [$options], ValidDependence::class => true];
    }

    /**
     * indica si se valido correctamente
     * @return boolean
     */
    public function IsValid()
    {
        if (!is_array($this->value))
        {
            return $this->valido;
        } elseif ($this->value instanceof ValidDependence && !$this->value->IsValid())
        {
            return false;
        } else
        {

            foreach ($this->value as $i => $v)
            {
                if ($v instanceof ValidDependence && !$v->IsValid())
                {
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * retorna el valor validado
     * @return mixes
     */
    public function get()
    {
        if ($this->value instanceof self)
        {
            return $this->value->value;
        }
        return $this->value;
    }

    /**
     * valida un elemento 

     * @param \Traversable|array|mixes $value
     * @param array $type tipo de validacion pude ser las constantes definidas para validacion o el nombre de un clase de validacion 
     * si el primer parametro de un array o Travesable entonces este deve ser un array de tipo clave valor con el [indice=>typoValidacion]
     * @param int $flangs flangs de validacion 
     * @return ValidDependence|mixes|boolean
     */
    public static function Filter($value, $type = NULL, $flangs = self::DefaultValid)
    {
        $val = [];
        $validate = NULL;
        if (ValidDependence::class != static::class && is_null($type))
        {
            $validate = new static($value);
        } else
        {
            if ($value instanceof \Traversable || is_array($value))
            {
                if (is_array($type))
                {
                    foreach ($value as $i => $v)
                    {
                        if (isset($type[$i]))
                        {
                            $val[$i] = self::ProccessValue($v, $type[$i]);
                        } else
                        {
                            $val[$i] = $v;
                        }
                    }
                } else
                {
                    foreach ($value as $i => $v)
                    {
                        $val[$i] = self::ProccessValue($v, $type);
                    }
                }

                $validate = new ValidDefault($val);
            } else
            {
                $validate = self::ProccessValue($value, $type);
            }
        }

        switch ($flangs)
        {
            case self::StrictValidValues:
                return $validate->StrictValues();
            case self::ReturnedBool:
                if ($validate->IsValid())
                {
                    return $validate;
                } else
                {
                    return false;
                }
                break;
            case self::DefaultValid:
                return $validate;
        }
    }

    /**
     * procesa la validacion
     * @param \Traversable $value
     * @param mixes $type
     * @return boolean|\Cc\class|\Traversable
     * @throws \Exception
     */
    protected static function ProccessValue($value, $type)
    {
        $p = [];
        if (is_array($type))
        {
            $p = $type[1];
            $type = $type[0];
        }

        switch ($type)
        {
            case self::ValidInt:
                if (is_int($value) || is_string($value) || is_float($value))
                {
                    return (int) $value;
                } else
                {
                    return false;
                }
            case self::ValidFloat:
                if (is_int($value) || is_string($value) || is_float($value))
                {
                    return (float) $value;
                } else
                {
                    return false;
                }
            case self::ValidArray:
                if (is_array($value))
                {
                    return (array) $value;
                } elseif ($value instanceof \Traversable)
                {
                    $r = [];
                    foreach ($value as $i => $v)
                    {
                        $r[$i] = $v;
                    }
                    return $r;
                }

            case self::ValidString:

                if (!is_array($value))
                {
                    return (string) $value;
                }
            default:
                if ($class = self::getTypevalid($type))
                {
                    /* @var $valid ValidDefault */
                    $valid = new $class($value, ...$p);

                    return $valid;
                } else
                {
                    throw new Exception("typo de validacion " . $type . " no valido");
                    return $value;
                }
        }
    }

    /**
     * retorna los valores estrictos
     * @return mixes
     */
    protected function StrictValues()
    {
        if (is_array($this->value))
        {
            $valor = [];
            foreach ($this->value as $i => $v)
            {
                if ($v instanceof ValidDependence)
                {
                    $valor[$i] = $v->StrictValues();
                } else
                {
                    $valor[$i] = $v;
                }
            }
        } else
        {
            return $this->__toString();
        }
    }

    private static function getTypevalid($type)
    {
        if (class_exists($type) && is_subclass_of($type, self::class))
        {
            return $type;
        } elseif (class_exists(__NAMESPACE__ . '\\' . $type) && is_subclass_of(__NAMESPACE__ . '\\' . $type, self::class))
        {
            return __NAMESPACE__ . '\\' . $type;
        } else
        {
            return false;
            // throw new \Exception("La clase validadora " . $type . " no existe ");
        }
    }

    public function __debugInfo()
    {
        return ['RecValue' => $this->RecValue, 'Value' => $this->value, 'valido' => $this->valido];
    }

    /**
     * retorna el valor recibido 
     * @return mixes
     */
    public function GetRecValue()
    {
        return $this->RecValue;
    }

    /**
     * devera realizar la validacion 
     * @param mixes &$value valor a validar 
     * @return mixes|bool si es invalido retornara un booleano false
     */
    abstract public function Validate(&$value);

    /**
     * retornara el valor validado 
     * @return mixes
     */
    public function __toString()
    {
        return $this->value;
    }

    public function getIterator()
    {
        if (!is_array($this->value))
        {
            return new \ArrayIterator([$this->value]);
        }
        return new \ArrayIterator($this->value);
    }

    public function offsetExists($offset)
    {
        return isset($this->value[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->value[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->value[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->value[$offset]);
    }

}
