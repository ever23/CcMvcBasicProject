<?php

namespace Cc;

/**
 * 
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package Cc
 * @subpackage DataBase
 * @internal                                                                  
 */
class SqlFormat
{

    /**
     * referencia a el objeto manejador de bases de datos
     * @var iDataBase 
     */
    protected $db;
    protected $SqlIdent = '';

    public function __construct(iDataBase &$db)
    {
        $this->db = &$db;
    }

    /**
     * AGREGA COLUMNAS A UNA CONSULTA PREVIAMENTE DEFINIDA
     *  @param string $consulta CONSULTA SQL
     *  @param array $coll COLUMNAS A AGREGAR 
     *  @return string CONSULTA SQL CON LAS COLUMNAS Y JOIS AGREGADOS
     */
    protected function AddCollConsulta($consulta, array $coll, $u = ',')
    {
        //$consulta=strtolower($consulta);
        $sql1 = substr(trim($consulta), 6, strlen(trim($consulta)));
        //print_r($mact);
        $sql = "SELECT " . implode(',', $coll) . $u . " " . $sql1;
        return $sql;
    }

    /**
     *   GENERA UNA BUSQUEDA SQL 
     * 	@param string $SQL sentencia sql que se ejecutara OJO(no puede llevar clausulas where,GROUP,ORDER,LIMIT )
     * 	@param string $cadena cadena de caracteres que se buscara 
     * 	@param array $campos campos de la sentencia sql donde se buscara la cadena 
     *   @return string  $sql
     */
    public function CreateBusqueda($SQL, $cadena, $campos)
    {
        if(is_array($cadena))
        {
            $cadena = implode(' ', $cadena);
        }
        $trozos = explode(' ', trim($cadena));
        $select = '';
        $were = '';
        foreach($campos as $campo)
        {
            $select.=" (" . $campo . " is NOT NULL AND " . $campo . " like '%" . $cadena . "%') + 
			(" . $campo . " is NOT NULL AND " . $campo . " like '" . $cadena . "%')+";
        }
        foreach($trozos as $palabra)
        {
            //if(strlen($palabra)>2 || (is_int($palabra) || is_float($palabra)))
            foreach($campos as $campo)
            {
                $select.=" (" . $campo . " is NOT NULL AND " . $campo . " like '%" . $palabra . "%') + (" . $campo . " is NOT NULL AND " . $campo . " like '" . $palabra . "%')+";
            }
        }
        $solo = '';
        if(!($this->db instanceof \SQLite3) && !(method_exists($this->db, 'sqliteCreateAggregate')))
        {
            if(count($campos) > 1)
            {
                $select.="(( CONCAT(";
                foreach($campos as $i => $campo)
                {
                    $espace = !is_int($i) ? "''" : "' '";
                    $select.="IF($campo IS NOT NULL,$campo,' ')," . $espace . ",";
                }
                $select.="'') like '%" . $cadena . "%' )+1) ";
            } elseif(count($campos) == 1)
            {
                $select.="(0)";

                $solo = "* (IF(" . $campos[0] . " = '" . $cadena . "',0 ,1))+(IF(" . $campos[0] . " = '" . $cadena . "',10,0))";
            }
        } else
        {
            $select.="(0)";
        }

        $having = "puntaje_busqueda>1";
        if(preg_match("/order|having|limit/i", $SQL, $m, PREG_OFFSET_CAPTURE))
        {
            $m[0][0];
            $m[0][1];
            if(strtolower($m[0][0]) == 'having')
            {
                $sql = $this->AddCollConsulta(substr($SQL, 0, $m[0][1]), [' (((' . $select . ')) ' . $solo . ') as puntaje_busqueda ']) . " having  (" . $having . ") and " . substr($SQL,
                                $m[0][1] + strlen($m[0][0]), strlen($SQL));
            } else
            {
                $sql = $this->AddCollConsulta(substr($SQL, 0, $m[0][1]), [' (((' . $select . ')) ' . $solo . ') as puntaje_busqueda ']);
                $sql.=" having (" . $having . ") " . substr($SQL, $m[0][1], strlen($SQL));
            }
            if(preg_match("/ORDER BY|limit/i", $sql, $m, PREG_OFFSET_CAPTURE))
            {
                $SQL = $sql;
                $m[0][0];
                $m[0][1];
                if(strtolower($m[0][0]) == 'order by')
                {
                    $sql = substr($SQL, 0, $m[0][1]) . " order by  puntaje_busqueda DESC, " . substr($SQL, $m[0][1] + strlen($m[0][0]), strlen($SQL));
                } else
                {
                    $sql = substr($SQL, 0, $m[0][1]);
                    $sql.=" order by  puntaje_busqueda DESC " . substr($SQL, $m[0][1], strlen($SQL));
                }
            }
        } else
        {
            $sql = $this->AddCollConsulta($SQL, [' (((' . $select . ')) ' . $solo . ') as puntaje_busqueda ']) . " having (" . $having . ") order by  puntaje_busqueda DESC";
        }
        return $sql;
    }

    /**
     * FILTRA LAS POSIBLES INYECCIONES SQL
     */
    public function FilterSqlI($val, $exept = [])
    {
        if($this->db->connect_error)
            return $val;
        if(is_string($val))
        {
            return $this->db->real_escape_string($val);
        } elseif(is_array($val))
        {
            foreach($val as $i => $v)
            {
                $val[$i] = !in_array($i, $exept) ? $this->FilterSqlI($v) : $v;
            }
            return $val;
        }
        return $val;
    }

}
