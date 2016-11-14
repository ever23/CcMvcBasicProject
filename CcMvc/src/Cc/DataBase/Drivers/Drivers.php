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

namespace Cc\DB;

use Cc\iDataBase;

include_once dirname(__FILE__) . '/../TypeMetaData/MetaData.php';

/**
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>
 * @package Cc
 * @subpackage DataBase  
 * @category Drivers
 */
class Exception extends \Exception
{
    
}

/**
 * Description of Drivers
 * CLASE ABSTRACTA PARA CREACION DE DRIVERS PARA LOS DIFERENTES TIPOS DE BASES DE DATOS 
 * ESTOS DRIVERS SERAN USADOS POR LA CLASE DBtabla
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>
 * @package Cc
 * @subpackage DataBase  
 * @category Drivers
 * @todo FALTAN LOS DRIVERS DE MSSQL, OCCI8, SQLSRV, ODBC ENTRE OTROS
 */
abstract class Drivers
{

    /**
     * OBJETO MANEJADOR DE BASES DE DATOS
     * @var iDataBase
     */
    protected $db;

    /**
     * atributos de la tabla con su respectivos tipos 
     * <pre>
     *  [
     *      'columna1'=>[
     *                  'Type'=>'tipo de dato', // el tipo de dato debe ser igual que en la tabla de la base de datos 
     *                  'KEY'=>'tipo de indice ', // es opcional, pero si la columna es una columna
     *                                           // primaria debe ser obligatorio y debe contener el valor PRI
     *                  'TypeName'=>'nombre del tipo de dato', //opcional 
     *                  'Default' => ''  // opcional, valor por defecto en la base de datos
     *                  'Extra' => self::AutoIncrement //opcional, puede ser usado para indica que una columna es auto_increment 
     *                  'Position'=>0 // el numero de posicoin de la columna en la tabla
     *                  ],
     *      'columna2'=>[...],
     *      'columna2'=>[...],
     *      .
     *      .
     *      .
     *      'columnaN'=>[...]
     * 
     * ] </pre>
     * @var array
     * 
     */
    protected $colum = [];
    protected $FormatNumber = [''];

    /**
     * atributos de la tabla en el orden que estan en la tabla
     * @var array 
     */
    protected $OrderColum = [];

    /**
     * atributo con la marca auto_increment
     * en caso de que la base de datos lo soporte 
     * @var string 
     */
    protected $autoincrement;

    /**
     * atributos que sean claves primarias 
     * @var array 
     */
    protected $primarykey = [];

    /**
     * nombre de la tabla 
     * @var string 
     */
    protected $tabla;

    /**
     * tipo de entidad
     * @var string 
     */
    public $Ttipe;

    /**
     *
     * @var bool 
     */
    protected $keys_activ = false;

    /**
     * caracter de escape
     * @var string 
     */
    public $_escape_char = '';
    protected $dbprefix = '';
    protected $_protect_identifiers = TRUE;

    /**
     * identificadores reservador por la base de datos
     * @var array
     */
    protected $_reserved_identifiers = array('*'); // Identifiers that should NOT be escaped
    protected $swap_pre = '';
    private $ar_aliased_tables = [];
    public $FilterSqli = true;

    const view = 'view';
    const table = 'table';
    const none = 'none';

    /**
     * 
     * @param iDataBase $db
     * @param string $tabla
     */
    public function __construct(iDataBase &$db, $tabla)
    {
        $this->db = &$db;
        $this->tabla = $tabla;
    }

    /**
     * 
     * @param string $serialized
     * @internal 
     */
    public function unserialize($serialized)
    {
        $this->colum = $serialized['colum'];
        $this->primarykey = $serialized['primary'];
        $this->autoincrement = $serialized['autoinrement'];
        $this->Ttipe = $serialized['Ttipe'];
        $this->tabla = $serialized['tabla'];
    }

    /**
     * recoge de la base de datos los metadatos de la tabla
     */
    public function CreateKeys()
    {
        $this->keys($this->tabla);
    }

    /**
     * recoge de la base de datos los metadatos de la tabla
     */
    abstract public function keys($tabla);

    /**
     * protege lo identificadores
     * @param string $item
     * @return string
     */
    protected function _escape_identifiers($item)
    {
        if ($this->_escape_char == '')
        {
            return $item;
        }

        foreach ($this->_reserved_identifiers as $id)
        {
            if (strpos($item, '.' . $id) !== FALSE)
            {
                $str = $this->_escape_char . str_replace('.', $this->_escape_char . '.', $item);

                // remove duplicates if the user already included the escape
                return preg_replace('/[' . $this->_escape_char . ']+/', $this->_escape_char, $str);
            }
        }

        if (strpos($item, '.') !== FALSE)
        {
            $str = $this->_escape_char . str_replace('.', $this->_escape_char . '.' . $this->_escape_char, $item) . $this->_escape_char;
        } else
        {
            $str = $this->_escape_char . $item . $this->_escape_char;
        }

        // remove duplicates if the user already included the escape
        return preg_replace('/[' . $this->_escape_char . ']+/', $this->_escape_char, $str);
    }

