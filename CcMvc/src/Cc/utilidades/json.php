<?php

namespace Cc;

use \JsonSerializable;

/**
 * Json class php                                                              
 * FACILITA LA ESCRITURA DE CODIGO JSON script SIN NESECIDAD DE                 
 * TENER CONOCIMIENTO DE EL LENGUAJE PROVEE UN TOTAL SOPORTE A LA SINTAXIS      
 * SOPORTA TODO TIPO DE VARIABLE PHP INCLUSO OBJETOS DE LOS CUALES SON          
 * TOMADOS LOS ATRIBUTOS PUBLICOS PARA GENERAR UN OBJETO JSON                   
 *                                                                              
 * @version 1.2.2.2                                                             
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                                    
 * @package Cc
 * @subpackage Utilidades              
 */
class Json implements \ArrayAccess, JsonSerializable, \IteratorAggregate
{

    private $vars = array();
    protected $autoprint;
    private $_JsonErrno;
    private $_JsonError;

    const ERROR_DEPTH = 'Se ha excedido la profundidad máxima de la pila';
    const ERROR_STATE_MISMATCH = 'JSON con formato incorrecto o inválido';
    const ERROR_CTRL_CHAR = 'Error del carácter de control, posiblemente se ha codificado de forma incorrecta';
    const ERROR_SYNTAX = 'Error de sintaxis';
    const ERROR_UTF8 = 'Caracteres UTF-8 mal formados, posiblemente codificados de forma incorrecta';
    const ERROR_RECURSION = 'Una o más referencias recursivas en el valor a codificar';
    const ERROR_INF_OR_NAN = 'Uno o más valores NAN o INF en el valor a codifica';
    const ERROR_UNSUPPORTED_TYPE = 'Se proporcionó un valor de un tipo que no se puede codificar';

    /**
     * 
     * @param bool $auto INDICA SI DE INPRIMIRA EL CONTENIDO EN EL DESTRUCTOR
     * @param string $json script json que sera procesado
     */
    public function __construct($json = NULL)
    {
        $this->AutoPrint(false);
        $this->vars = array();
        if (is_string($json))
        {
            $this->SetJson($json);
        } elseif (!is_null($json))
        {
            $this->CreateJson($json);
        }
    }

    /**
     * @ignore
     */
    public function __destruct()
    {
        if ($this->autoprint)
        {
            $this->PrintJson();
        }
        unset($this->vars);
        unset($this->autoprint);
    }

    /**
     * @ignore
     */
    public function __debugInfo()
    {
        return $this->vars;
    }

    /**
     * @ignore
     */
    public static function __set_state($an_array) // A partir de PHP 5.1.0
    {
        $obj = new self();
        $obj->vars = $an_array['vars'];
        return $obj;
    }

    /**
     *  INDICA SI SE INPRIMIRA EL CONTENIDO EN EL DESTRUCTOR
     *  @param bool $auto
     */
    public function AutoPrint($auto)
    {
        $this->autoprint = $auto;
    }

    /**
     *  REALIZA UNA COPIA DE UN OBJETO JSON
     *  @param Json $j  objeto json
     */
    public function Copy(&$j)
    {
        if ($j instanceof Json)
        {
            $this->vars = $this->vars + $j->vars;
        } elseif ($j instanceof \JsonSerializable)
        {
            $this->vars = $this->vars + $j->jsonSerialize();
        } elseif (is_array($j))
        {
            $this->vars = $this->vars + $j;
        }
    }

    /**
     *  AGREGA UNA VARIABLE AL OBJETO JSON
     * @param string $name  nombre de la varialbe json
     * @param mixes $conten el contenido de la variable puede ser cualquier tipo de variable php tales como  string,bool,int,float,Array,Object,NULL
     * @return this para encadenado de metodos
     */
    public function &Set($name, $conten = NULL)
    {
        if (is_object($conten))
        {
            if ($conten instanceof Json)
            {
                $this->vars[$name] = clone $conten;
            } elseif ($conten instanceof \JsonSerializable)
            {
                $json = new self(false);
                $json->CreateJson($conten->jsonSerialize(), true);
                $this->vars[$name] = $json;
            } else
            {

                $this->vars[$name] = self::ObjectJson($conten);
            }
        } else
        {
            $this->vars[$name] = $conten;
        }
        return $this;
    }

    /**
     * agrega un script a los atributos del objeto json actual
     * @param string $name
     * @param string $script
     */
    public function SetFromScript($name, $script)
    {
        $this->Set($name, new self(false, $script));
    }

    /**
     * @ignore
     * @param type $name
     * @param type $conten
     */
    public function __set($name, $conten)
    {
        $this->Set($name, $conten);
    }

