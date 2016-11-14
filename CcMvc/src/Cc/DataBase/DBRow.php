<?php

namespace Cc;

/**
 * CLASE DBRow                                                                  
 * FACILITA LAS OPERACIONES BASICAS SOBRE LAS FILAS USANDO LA CLASE DBtabla     
 * PARA INSTANCEAR UN OBJETO DE ESTA CLASE                                      
 *                                                                              
 * @version 1.0                                                                 
 * @fecha 2016-02-07                                                           
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>
 * @package Cc
 * @subpackage DataBase  
 * @uses DBtabla REQUIERE UN OBJETO DBtabla PARA FUNCIONAR                                                          
 */
class DBRow implements \ArrayAccess, \Countable, \Iterator, \JsonSerializable
{

    /**
     *
     * @var array
     */
    protected $colum = array();

    /**
     *
     * @var array 
     */
    protected $Vprimary = NULL;

    /**
     *
     * @var array 
     */
    protected $oldrow;

    /**
     * refrencia a el objeto {@link DBtabla} que lo instancio 
     * @var DBtabla 
     */
    protected $DBtabla;

    /**
     *
     * @var type 
     */
    protected $row;

    /**
     * 
     * @param DBtabla $db
     * @param mixes $row 
     * @access private
     * @depends DBtabla::fetch()
     */
    public function __construct(DBtabla &$db, array $row = array())
    {
        $this->DBtabla = &$db;
        $this->colum = $db->GetCol();

        $this->oldrow = $this->row = $row;
    }

    /**
     * @access private
     * @return type
     */
    public function __debugInfo()
    {
        return $this->row;
    }

    /**
     * ELIMINA LA FILA CONTENIDA EN EL OBJETO DE LA TABLA DE DONDE PROVIENE
     * @return bool TRUE SI TUVO EXITO DE LO CONTRARIO FALSE
     * @uses DBtabla::Delete() 
     */
    public function Delete()
    {

        if (count($this->DBtabla->GetPrimary()) > 0)
        {
            $valores = array();
            foreach ($this->DBtabla->GetPrimary() as $v)
            {
                $valores+=[$v => $this->row[$v]];
            }
        } else
        {
            $valores = $this->row;
        }
        return $this->DBtabla->Delete($valores);
    }

    /**
     * EJECUTA UNA SENTENCIA UPDATE A LA FILA CONTENIDA EN EL OBJETO 
     * @return bool  TRUE SI TUVO EXITO DE LO CONTRARIO FALSE
     * @uses DBtabla::Update() 
     */
    public function Update()
    {
        if (!is_array($this->Vprimary))
        {
            $this->CreateVprimary();
        }
        $p = false;
        if (count($this->Vprimary) == 0)
        {
            $p = true;
            if (count($this->DBtabla->GetPrimary()) == 0)
            {
                IF (count($this->oldrow) == 0)
                {
                    $valores = $this->row;
                } else
                {
                    $valores = $this->oldrow;
                }
            } else
            {
                $valores = array();

                foreach ($this->DBtabla->GetPrimary() as $v)
                {
                    $valores+=[$v => $this->row[$v]];
                }
            }
        } else
        {
            $valores = $this->Vprimary;
        }
        return $this->DBtabla->Update($this->row, $valores);
    }

    /**
     * INSERTA EL CONTENIDO DEL OBJETO EN LA TABLA DE DONDE PROVIENE
     * @uses DBtabla::Insert() 
     * @return bool 
     */
    public function Insert()
    {
        return $this->DBtabla->Insert($this->row);
    }

    /**
     * retorna el nombre de la tabla
     * @return string
     */
    public function GetTabla()
    {
        return $this->DBtabla;
    }

    /**
     * retorna un array con el contenido del objeto
     * @return array
     */
    public function GetRow()
    {
        $json = [];
        foreach ($this->row as $i => $v)
        {
            $json[$i] = $this->DBtabla->Driver()->UnserizaliseType($i, $v);
        }
        return $json;
    }

