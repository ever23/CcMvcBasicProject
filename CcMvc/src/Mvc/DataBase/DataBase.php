<?php

namespace Cc\Mvc;

use Cc\Cache;
use Cc\iDataBase;
use Cc\Mvc;

/**
 * CLASE DBtabla                                                                
 * FACILITA LAS OPERACIONES BASICAS SOBRE LAS TABLAS USANDO LA CLASE DB_MySQli  
 * U OTRA CLASE QUE IMPLEMENTE LA INTERFACE iDataBase PARA CREA UN OBJETO       
 * DE ESTA CLASE                                                                
 *                                                                              
 *                                                       
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc
 * @subpackage DataBase
 * @see iDataBase
 * @see DB_MySQLi
 * @see DB_PDO  
 * @uses \Cc\DBtabla ES EXTENDIDA DE ESTA CLASE 
 * @example ../examples/cine/protected/controllers/Cpelicula.php description                                                         
 */
class DBtabla extends \Cc\DBtabla implements \Serializable, ParseObjectSmartyTpl
{

    /**
     * constructor
     * <code>
     * <?php
     * $db= new DB_PDO('sqlite:sqlite.db');
     * $mitabla=$db->Tab('mitabla');// creando un objeto DBtabla con DB_PDO
     * </code>
     * @param iDataBase &$db referencia a un objeto manejador de bases de datos que implemente iDatabase 
     * @param string $tabla nombre de la tabla que se asociara 
     * @throws Exception en caso de no existir la tabla
     */
    public function __construct(iDataBase &$db, $tabla, $useStmt = NULL)
    {

        $this->db = &$db;
        if (is_null($useStmt))
        {
            $this->useStmt = Mvc::App()->Config()->DB['UseStmt'];
        } else
        {
            $this->useStmt = Mvc::App()->Config()->DB['UseStmt'] && $useStmt;
        }

        $this->tabla = $tabla;
        $this->CacheName = get_class($db) . static::class . $tabla;

        if (Cache::IsSave($this->CacheName))
        {
            Mvc::App()->Log("Creando objeto para la tabla " . $tabla . " desde cache...");
            $Cache = Cache::Get($this->CacheName);
            static::$_CACHE[$this->CacheName] = $Cache;
            $this->unserialize($Cache);
        } else
        {
            Mvc::App()->Log("Creando objeto para la tabla " . $tabla);
            parent::__construct($db, $tabla, $this->useStmt);
            Cache::Set($this->CacheName, $this->serialize());
        }

        $this->Driver->FilterSqli = Mvc::App()->Config()->VarAceptSqlI === false;
    }

    /**
     * retorna el nombre del indice donde se almacena el cache de la tabla 
     * @return string
     */
    public function GetCacheName()
    {
        return $this->CacheName;
    }

    public function unserialize($serialized)
    {
        if (!($this->db instanceof iDataBase))
            $this->db = Mvc::App()->DataBase();
        parent::unserialize($serialized);
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
            'allowed' => ['__debugInfo', 'fetch', 'GetPrimary', 'Tabla', 'GetValuesEnum', '__call'],
            'format' => true,
            'block_methods' => ['each']
        ];
    }

}

/**
 * @package CcMvc
 * @subpackage DataBase
 * 
 */
class DBRow extends \Cc\DBRow
{
    
}

/**
 * @package CcMvc
 * @subpackage DataBase
 * 
 */
class MySQLi extends \Cc\MySQLi
{

    public function Tab($tabla)
    {

        return new DBtabla($this, $tabla);
    }

}

/**
 * @package CcMvc
 * @subpackage DataBase
 * 
 */
class PDO extends \Cc\PDO
{

    /**
     * 
     * @param string $tabla
     * @return \Cc\Mvc\DBtabla
     */
    public function Tab($tabla)
    {
        return new DBtabla($this, $tabla);
    }

}

/**
 * @package CcMvc
 * @subpackage DataBase
 * 
 */
class SQLite3 extends \Cc\SQLite3
{

    public function Tab($tabla)
    {
        return new DBtabla($this, $tabla);
    }

}