    /**
     *  INSERTA UNA CADENA JSON Y LA AGREGA A LAS VARIABLES
     *  DE LA CLASE
     *  @param string $stringJson CADENA DE TECTO JSON
     *  @param bool $is_new TRUE PARA INSERTAR REEMPLAZAR EL OBBJETO DE LA CLASE ,FALSE PARA CONCATENAR EL OBJETO POR DEFECTO ES TRUE
     *  @return this para encadenado de metodos
     */
    public function &SetJson($stringJson, $is_new = true)
    {
        $var = $this->Decode($stringJson, false);


        $this->CreateJson($var, $is_new);
        return $this;
    }

    /**
     * PROCESA UN SCRIPT JSON ALMACENADO EN UN ARCHIVO
     * @param string $filename nombre del archivo
     * @param bool $is_new true para sustituir al existente y false para concatenar
     */
    public function SetJsonFile($filename, $is_new = true)
    {
        $stringJson = file_get_contents($filename);
        $this->SetJson($stringJson, $is_new);
    }

    public function SaveToFile($file)
    {
        return file_put_contents($file, $this);
    }

    /**
     * 
     * @param mixes $mixes
     * @return mixes
     */
    private function &FilterJson($mixes)
    {
        $mixes = (array) $mixes;
        foreach ($mixes as $i => &$v)
        {
            if (is_object($v))
            {
                $v = self::ObjectJson($v);
            } elseif (is_array($v))
            {
                $v = $this->FilterJson($v);
            } else
            {
                
            }
        }
        return $mixes;
    }

    /**
     * 
     * @param mixes $mixes variables a procesar
     * @param bool $is_new true para sustituir al existente y false para concatenar
     * @return Json
     */
    public function CreateJson($mixes, $is_new = true)
    {
        if ($is_new)
        {
            $this->vars = $mixes;
        } else
        {
            $this->vars = array_merge($this->vars, $mixes);
        }
        return $this;
    }

    /**
     *  AGREGA UNA VARIABLE AL OBJETO JSON
     *  @param string $name  nombre de la varialbe json
     *  @return mixes el contenido de la variable puede ser cualquier tipo de variable php tales como  string,bool,int,float,Array,Object,NULL
     *  segun se aya definido en el metodo Set de no pasarse nungun parametro retornara un array con todo el contenido json
     */
    public function Get($name = NULL)
    {
        if (is_null($name))
        {
            return $this->vars;
        } elseif (isset($this->vars[$name]))
        {
            return $this->vars[$name];
        }
    }

    /**
     * @ignore
     * @param type $name
     * @return type
     */
    public function __get($name)
    {
        if (isset($this->vars[$name]))
        {
            return $this->vars[$name];
        }
        throw new \Exception();
    }

    /**
     * @ignore
     * @param type $offset
     * @return type
     */
    public function offsetGet($offset)
    {
        if (isset($this->vars[$offset]))
        {
            return $this->vars[$offset];
        }
    }

    /**
     * @ignore
     * @param type $offset
     * @param type $value
     */
    public function offsetSet($offset, $value)
    {

        $this->__set($offset, $value);
    }

    /**
     * @ignore
     * @param type $offset
     * @return type
     */
    public function offsetExists($offset)
    {
        return isset($this->vars[$offset]);
    }

    /**
     * @ignore
     * @param type $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->vars[$offset]);
    }

    public function __unset($name)
    {
        unset($this->vars[$name]);
    }

    public function __isset($name)
    {
        return isset($this->vars[$var]);
    }

    /**
     *  RETORNA EL NOMBRE DEL TIPO DE VARIALBLE
     *  @param mixes $var  variable a revisar
     *  @return string el nombre del tipo de variable  string,bool,int,float,Array,Object,NULL
     *  segun se aya definido en el metodo Set
     */
    private function TypeVar($var)
    {
        if (is_null($var))
        {
            return 'NULL';
        } elseif (is_int($var))
        {
            return 'int';
        } elseif (is_float($var))
        {
            return 'float';
        } elseif (is_bool($var))
        {
            return 'bool';
        } elseif (is_array($var))
        {
            return 'Array';
        } elseif (is_object($var))
        {
            return 'Object';
        } else
        {
            if ($var == 'NULL' || $var == 'null')
            {
                return 'NULL';
            } else
            {
                return 'string';
            }
        }
    }