    /**
     * 
     * @param string $ColumName
     * @param mixes $valor
     * @return mixes
     * 
     */
    public function SerializeType($ColumName, $valor)
    {
        if (!key_exists($ColumName, $this->colum))
            return $valor;
        $class_name = __NAMESPACE__ . "\\MetaData\\" . $this->colum[$ColumName]['Type'];

        if (!is_null($valor) && class_exists($class_name, false))
        {

            $obj = new $class_name($valor, $this->colum[$ColumName]);
            return $obj->__toString();
        }
        return $valor;
    }

    /**
     * 
     * @param string $ColumName
     * @param mixes $valor
     * @return mixes
     */
    public function UnserizaliseType($ColumName, $valor)
    {

        if (!key_exists($ColumName, $this->colum))
            return $valor;
        $class_name = __NAMESPACE__ . "\\MetaData\\" . $this->colum[$ColumName]['Type'];
        if (!is_null($valor) && class_exists($class_name, false))
        {
            $obj = new $class_name($valor, $this->colum[$ColumName]);
            return $obj;
        }
        return $valor;
    }

    /**
     * retorna las claves primarias
     * @return array
     */
    public function PrimaryKey()
    {
        return $this->primarykey;
    }

    /**
     * 
     * @return array
     */
    public function Colum()
    {
        return $this->colum;
    }

    /**
     * 
     * @return array
     */
    public function OrderColum()
    {
        return $this->OrderColum;
    }

    /**
     * 
     * @return array
     */
    public function auto_incremet()
    {
        return $this->autoincrement;
    }

    public function tabla()
    {
        return $this->tabla;
    }

    protected function num_rows($result)
    {
        /* $result \mysqli_result */
        if ($result instanceof \mysqli_result)
        {
            return $result->num_rows;
        } elseif ($result instanceof \PDOStatement)
        {
            return $result->rowCount();
        } elseif ($result instanceof \SQLite3Result)
        {
            // $r = count($result->fetchArray());
            for ($i = 0; $result->fetchArray(); $i++)
                ;
            $result->reset();
            return $i;
        }
    }

    protected function fecth_result($result)
    {
        if ($result instanceof \mysqli_result)
        {
            $array = $result->fetch_array(MYSQLI_ASSOC);
        } elseif ($result instanceof \PDOStatement)
        {
            $array = $result->fetch(\PDO::FETCH_ASSOC);
        } elseif ($result instanceof \SQLite3Result)
        {
            $array = $result->fetcharray(SQLITE3_ASSOC);
        }

        return $array;
    }

    protected $FormatBinary = ['longblob', 'blob', 'binary'];

    /**
     * formatea una valiable a sql de tipo inset
     * @param mixes $var
     * @return string
     */
    public function FormatVarInsert($var, $ColumName = '')
    {
        $type = 'TEXT';
        $var = $this->SerializeType($ColumName, $var);
        if (key_exists($ColumName, $this->colum))
            $type = $this->colum[$ColumName]['Type'];

        $var = $this->FilterSqlI($var);
        if (preg_match("/" . implode("|", $this->FormatBinary) . "/i", $type))
        {
            $bin = '';
            if ($var instanceof \SplFileInfo && $var->isReadable())
            {
                $file = $var->openFile('r');

                foreach ($file as $línea)
                {
                    $bin.=$línea;
                }

                return '0x' . bin2hex($bin);
            } elseif ($var instanceof \SplFileObject)
            {

                foreach ($var as $línea)
                {
                    $bin.=$línea;
                }
                return '0x' . bin2hex($bin);
            } elseif (is_resource($var) && get_resource_type($var) == 'stream')
            {

                while (!feof($var))
                {
                    $bin.=fgets($var);
                }
                return '0x' . bin2hex($bin);
            } elseif ((is_string($var) && strncmp($var, '0x', 2) === 0))
            {
                return $var;
            }
        }

        if (is_null($var) || (is_string($var) && strtolower($var) == 'null'))
        {
            return 'NULL';
        } elseif (is_int($var) || is_float($var) || is_double($var))
        {
            return $var;
        } elseif (is_bool($var))
        {
            return $var ? 'true' : 'false';
        } else
        {

            return "'" . $var . "'";
        }
    }

    /**
     * formatea una valiable a sql de tipo Select
     * @param mixes $var
     * @return string
     */
    public function FormatVarSelect($var)
    {
        $var = $this->FilterSqlI($var);
        if (is_null($var) || strtolower($var) == 'null')
        {
            return 'is NULL';
        } else
        if (is_int($var) || is_float($var) || is_double($var))
        {
            return "=" . $var . "";
        } elseif (is_bool($var))
        {
            return $var ? '=true' : '=false';
        } else
        {
            return "='" . $var . "'";
        }
    }

