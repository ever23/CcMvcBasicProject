<?php

namespace Cc;

/**
 * EJECUTA CONSULTAS EN LA BASE DE DATOS Y MANEJA EL RESULTADO
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package Cc
 * @subpackage DataBase  
 * @internal                                                                
 */
class ResultManager extends SqlFormat implements \Iterator, \Countable
{

    /**
     * MENSAJE DE ERROR DE LA ULTIMA CONSULTA 
     * @var string 
     */
    public $error;

    /**
     * NUMERO DE ERROR DE LA ULTIMA CONSULTA
     * @var int
     */
    public $errno;

    /**
     *
     * @var mixes 
     */
    public $result;

    /**
     * NUMERO DE FILAS OBTENIDAS DE LA ULTIMA CONSULTA 
     * @var int 
     */
    public $num_rows;

    /**
     * SQL DE LA ULTIMA CONSULTA 
     * @var string 
     */
    public $sql = '';

    /**
     * resultado de la ultima consulta 
     * @var array 
     */
    private $ResultAll;

    /**
     *
     * @var int
     */
    protected $end_result = NULL;
    protected $paramSmt = [];
    public $typeDB = '';

    const SQLite = 'sqlite';
    const PostgreSQL = 'pgsql';
    const MySQL = 'mysql';

    protected $useStmt = true;

    /**
     * 
     * @param iDataBase &$db
     */
    public function __construct(iDataBase &$db, $useStmt = true)
    {
        parent::__construct($db);
        $this->useStmt = $useStmt;
        $this->typeDB = $this->GetTypeDB();
    }

    protected function GetMetadata($sql)
    {
        return $this->QuerySmt($sql, true, true);
    }

    public function GetTypeDB()
    {
        if ($this->db instanceof \PDO)
        {
            return $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } elseif ($this->db instanceof \mysqli)
        {
            return self::MySQL;
        } elseif ($this->db instanceof \SQLite3)
        {
            return self::SQLite;
        } elseif ($this->db instanceof iDataBaseResult)
        {
            return $this->db->GetTypeDB();
        } else
        {
            throw new Exception("LA CLASE MANEJADORA DE BASES DE DATOS " . get_class($this->db) . " ACTUALMENTE NO ESTA SOPORTADA PORFAVOR USE PDO, MYSQLI O SQLITER3");
        }
    }

    /**
     * 
     * @ignore
     */
    public function __debugInfo()
    {
        return ['error' => $this->error, 'errno' => $this->errno, 'sql' => $this->sql, 'result' => $this->ResultAll];
    }

    public function FreeResult()
    {
        $this->rewind();
        $this->num_rows = NULL;
        $this->ResultAll = [];
    }

    /**
     * 
     * @param string $name
     * @param mixes $value
     * @param mixes $tipe
     */
    public function BindValue($name, $value, $tipe = NULL)
    {
        array_push($this->paramSmt, [$name, &$value, $tipe]);
    }

    /**
     * EJECUTA UNA CONSULTA CON LOS METODOS PROVENIENTES DE LA CLASE MANEJADORA DE BASE DE DATOS 
     * @param string $sql
     * @param bool $select
     * @return bool
     * @throws Exception SI LA CLASE NO ESTA SOPORTADA 
     * @uses ExcecutePDO se usa si la clase manejadora de bases de datos fue extendida de {@link PDO}
     * @uses ExcecuteMySqli se usa si la clase manejadora de bases de datos fue extendida de {@link MySqli}
     * @uses ExcecuteSqLite3 se usa si la clase manejadora de bases de datos fue extendida de {@link SqLite3}
     */
    private function QuerySmt($sql, $select = false, $metadata = false)
    {
        if ($select)
            $this->ResultAll = [];
        if ($this->db instanceof \PDO)
        {
            $return = $this->ExcecutePDO($sql, $select, $metadata);
        } elseif ($this->db instanceof \mysqli)
        {
            $return = $this->ExcecuteMySqli($sql, $select, $metadata);
        } elseif ($this->db instanceof \SQLite3)
        {
            $return = $this->ExcecuteSqLite3($sql, $select, $metadata);
        } elseif ($this->db instanceof iDataBaseResult)
        {
            if ($select)
            {
                if ($result = $this->db->QuerySQL($sql, $this->paramSmt))
                {
                    $this->ResultAll = $result;
                } else
                {
                    $this->error = $this->db->error();
                    $this->errno = $this->db->error();
                }
            } else
            {
                if (!$this->db->ExecuteSQL($sql, $this->paramSmt))
                {
                    $this->error = $this->db->error();
                    $this->errno = $this->db->error();
                }
            }
        } else
        {
            throw new Exception("LA CLASE MANEJADORA DE BASES DE DATOS " . get_class($this->db) . " ACTUALMENTE NO ESTA SOPORTADA PORFAVOR USE PDO, MYSQLI O SQLITER3");
        }
        $this->paramSmt = [];
        return $return;
    }

