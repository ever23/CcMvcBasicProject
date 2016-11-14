<?php

namespace Cc;

/**
 * SIMPLE EXTENCION DE MYSQLI 
 * @package Cc
 * @subpackage DataBase 
 */
class MySQLi extends \MySQLi implements iDataBase
{

    protected $errores;
    public $result;
    protected $SqlFormat;
    public $_ERRNO = array(
        'restricciones' => 1451,
        'DUPLICATE_KEY' => 1062
    );

    use DumpDB;

    /**
     * @ignore
     */
    public function __construct($host = NULL, $username = NULL, $passwd = NULL, $dbname = "", $port = NULL, $socket = NULL)
    {
        $this->conectar($host, $username, $passwd, $dbname, $port, $socket);
        $this->SqlFormat = new SqlFormat($this);
    }

    public function GetDB()
    {
        return $this->db;
    }

    /**
     * @ignore
     */
    public final function conectar($host = NULL, $username = NULL, $passwd = NULL, $dbname = "", $port = NULL, $socket = NULL)
    {
        $host = is_null($host) ? ini_get("mysqli.default_host") : $host;
        $username = is_null($username) ? ini_get("mysqli.default_user") : $username;
        $passwd = is_null($passwd) ? ini_get("mysqli.default_pw") : $passwd;
        $port = is_null($port) ? ini_get("mysqli.default_port") : $port;
        $socket = is_null($socket) ? ini_get("mysqli.default_host") : $socket;
        try
        {
            $this->user = $username;
            $this->pass = $passwd;
            $this->db = $dbname;
            $a = parent::connect($host, $username, $passwd, $dbname, $port, $socket);
        } catch (\mysqli_sql_exception $e)
        {

            throw $e;
        }
        if (!$this->connect_error)
        {
            $this->result = NULL;
        } else
        {
            $e = new CcException("NO FUE POSIBLE CONECTAR CON LA BASE DE DATOS", $this->connect_errno);
            $e->AddMsjMysql($this->connect_error, $this->connect_errno);
        }
    }

    /**
     * CREA UN INSTANCIA DE LA CLASE DBtabla 
     * @param string $tabla EL NOMBRE DE LA TABLA AL VUAL SE LE ASOCIARA EL OBJETO
     * @return DBtabla UNA INSTANCIA DE LA CLASE DBtabla
     */
    public function Tab($tabla)
    {
        return new DBtabla($this, $tabla);
    }

    /**
     * VERIFICA SI UNA TABLA EXISTE EN LA BASE DE DATOS
     * @param string $tabla
     * @return boolean
     */
    public function TableExis($tabla)
    {
        $result = $this->query("show tables where Tables_in_" . $this->db . "='" . $tabla . "'");

        if ($result && $result->num_rows == 1)
        {
            return true;
        }
        return false;
    }

    /**
     * RETORNA EL NOMBRE DE LA ACTUAL BASE DE DATOS
     * @return string
     */
    public function dbName()
    {
        return $this->db;
    }

    /**
     * VERIFICA EL VALOR A AUTOCOMMIT
     * @return bool 
     */
    public final function is_autocommit()
    {
        if (!$this->connect_error)
        {
            if (!($res = $this->query("SELECT @@autocommit")))
            {
                $e = new CcException("ERROR AL CONECTAR EL SISTEMA ");
                $e->AddMsjMysql($this->error, $this->errno);
            }
            $commit = $res->fetch_row();
            $res->free();
            return ((bool) $commit[0]);
        }
    }

    public function beginTransaction(...$params)
    {
        return $this->begin_transaction(...$params);
    }

    /**
     * EN CASO DE ABER ERROR RETORNA UN STRING DE LO 
     * CONTRARIO RETORNA FALSE
     */
    public final function error()
    {
        if (!(bool) $this->connect_error)
        {
            $error = $this->error;
            if (!CcException::_Empty())
            {
                return $error . " CcException ";
            }
            return trim($error) == '' ? false : $error;
        } else
        {
            return $this->connect_error;
        }
    }

    public function errno()
    {
        return $this->errno;
    }

    /**
     * INYECTA UN ERROR 
     */
    public function SetError($err)
    {
        $this->errores.=$err;
    }

    /**
     * REALIZA UNA CONSULTA (query) MYSQLI 
     * @return  MySQLi_Result
     */
    public function consulta($consulta, $error = true)
    {

        $sql = $consulta;
        if (!$this->connect_error)
        {
            if (!$result = $this->query($sql))
            {
                if ($error)
                {
                    $error = " " . $this->error . " numero: " . $this->errno . "<br>";
                    $this->errores.=$error;
                    $e = new CcException("<H2 align=center>ERROR AL CONSULTAR LA BD!!</H2>");
                    $e->AddMsjMysql("ERROR: " . $this->error, "ERRNO " . $this->errno);
                }
                $this->result = $re = NULL;
            } else
            {

                $this->result = &$result;
            }


            return $result;
        }
    }

    public final function free()
    {
        if (!$this->connect_error)
            $this->result->free();
    }

    public function GetResult()
    {
        return $this->result;
    }

    /**
     * VACIA TODO EL RESULTADO DE UNA CONSULTA EN UN ARREGLO
     * @return array RESULTADO DE CONSULTA
     */
    public function result_array($type = MYSQLI_ASSOC)
    {
        if (!$this->connect_error)
        {
            $buffer = array();
            while ($camp = $this->result->fetch_array($type))
            {
                array_push($buffer, $camp);
            }
            //$this->free();
            return $buffer;
        }
    }

    /**
     * RESULTADO LA CONSULTA GUARDADA EN RESULT 
     */
    public function result($type = MYSQLI_ASSOC)
    {
        if (!$this->connect_error && !$this->error)
            return $this->result->fetch_array($type);
    }

    /**
     * SI LA VARIABLE ES NULL RETURNA UN NULL VALIDO SQL 
     * @param strig $value valor a procesar 
     * @param strig $tb  QUE INDICA  EL MODO DE RETORNO CON I => = y S is 
     * @return string null validos sql
     */
    public function &ResultJson()
    {
        $json = new Json();
        $json->Set('error', false);
        if ($this->error())
        {
            $json->Set('error', CcException::GetExeptionS());
            return $json;
        }
        $json->Set('num_rows', $this->result->num_rows);
        if ($this->result->num_rows == 0)
        {
            $json->Set('result', array());
            return $json;
        }
        $array = array();
        while ($obj = $this->result->fetch_object())
        {
            array_push($array, $obj);
        }
        $json->Set('result', $array);
        return $json;
    }

}