    /**
     * @ignore
     * @return string
     */
    public function __toString()
    {
        $valor = '';
        foreach ($this->row as $i => $v)
        {
            $valor.=$i . "=>" . $v . ",\n";
        }
        return $valor;
    }

    /**
     * FUNCION MAGICA DE LECTURA DE ATRIBUTOS DE LA FILA 
     */
    public function __get($name)
    {
        if (key_exists($name, $this->colum) || key_exists($name, $this->row))
        {
            return $this->DBtabla->Driver()->UnserizaliseType($name, $this->row[$name]);
        } else
        {
            ErrorHandle::Notice("LA COLUMNA $name NO EXISTE EN LA TABLA " . $this->DBtabla->tabla);
        }
    }

    /**
     * 
     */
    private function CreateVprimary()
    {
        $this->Vprimary = [];
        foreach ($this->DBtabla->GetPrimary() as $a)
        {
            if (isset($this->row[$a]))
            {
                $this->Vprimary +=[$a => $this->row[$a]];
            }
        }
    }

    /**
     * FUNCION MAGICA DE ASIGNACION DE ATRIBUTOS DE LA FILA 
     */
    public function __set($name, $value)
    {
        if (!is_array($this->Vprimary))
        {
            $this->CreateVprimary();
        }
        if (key_exists($name, $this->colum) || key_exists($name, $this->row))
        {

            $this->row[$name] = $this->DBtabla->Driver()->SerializeType($name, $value);
        } else
        {
            ErrorHandle::Notice("LA COLUMNA $name NO EXISTE EN LA TABLA " . $this->DBtabla->tabla);
        }
    }

    /**
     * MUESTRA EL ULTIMO ERROR 
     * @return string
     */
    public function error()
    {
        return $this->DBtabla->error;
    }

    /**
     * MUESTRA EL ULTIMO NUMERO DE ERROR 
     * @return int
     */
    public function errno()
    {
        return $this->DBtabla->errno;
    }

    /**
     * @access private
     * @param type $name
     * @param type $value
     */
    public function offsetSet($name, $value)
    {
        if (!is_array($this->Vprimary))
        {
            $this->CreateVprimary();
        }
        if (key_exists($name, $this->colum) || key_exists($name, $this->row))
        {
            $this->row[$name] = $this->DBtabla->Driver()->SerializeType($name, $value);
        } else
        {
            ErrorHandle::Notice("LA COLUMNA $name  NO EXISTE EN LA TABLA " . $this->DBtabla->tabla);
        }
    }

    /**
     * @access private
     * @param string $offset
     * @return mixes
     */
    public function offsetExists($offset)
    {
        return isset($this->row[$offset]);
    }

    /**
     * @access private
     * @param type $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->row[$offset]);
    }

    /**
     * @access private
     * @param type $name
     * @return type
     */
    public function offsetGet($name)
    {
        if (key_exists($name, $this->colum) || key_exists($name, $this->row))
        {
            return $this->DBtabla->Driver()->UnserizaliseType($name, $this->row[$name]);
        } else
        {
            ErrorHandle::Notice("LA COLUMNA $name NO EXISTE EN LA TABLA " . $this->DBtabla->tabla);
        }
    }

    /**
     * @access private
     * @return type
     */
    public function count()
    {
        return count($this->row);
    }

    /**
     * @access private
     * @return type
     */
    public function rewind()
    {
        return reset($this->row);
    }

    /**
     * @access private
     * @return type
     */
    public function current()
    {
        return current($this->row);
    }

    /**
     * @access private
     * @return type
     */
    public function key()
    {
        return key($this->row);
    }

    /**
     * @access private
     * @return type
     */
    public function next()
    {
        return next($this->row);
    }

    /**
     * @access private
     * @return type
     */
    public function valid()
    {
        return $this->current() !== false;
    }

    /**
     * @access private
     * @return type
     */
    public function jsonSerialize()
    {
        $json = [];

        foreach ($this->row as $i => $v)
        {
            $json[$i] = $this->DBtabla->Driver()->UnserizaliseType($i, $v);
        }
        return $json;
    }

}