    private function ExcecutePDO($sql, $select = false, $metadata = false)
    {
        /* @var $smt \PDOStatement */
        if (!( $smt = $this->db->prepare($sql)))
        {
            $this->error = $this->db->error();
            $this->errno = $this->db->error();
            return false;
        }
        foreach ($this->paramSmt as $i => $v)
        {
            list($name, $value, $data_type) = $v;
            $smt->bindValue($name, $value, $data_type);
        }

        if (!$smt->execute())
        {
            list($errno, $o, $error) = $smt->errorInfo();
            $this->error = $errno;
            $this->errno = $error;
            return false;
        }
        if ($metadata)
        {
            $colum = $smt->columnCount();
            $MetaDataResult = [];
            for ($i = 0; $i < $colum; $i++)
            {
                $MetaDataResult[$i] = $smt->getColumnMeta($i);
            }
            return $MetaDataResult;
        }
        if ($select && $smt)
        {
            $this->ResultAll = $smt->fetchAll(\PDO::FETCH_ASSOC);
        }
        $this->num_rows = $smt->rowCount();
        $smt->closeCursor();

        return true;
    }

    private function ExcecuteMySqli($sql, $select = false, $metadata = false)
    {
        /* @var $smt \mysqli_stmt */

        $smt = $this->db->stmt_init();
        if (!$smt->prepare($sql))
        {
            $this->error = $this->db->error();
            $this->errno = $this->db->error();
            return false;
        }
        foreach ($this->paramSmt as $i => &$v)
        {
            list($name, $value, $data_type) = $v;
            $smt->bind_param($name, $value);
        }

        if (!$smt->execute())
        {
            $this->error = $smt->error;
            $this->errno = $smt->errno;
            return false;
        }
        if ($metadata)
        {
            $result = $smt->result_metadata();
            return $result->fetch_all();
        }
        if ($select)
        {
            $this->num_rows = $smt->num_rows();
        } else
        {
            $this->num_rows = $smt->affected_rows;
        }
        if ($select && $smt)
        {
            /* @var $result \mysqli_result */
            // $result = $smt->get_result();

            if (method_exists($smt, 'get_result'))
            {

                if (!($result = $smt->get_result()))
                {
                    $this->error = $smt->error;
                    $this->errno = $smt->errno;
                    return false;
                }

                $this->ResultAll = $result->fetch_all(\MYSQLI_ASSOC);
                $result->free();
            } else
            {
                $result = $smt->result_metadata();

                $r = [];
                $i = 0;
                $j = [];
                while ($c = $result->fetch_field())
                {
                    $r[$c->name] = $i;
                    $j[$i] = NULL;
                    $i++;
                }
                $result->free();
                $smt->bind_result(...$j);
                $i = NULL;
                while ($e = $smt->fetch())
                {
                    foreach ($r as $i => $v)
                    {
                        $r[$i] = $j[$v];
                    }
                    $this->ResultAll[] = $r;
                }
                // return false;
                // foreach($result)
            }
            // $smt->store_result();
        }
        $smt->close();
        return true;
    }