    public function ProtectIdentifiers($item, $prefix_single = FALSE, $protect_identifiers = NULL, $field_exists = TRUE)
    {
        if (!is_bool($protect_identifiers))
        {
            $protect_identifiers = $this->_protect_identifiers;
        }

        if (is_array($item))
        {
            $escaped_array = array();

            foreach ($item as $k => $v)
            {
                $escaped_array[$this->ProtectIdentifiers($k)] = $this->ProtectIdentifiers($v);
            }

            return $escaped_array;
        }
        $item = $this->FilterSqlI($item);
        // Convert tabs or multiple spaces into single spaces
        $item = preg_replace('/[\t ]+/', ' ', $item);

        // If the item has an alias declaration we remove it and set it aside.
        // Basically we remove everything to the right of the first space
        if (strpos($item, ' ') !== FALSE)
        {
            $alias = strstr($item, ' ');
            $item = substr($item, 0, - strlen($alias));
        } else
        {
            $alias = '';
        }

        // This is basically a bug fix for queries that use MAX, MIN, etc.
        // If a parenthesis is found we know that we do not need to
        // escape the data or add a prefix.  There's probably a more graceful
        // way to deal with this, but I'm not thinking of it -- Rick
        if (strpos($item, '(') !== FALSE)
        {
            return $item . $alias;
        }

        // Break the string apart if it contains periods, then insert the table prefix
        // in the correct location, assuming the period doesn't indicate that we're dealing
        // with an alias. While we're at it, we will escape the components
        if (strpos($item, '.') !== FALSE)
        {
            $parts = explode('.', $item);

            // Does the first segment of the exploded item match
            // one of the aliases previously identified?  If so,
            // we have nothing more to do other than escape the item
            if (in_array($parts[0], $this->ar_aliased_tables))
            {
                if ($protect_identifiers === TRUE)
                {
                    foreach ($parts as $key => $val)
                    {
                        if (!in_array($val, $this->_reserved_identifiers))
                        {
                            $parts[$key] = $this->_escape_identifiers($val);
                        }
                    }

                    $item = implode('.', $parts);
                }
                return $item . $alias;
            }

            // Is there a table prefix defined in the config file?  If not, no need to do anything
            if ($this->dbprefix != '')
            {
                // We now add the table prefix based on some logic.
                // Do we have 4 segments (hostname.database.table.column)?
                // If so, we add the table prefix to the column name in the 3rd segment.
                if (isset($parts[3]))
                {
                    $i = 2;
                }
                // Do we have 3 segments (database.table.column)?
                // If so, we add the table prefix to the column name in 2nd position
                elseif (isset($parts[2]))
                {
                    $i = 1;
                }
                // Do we have 2 segments (table.column)?
                // If so, we add the table prefix to the column name in 1st segment
                else
                {
                    $i = 0;
                }

                // This flag is set when the supplied $item does not contain a field name.
                // This can happen when this function is being called from a JOIN.
                if ($field_exists == FALSE)
                {
                    $i++;
                }

                // Verify table prefix and replace if necessary
                if ($this->swap_pre != '' && strncmp($parts[$i], $this->swap_pre, strlen($this->swap_pre)) === 0)
                {
                    $parts[$i] = preg_replace("/^" . $this->swap_pre . "(\S+?)/", $this->dbprefix . "\\1", $parts[$i]);
                }

                // We only add the table prefix if it does not already exist
                if (substr($parts[$i], 0, strlen($this->dbprefix)) != $this->dbprefix)
                {
                    $parts[$i] = $this->dbprefix . $parts[$i];
                }

                // Put the parts back together
                $item = implode('.', $parts);
            }

            if ($protect_identifiers === TRUE)
            {
                $item = $this->_escape_identifiers($item);
            }

            return $item . $alias;
        }

        // Is there a table prefix?  If not, no need to insert it
        if ($this->dbprefix != '')
        {
            // Verify table prefix and replace if necessary
            if ($this->swap_pre != '' && strncmp($item, $this->swap_pre, strlen($this->swap_pre)) === 0)
            {
                $item = preg_replace("/^" . $this->swap_pre . "(\S+?)/", $this->dbprefix . "\\1", $item);
            }

            // Do we prefix an item with no segments?
            if ($prefix_single == TRUE AND substr($item, 0, strlen($this->dbprefix)) != $this->dbprefix)
            {
                $item = $this->dbprefix . $item;
            }
        }

        if ($protect_identifiers === TRUE AND ! in_array($item, $this->_reserved_identifiers))
        {
            $item = $this->_escape_identifiers($item);
        }

        return $item . $alias;
    }

    /**
     * filtra los campos
     * @param array $campos
     * @return string
     */
    public function Campos(array $campos)
    {
        $ret = [];
        //$match = "/(\(.*\))|(\*)/";
        foreach ($campos as $i => $v)
        {

            $ret[$i] = $this->ProtectIdentifiers($v);
        }
        return implode(',', $ret);
    }

    /**
     * FILTRA LAS POSIBLES INYECCIONES SQL
     */
    public function FilterSqlI($val, $exept = [])
    {
        if (!$this->FilterSqli)
        {
            return $val;
        }
        if ($this->db->connect_error)
            return $val;
        if (is_null($val))
        {
            return NULL;
        } elseif (is_array($val))
        {
            foreach ($val as $i => $v)
            {
                $val[$i] = !in_array($i, $exept) ? $this->FilterSqlI($v) : $v;
            }
            return $val;
        } else
        {
            return $this->db->real_escape_string($val);
        }
        return $val;
    }

}
