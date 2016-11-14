<?php

namespace Cc;

/**
 * SIMPLE EXTENCION DE PDO
 * @package Cc
 * @subpackage DataBase 
 */
class PDO extends \PDO implements iDataBase
{

    public $connect_error = 0;

    use DumpDB;

    public function __construct($dsn, $username = null, $password = null, $options = NULL)
    {
        $this->user = $username;
        $this->pass = $password;
        if (preg_match('/(dbname=.*;)|(DATABASE=.*;)/i', $dsn, $m))
        {
            $this->db = substr(preg_replace('/(dbname=)|(DATABASE=)/i', '', $m[0]), 0, -1);
        }

        try
        {
            parent::__construct($dsn, $username, $password, $options);
        } catch (\PDOException $ex)
        {
            $this->connect_error = $ex->getMessage();
            NEW CcException("No ha sido posible la coneccion con la base de datos");
        }
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
     * 
     * @param string $tab
     * @return DBtabla
     */
    public function Tab($tab)
    {
        return new DBtabla($this, $tab);
    }

    public function errno()
    {
        return (int) $this->errorCode();
    }

    public function real_escape_string($sq)
    {

        if ($sq == '')
            return $sq;
        $str = $this->quote($sq);
        if (strpos($str, "'") === 0)
        {
            $str = substr($str, 1, -1);
        }
        return $str;
    }

    public function error()
    {
        IF ($this->connect_error)
            return $this->connect_error;
        $arr = $this->errorInfo();
        return $arr[count($arr) - 1];
    }

}