    private function ExcecuteSqLite3($sql, $select = false, $metadata = false)
    {
        /* @var $smt \SQLite3Stmt */
        if (!( $smt = $this->db->prepare($sql)))
        {
            $this->error = $this->db->error();
            $this->errno = $this->db->error();
            return false;
        }
        foreach ($this->paramSmt as $i => &$v)
        {
            list($name, $value, $data_type) = $v;
            $smt->bindValue($name, $value, $data_type);
        }
        /* @var $result \SQLite3Result */

        if (!($result = $smt->execute()))
        {

            $this->error = $this->db->error();
            $this->errno = $this->db->errno();
            return false;
        }
        if ($metadata)
        {
            return false;
        }
        if ($select)
        {

            $this->ResultAll = [];
            $this->num_rows = 0;
            while ($r = $result->fetchArray(\SQLITE3_ASSOC))
            {
                $this->num_rows++;
                array_push($this->ResultAll, $r);
            }
            $result->finalize();
        } else
        {
            $this->num_rows = $this->db->changes();
        }

        $smt->close();
        return true;
    }

    /**
     * ejecuta una sentencia sql 
     * @param string $sql
     * @return boolean
     */
    protected function Excecute($sql)
    {
        $this->sql = $sql;
        if ($this->useStmt)
        {

            if ($this->QuerySmt($sql, false))
            {
                $this->rewind();
                // var_dump($this->ResultAll);
                $this->end_result = NULL;

                $this->ActiveRow = NULL;
                $this->ResultAll = [];
                return true;
            } else
            {

                $e = new CcException("<H2 align=center>ERROR AL CONSULTAR LA BD!!</H2>");
                $e->AddMsjMysql("ERROR: " . $this->error, "ERRNO " . $this->errno);

                return false;
            }
        } else
        {
            if ($this->db instanceof \PDO)
            {
                $result = $this->db->exec($sql);
                if ($result !== false)
                    $this->num_rows = $result;
            } elseif ($this->db instanceof \mysqli)
            {
                $result = $this->db->real_query($sql);
                if ($result !== false)
                    $this->num_rows = $this->db->affected_rows;
            } elseif ($this->db instanceof \SQLite3)
            {
                $result = $this->db->exec($sql);
                if ($result !== false)
                    $this->num_rows = $this->db->changes();
            }
            if ($result !== false)
            {

                $this->end_result = NULL;

                $this->ActiveRow = NULL;
                $this->ResultAll = [];
                return true;
            } else
            {
                $this->end_result = NULL;
                $this->num_rows = NULL;
                $this->ActiveRow = NULL;
                $this->ResultAll = [];
                $this->error = $this->db->error();
                $this->errno = $this->db->errno();
                $e = new CcException("<H2 align=center>ERROR AL CONSULTAR LA BD!!</H2>");
                $e->AddMsjMysql("ERROR: " . $this->error, "ERRNO " . $this->errno);
                return false;
            }
        }

        return false;
    }

    /**
     * ejecuta una sentecia sql y almacena el resultado en el objeto
     * @param string $sql
     * @return boolean|ResultManager
     */
    protected function &Query($sql)
    {
        $false = false;

        $this->sql = $sql;
        if ($this->useStmt)
        {

            if ($this->QuerySmt($sql, true))
            {
                $this->rewind();
                // var_dump($this->ResultAll);
                $this->end_result = NULL;
                $this->num_rows = $this->count();
                $this->ActiveRow = NULL;
                return $this;
            } else
            {
                $e = new CcException("<H2 align=center>ERROR AL CONSULTAR LA BD!!</H2>");
                $e->AddMsjMysql("ERROR: " . $this->error, "ERRNO " . $this->errno);

                $false = false;
                return $false;
            }
        } else
        {

            if ($result = $this->db->query($sql))
            {
                $this->ResultAll = $this->Fetch_ResultAll($result);
                $this->rewind();
                $this->end_result = NULL;

                $this->num_rows = $this->count();
                $this->ActiveRow = NULL;
                return $this;
            } else
            {
                $this->end_result = NULL;
                $this->num_rows = NULL;
                $this->ActiveRow = NULL;
                $this->ResultAll = [];
                $this->error = $this->db->error();
                $this->errno = $this->db->errno();
                $e = new CcException("<H2 align=center>ERROR AL CONSULTAR LA BD!!</H2>");
                $e->AddMsjMysql("ERROR: " . $this->error, "ERRNO " . $this->errno);
                return $false;
            }
        }
        return $false;
    }