    /**
     *  FORMATEA LA VARIALBLE SEGUN SU TiPO JSON
     *  @param mixes $var  variable a formartear
     *  @return string  contenido formateado para json
     */
    protected function FmtTypeVar(&$var)
    {
        switch ($this->TypeVar($var))
        {
            case 'NULL':
                return 'null';
            case 'int':
            case 'float':
                return $var;
            case 'bool':
                return $var ? 'true' : 'false';
            case 'Array':
                return $this->ArrayJson($var);
            case 'Object':

                return $var;
            case 'string':

                $str = str_replace("\r", "\\r", $var);
                $str = str_replace("\n", "\\n", $str);
                $str = str_replace("\t", "\\t", $str);
                $str = addcslashes($str, "\\");
                return "\"" . trim($str) . "\"";
        }
    }

    /**
     *  CONVIERTE UN ARRAY PHP EN UN ARRAY JSON
     *  @param array $array  array
     *  @return string  contenido  del array
     */
    protected function ArrayJson(array $array)
    {
        $buff = "[";
        foreach ($array as $i => $var)
        {
            if (!is_int($i))
            {
                return $this->Json_encode($array);
            }
            $buff.=$this->FmtTypeVar($var) . ",";
        }
        if (count($array) > 0)
            $buff = substr($buff, 0, strlen($buff) - 1);
        return $buff . "]";
    }

    /**
     *  TOMA UN objeto PHP CUALQUIERA Y factoriza UN objeto JSON DE SUS ATRIBUTOS PUBLICOS
     *  @param Object $objeto  array
     *  @return Json  objeto json
     */
    protected static function &ObjectJson(&$objeto)
    {
        if ($objeto instanceof self)
        {
            return $objeto;
        } else
        {
            $json = new Json();
            foreach ($json->FilterJson($objeto) as $attr => $value)
            {
                $json->Set($attr, $value);
            }
            return $json;
        }
    }

    private function Json_encode($var)
    {
        $json = '{';
        foreach ($var as $i => $v)
        {
            $json.='"' . $i . '":' . $this->FmtTypeVar($v) . ',';
        }
        return substr($json, 0, -1) . '}';
    }

    /**
     *  RETORNA UNA CADENA DE TEXTO JSON
     * @return string 
     */
    public function Encode($option = NULL)
    {
        if ($js = json_encode($this->vars, $option))
        {
            return $js;
        }

        return $this->Json_encode($this->vars); //'{"error":"ERRO JSON"}';
    }

    /**
     * @ignore
     * @param type $stringJson
     * @param type $array
     * @return type
     */
    private function Decode($stringJson, $array = false)
    {
        if (($json = json_decode($stringJson, $array)) === NULL)
        {
            $this->_JsonErrno = json_last_error();
            $this->_JsonError = json_last_error_msg();
        }
        return $this->FilterJson($json);
    }

    public function Errno()
    {
        return $this->_JsonErrno;
    }

    public function Error()
    {
        switch ($this->_JsonErrno)
        {
            case JSON_ERROR_NONE:
                return false;

            case JSON_ERROR_DEPTH:
                return self::ERROR_DEPTH;
            case JSON_ERROR_STATE_MISMATCH:
                return self::ERROR_STATE_MISMATCH;
            case JSON_ERROR_CTRL_CHAR:
                return self::ERROR_CTRL_CHAR;
            case JSON_ERROR_SYNTAX:
                return self::ERROR_SYNTAX;
            case JSON_ERROR_UTF8:
                return self::ERROR_UTF8;
            case JSON_ERROR_RECURSION:
                return self::ERROR_RECURSION;
            case JSON_ERROR_INF_OR_NAN:
                return self::ERROR_INF_OR_NAN;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                return self::ERROR_UNSUPPORTED_TYPE;
            default:
                return false;
        }
    }

    /**
     * funcion magica que codifica el contenido del objeto en un script json
     * @return string
     */
    public function __toString()//function que se ejecuara cuado el objeto sea tratado como un texto
    {
        return $this->Encode();
    }

    /**
     *  IMPRIME EL TEXTO JSON Y EJECUTA LOS HEADERS CORRESPONDIENTE EN CASO DE SER ACEPTADO LA COMPRESION GZIP SE EJECUTA
     */
    public function Header()
    {
        header("Content-type:  application/json");
    }

    public function PrintJson()
    {
        $json = $this->Encode();
        $this->Header();
        if (empty($_SERVER['HTTP_ACCEPT_ENCODING']))
        {
            echo $json;
            exit;
        }

        $acep = explode(",", $_SERVER['HTTP_ACCEPT_ENCODING']);
        if (in_array('gzip', $acep) || in_array('deflate', $acep))
        {
            header('Content-Encoding: gzip');
            header('Content-Length: ' . strlen($json));
            $modo = in_array('gzip', $acep) ? FORCE_GZIP : FORCE_DEFLATE;
            echo gzencode($json, 9, $modo);
        } else
        {
            echo $json;
        }
        exit;
    }

    public function jsonSerialize()
    {
        return $this->vars;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->vars);
    }

}