    /**
     * retorna la siguiente fila del resultado
     * @param int $n
     * @param int $limit
     * @return array
     * @depends Query
     */
    public function fetch($n = NULL, $limit = NULL)
    {
        if (!is_null($n))
        {
            return $this->ResultAll[$n];
        }
        if (!is_null($limit))
        {
            $this->SetEndResult($limit);
        }
        if (!$this->valid())
        {
            return NULL;
        }
        $array = $this->current();
        $this->next();
        return $array;
    }

    /**
     * @access private
     * @param type $result
     * @return type
     */
    protected function &fecth_result($result = NULL)
    {

        if (method_exists($result, 'fetch_array'))
        {

            $array = $result->fetch_array();
        } else if (method_exists($result, 'fetch'))
        {
            $array = $result->fetch();
        } elseif (method_exists($result, 'fetcharray'))
        {
            $array = $result->fetcharray();
        }
        return $array;
    }

    /**
     * @access private
     * @param type $result
     * @return array
     */
    protected function Fetch_ResultAll($result = NULL)
    {
        if (method_exists($result, 'fetch_all'))
        {
            // $result= new mysqli_result();
            $array = $result->fetch_all(\MYSQLI_ASSOC);
        } else if (method_exists($result, 'fetchAll'))
        {
            //$result= new PDOStatement();
            $array = $result->fetchAll(\PDO::FETCH_ASSOC);
        } elseif (method_exists($result, 'fetcharray'))
        {
            // $result= new SQLite3Result;
            $array = array();
            while ($res = $result->fetchArray(\SQLITE3_ASSOC))
            {
                array_push($array, $res);
            }
        }
        return $array;
    }

    /**
     * VUELCA EL RESULTADO EN UN OBJETO JSON
     * @return Json
     */
    public function &ResultJson()
    {
        $json = new Json();
        $json['error'] = false;
        if ($this->error())
        {
            $json['error'] = CcException::GetExeptionS();
            return $json;
        }
        $json['num_rows'] = $this->num_rows;
        if ($this->num_rows == 0)
        {
            $json['result'] = [];
            return $json;
        }

        $result = [];
        for ($i = 0; $v = $this->current(); $i++)
        {

            $result[$i] = $v;
            $this->next();
        }
        $json['result'] = $result;
        return $json;
    }

    /**
     * reinicia el puntero interno en el indice $n
     * @param int $n
     */
    public function DataSeek($n = 0)
    {
        $this->rewind();

        for ($i = 0; $i <= $n; $i++)
        {
            $this->next();
        }
    }

    /**
     * establece el limite de resultados
     * @param int $n
     */
    public function SetEndResult($n = NULL)
    {
        $this->end_result = $n;
    }

    /**
     * retorna el ultimo error ocurrido
     * @return string
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * retorna el ultimo numero de error ocurrido
     * @return int
     */
    public function errno()
    {
        return $this->errno;
    }

    /**
     * @access private
     * @return type
     */
    public function count()
    {
        return count($this->ResultAll);
    }

    /**
     * REINICIA EL PUNTERO EN EL INIDCE 0
     * 
     */
    public function rewind()
    {
        if (is_array($this->ResultAll))
            return reset($this->ResultAll);
    }

    /**
     * @access private
     * @return boolean
     */
    public function current()
    {
        if (is_null($this->ResultAll) || $this->error || (!is_null($this->end_result) && $this->key() >= $this->end_result))
        {
            return false;
        }
        //   var_dump($this->ResultAll);
        return current($this->ResultAll);
    }

    /**
     * @access private
     * @return type
     */
    public function key()
    {
        return key($this->ResultAll);
    }

    /**
     * @access private
     * @return boolean
     */
    public function next()
    {
        if (!is_null($this->end_result) && $this->key() >= $this->end_result)
        {
            return false;
        }
        return next($this->ResultAll);
    }

    /**
     * @access private
     * @return type
     */
    public function valid()
    {
        return $this->current() !== false;
    }

}
