<?php

namespace Cc;

/**
 * CLASE DBtabla                                                                
 * FACILITA LAS OPERACIONES BASICAS SOBRE LAS TABLAS USANDO LA CLASE Cc\MySQli  
 * U OTRA CLASE QUE IMPLEMENTE LA INTERFACE Cc\iDataBase PARA CREA UN OBJETO       
 * DE ESTA CLASE                                                                
 *                                                                              
 * @version 1.0.0.3                                                             
 *                                                         
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package Cc
 * @subpackage DataBase
 * @see iDataBase
 * @see MySQLi
 * @see PDO  
 * @see SQLite3                                                            
 */
class DBtabla extends ResultManager implements \JsonSerializable
{

    /**
     * atributos de la tabla con su respectivos tipos 
     * <pre>
     *  [
     *      'columna1'=>[
     *                  'Type'=>'tipo de dato',               // el tipo de dato debe ser igual que en la tabla de la base de datos 
     *                  'KEY'=>'tipo de indice ',             // es opcional, pero si la columna es una columna
     *                                                        // primaria debe ser obligatorio y debe contener el valor PRI
     *                  'TypeName'=>'nombre del tipo de dato',//opcional 
     *                  'Default' => ''                       // opcional, valor por defecto en la base de datos
     *                  'Extra' => self::AutoIncrement        //opcional, puede ser usado para indica que una columna es auto_increment 
     *                  'Position'=>0                         // el numero de posicoin de la columna en la tabla
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

    /**
     *
     * @var array 
     */
    protected $OrderColum = [];

    /**
     * NOMBRE DE LA TABLA ASOCIADA
     * @var string 
     */
    public $tabla;

    /**
     * CLAVES PRIMARIAS DE LA TABLA
     * @var array 
     */
    protected $primary = [];

    /**
     * conlumna de tipo autoincrement de la tabla 
     * @var autoincrement 
     */
    protected $autoinrement = '';

    /**
     *
     * @var bool 
     */
    protected $keys_activ = false;
    protected $ActiveRow = NULL;
    protected $Ttipe = 'table';
    protected $SqlUnion = '';
    private $postTab = '';

    /**
     * nombre del indice donde se almacena el cache de la tabla 
     * @var string 
     */
    protected $CacheName = '';

    const view = 'view';
    const table = 'table';
    const none = 'none';

    /**
     * SE USA EN LOS METODOS SELECT COMO PRIMER VALOR DEL PARAMETRO $campos 
     * INDICA QUE SE REALIZARA UN SELECT ALL ... FROM 
     */
    const AllSelect = '0x255788AllSelect';

    /**
     * SE USA EN LOS METODOS SELECT COMO PRIMER VALOR DEL PARAMETRO $campos 
     * INDICA QUE SE REALIZARA UN SELECT DISTINCT ... FROM 
     */
    const DistinctSelect = '0x234746DistinctSelect';

    /**
     * SE USA EN LOS METODOS SELECT COMO PRIMER VALOR DEL PARAMETRO $campos 
     * INDICA QUE SE REALIZARA UN SELECT DISTINCTROW ... FROM 
     */
    const DistinctRowSelect = '0x737366DistinctRowSelect';

    protected static $_CACHE = [];

    /**
     *
     * @var DB\Drivers 
     */
    protected $Driver;
    protected $ShowView = "show create view ";
    protected $ExplainSelect = "explain select * from ";
    protected $DescribeTable = "DESCRIBE ";
    protected $SqliteMaster = "select * from sqlite_master where  name=";

    /**
     * constructor
     * <code>
     * <?php
     * $db= new PDO('sqlite:sqlite.db');
     * $mitabla=$db->Tab('mitabla');// creando un objeto DBtabla con PDO
     * </code>
     * @param iDataBase &$db referencia a un objeto manejador de bases de datos que implemente iDatabase 
     * @param string $tabla nombre de la tabla que se asociara 
     * @throws Exception en caso de no existir la tabla
     */
    public function __construct(iDataBase &$db, $tabla, $useStmt = true)
    {

        parent::__construct($db, $useStmt);
        $this->tabla = $tabla;
        $this->CacheName = get_class($db) . static::class . $tabla;

        if (isset(static::$_CACHE[$this->CacheName]))
        {
            $this->unserialize(static::$_CACHE[$this->CacheName]);
        } else
        {
            $this->keys($tabla);
            static::$_CACHE[$this->CacheName] = $this->serialize();
        }
    }

    public function serialize()
    {
        return [
            'colum' => $this->colum,
            'primary' => $this->primary,
            'autoinrement' => $this->autoinrement,
            'Ttipe' => $this->Ttipe,
            'tabla' => $this->tabla,
            'typeDB' => $this->typeDB,
            'OrderColum' => $this->OrderColum
        ];
    }

    public function unserialize($serialized)
    {
        $this->colum = $serialized['colum'];
        $this->primary = $serialized['primary'];
        $this->autoinrement = $serialized['autoinrement'];
        $this->Ttipe = $serialized['Ttipe'];
        $this->tabla = $serialized['tabla'];
        $this->typeDB = $serialized['typeDB'];
        $this->OrderColum = $serialized['OrderColum'];
        $class = "\\Cc\\DB\\Drivers\\" . $this->typeDB;
        if (class_exists($class))
        {

            $this->Driver = new $class($this->db, $this->tabla);
        } else
        {
            $this->Driver = new \Cc\DB\Drivers\DefaultDriver($this->db, $this->tabla);
        }
        $this->Driver->unserialize($serialized);
        $this->SqlIdent = $this->Driver->_escape_char;
    }

    /**
     * 
     * @access private
     */
    public function __debugInfo()
    {
        return ['tabla' => $this->tabla, 'colun' => $this->colum] + parent::__debugInfo();
    }

    /**
     *  RETORNA UNA REFERENCIA A EL OBJETO DE DBRow QUE CONTIENE LA FILA ACTIVA
     *  @return DBRow  REFERENCIA DEL OBJETO DBRow
     */
    public function &GetActiveRow()
    {
        return $this->ActiveRow;
    }

    /**
     * INDICA EL VALOR AUTO INCREMENT DE LA TABLA 
     * @param string $auto SI SE PASA ESTE PARAMETRO SERA EL ATRIBUTO DONDE SE BUSCARA EL VALOR DE LO CONTRARIO SERA EL ATRIBUTO QUE CONTENGA AUTOINCREMENT
     * @return int
     */
    public function AutoIncrement($auto = NULL)
    {

        if (is_null($auto))
        {
            $auto = $this->autoinrement;
        }
        if (!$result = $this->db->query("SELECT max(" . $auto . ") from " . $this->Tabla()))
        {
            $e = new CcException("ERROR NO AUTOINCREMET");

            ErrorHandle::Notice("Error no se ha podido obtener el valor autoincrement de la tabla  " . $this->Tabla());
        }
        $r = $this->fecth_result($result);
        return $r["max(" . $auto . ")"];
    }

    /**
     * crea una sentencia union con otra tabla
     * @param string $tab nombre de la tabla
     * @return DBtabla objeto relacionado con la tabla pasada por parametro
     * 
     */
    public function &Union($tab)
    {
        $u = new self($this->db, $tab);
        $u->SqlUnion = $this->SqlUnion . 'Union ';

        return $u;
    }

    /**
     * EJECUTA LA SENTENCIA DE UNION ALMACENADA EN EL OBJETO Y CREADA PREVIAMENTE CON Union y SelectUnion
     * @return this|false retorna false si ocurrio un error
     * 
     * @depends Union
     * @uses ResultManager::Query()
     */
    public function &ExcUnion()
    {
        if ($this->Query($this->SqlUnion, 1))
        {
            return $this;
        }
    }

    /**
     * AGREGA UNA SENTENCIA SELECT A LA UNION 
     * <code>
     * <?php
     * $db= new PDO('mysqli:mysqli.db');
     * $mitabla=$db->Tab('mitabla');// creando un objeto DBtabla con PDO
     * $mitabla->SelectUnion()->Union('mitabla2')->SelectUnion()->ExcUnion();
     * foreach($mitabla as $i=>$campo)
     * {
     *      echo "campos1=".$campo['campo1']." campo2=".$campo['campo2'];
     * }
     * </code>
     * @see SelectSql usa los mismos parametros que SelectSql y tienen el mismo significado
     * @depends Union
     * 
     */
    public function &SelectUnion(...$Select)
    {
        $this->SqlUnion.=$this->SelectSql(...$Select);
        return $this;
    }

    protected function ResolvedSelectParams($campos = NULL, $where = NULL, $joins = NULL, $group = NULL, $having = NULL, $order = NULL, $limit = NULL)
    {
        $aj = $joins;
        $aw = $where;
        $ag = $group;
        $ah = $having;
        $ao = $order;

        if (is_string($campos))
        {

            $where = $campos;
            $joins = $aw;
            $group = $aj;
            $having = $ag;
            $order = $ah;
            $limit = $ao;
            $campos = NULL;
            $aj = $joins;
            $aw = $where;
            $ag = $group;
            $ah = $having;
            $ao = $order;
        }

        if (is_array($where))
        {

            $joins = $aw;
            $group = $aj;
            $having = $ag;
            $order = $ah;
            $limit = $ao;
            $aj = $joins;
            $aw = $where;
            $ag = $group;
            $ah = $having;
            $ao = $order;
            $where = NULL;
        }
        if (is_string($joins))
        {
            $group = $aj;
            $having = $ag;
            $order = $ah;
            $limit = $ao;
            $aj = $joins;
            $aw = $where;
            $ag = $group;
            $ah = $having;
            $ao = $order;
            $joins = NULL;
        }

        $aj = $joins;
        $aw = $where;
        $ag = $group;
        $ah = $having;
        $ao = $order;

        if (is_null($joins))
        {
            $joins = array();
        }
        if (!is_array($campos))
        {
            $campos = [ $this->Tabla() . '.*'];
        }


        if (preg_match('/^(\ {0,}group by )/i', $where))
        {

            $group = $where;
            $having = $aj;
            $order = $ag;
            $limit = $ah;
            $where = NULL;
            $joins = NULL;
        } elseif (preg_match('/^(\ {0,}Having )/i', $where))
        {


            $having = $where;
            $order = $aj;
            $limit = $ag;
            $where = NULL;
            $joins = NULL;
            $group = NULL;
        } elseif (preg_match('/^(\ {0,}Order by)/i', $where))
        {



            $order = $where;
            $limit = $aj;
            $where = NULL;
            $joins = NULL;
            $group = NULL;
            $having = NULL;
        } elseif (preg_match('/^(\ {0,}Limit )/i', $where))
        {




            $limit = $where;
            $where = NULL;
            $joins = NULL;
            $group = NULL;
            $having = NULL;
            $order = NULL;
        }


        if (!is_null($group))
            if (is_array($group))
            {
                $group = 'GROUP BY ' . implode(',', $group);
            } elseif (preg_match('/^(\ {0,}having )/i', $group))
            {
                $having = $group;
                $order = $ah;
                $limit = $ao;
                $group = NULL;
            } elseif (preg_match('/^(\ {0,}ORDER by )/i', $group))
            {
                $order = $group;
                $limit = $ao;
                $group = NULL;
            } if (preg_match('/^(\ {0,}LIMIT )/i', $group))
        {

            $limit = $group;
            $group = NULL;
        } elseIF ($group != '')
        {

            if (!preg_match('/^(\ {0,}GROUP BY )/i', $group))
                $group = 'GROUP BY ' . $group;
        }
        if (!is_null($having))
            if (preg_match('/^(\ {0,}LIMIT )/i', $having) || is_numeric($having))
            {

                $limit = $having;
                $having = NULL;
            } elseif (preg_match('/^(\ {0,}ORDER BY )/i', $having))
            {
                $order = $having;
                $limit = $ao;
                $having = NULL;
            }
        if (!is_null($order))
            if (preg_match('/^(\ {0,}LIMIT )/i', $order))
            {
                $limit = $order;
                $order = NULL;
            } else
            {
                if (!preg_match('/^(\ {0,}ORDER BY )/i', $order))
                    $order = 'ORDER BY ' . $order;
            }elseif (preg_match('/^(\ {0,}LIMIT )/i', $order) || is_numeric($order))
        {
            $limit = $order;

            $order = NULL;
        }
        if (!is_null($limit))
        {
            if (!preg_match('/^(\ {0,}LIMIT )/i', $limit))
                $limit = 'LIMIT ' . $limit;
        }
        $Select = 'SELECT ';
        if (isset($campos[0]))
        {
            switch ($campos[0])
            {
                case self::AllSelect:
                    $Select.='All ';
                    unset($campos[0]);
                    break;
                case self::DistinctRowSelect:
                    $Select.='DistinctRow ';
                    unset($campos[0]);
                    break;
                case self::DistinctSelect:
                    $Select.='Distinct ';
                    unset($campos[0]);
                    break;
            }
        }
        return [$Select, $campos, $where, $joins, $group, $having, $order, $limit];
    }

    /**
     *  CREA UNA CONSULTA SELECT SQL PARA LA TABLA
     *  @param array $campos 	CAMPOS QUE SE DE LA TABLA SELECCIONARAN SI ES UN string ENTONCES SE UTILISARA COMO LA CLAUSULA WHERE Y
     * TODOS LOS PARAMETROS SE CORRERAN HACIA LA IZQUIERDA
     * @param string $where   CLAUSULA WHERE SI ES UN ARRAY SE TOMARA COMO LOS JOINS Y TODOS LOS PARAMETROS SE CORRERAN HACIA LA IZQUIERDA DESDE ESTE PARAMETRO SE PUEDE INDICAR 
     * EXPLICITAMENTE QUE CLAUSULA ES Y LOS PARAMETROS SIGUENTES SE CORRERAN HACIA LA IZQUIERDA SEGUN LA POSICION DEL PARAMETRO INDICADO <br>
     * EJEMPLO: <em>si en el parametro $where se coloca GRUP BY micampo entonces $where sera tomado como $group y $goup como $having y asi sucesivamente   </em>
     * <code>
     * $tabla->SelectSQL(['micampo','micampo2'],"GRUP BY micampo","micampo2 desc",3);
     *
     * </code> <em>esta llamada generaria el siguente sql: SELECT tabla.micampo,tabla.micampo2 from tabla GRUP BY micampo ORDER BY micampo2 desc  LIMIT 3</em>
     * @param  mixes $joins CLAUSULAS JOIS UTILIZANDO UN ARRAY CON LAS TABLAS QUE SE RELACIONARAN ATEPONIENDO LOS SINGNOS <,>,=
     * < SERA PARA LEFT JOIN
     * > SERA PARA RINGT JOIN
     * = SERA INNERT JOIN
     * SI NO SE ANTEPONE NADA SERA NATURAL JOIN
     * SERA TOMADO DE LAS CLAVES PROMARIAS QUE CONTENGAN LAS TABLAS PARA LA CLAUSULA USING
     * PARA COLOCAR LA CLAUSULA 'ON' O 'USING' EXPLICITAMENTE SE COLOCA EL NOMBRE DE LA TABLA COMO INDICE
     * LOS CAMPOS 'USING' SI ES UNO COMO COMO VALOR STRING SI SON VARIOS EN UN ARRAY
     * Y PARA LA EXPRECION 'ON' UN STRING CON LA EXPRECION
     * SI $joins ES UN STRING ENTONCES SE UTILISARA COMO LA CLAUSULA GROUP Y
     * TODOS LOS PARAMETROS SE CORRERAN HACIA LA IZQUIERDA
     * @param string $group CLAUSULA GROUP
     * @param string $having CLAUSULA HAVING
     * @param string $order CLAUSULA ORDER
     * @param string $limit CLAUSULA LIMIT
     *  @return string LA SENTENCIA SQL GENERADA
     */
    protected function SelectSql($campos = NULL, $where = NULL, $joins = NULL, $group = NULL, $having = NULL, $order = NULL, $limit = NULL)
    {
        list($Select, $campos, $where, $joins, $group, $having, $order, $limit) = $this->ResolvedSelectParams($campos, $where, $joins, $group, $having, $order, $limit);

        $sql = $Select
                . $this->Driver->Campos($campos) . "  FROM " . $this->Driver->ProtectIdentifiers($this->Tabla()) . $this->postTab . " "
                . implode(' ', $this->Join($joins)) . " "
                . $this->Where($where) . ' '
                . $group . ' '
                . $this->Having($having) . ' '
                . $order . ' '
                . $limit;
        return $sql;
    }

    /**
     *  CREA UNA CONSULTA SELECT SQL PARA LA TABLA
     *  @param array $campos 	CAMPOS QUE SE DE LA TABLA SELECCIONARAN SI ES UN string ENTONCES SE UTILISARA COMO LA CLAUSULA WHERE Y
     * TODOS LOS PARAMETROS SE CORRERAN HACIA LA IZQUIERDA
     * @param string $where   CLAUSULA WHERE SI ES UN ARRAY SE TOMARA COMO LOS JOINS Y TODOS LOS PARAMETROS SE CORRERAN HACIA LA IZQUIERDA DESDE ESTE PARAMETRO SE PUEDE INDICAR 
     * EXPLICITAMENTE QUE CLAUSULA ES Y LOS PARAMETROS SIGUENTES SE CORRERAN HACIA LA IZQUIERDA SEGUN LA POSICION DEL PARAMETRO INDICADO <br>
     * EJEMPLO: <em>si en el parametro $where se coloca GRUP BY micampo entonces $where sera tomado como $group y $goup como $having y asi sucesivamente   </em>
     * <code>
     * $tabla->Select(['micampo','micampo2'],"GRUP BY micampo","micampo2 desc",3);
     *
     * </code> <em>esta llamada ejecutaria el siguente sql: SELECT tabla.micampo,tabla.micampo2 from tabla GRUP BY micampo ORDER BY micampo2 desc  LIMIT 3</em>
     * @param  mixes $joins CLAUSULAS JOIS UTILIZANDO UN ARRAY CON LAS TABLAS QUE SE RELACIONARAN ATEPONIENDO LOS SINGNOS <,>,=
     * < SERA PARA LEFT JOIN
     * > SERA PARA RINGT JOIN
     * = SERA INNERT JOIN
     * SI NO SE ANTEPONE NADA SERA NATURAL JOIN
     * SERA TOMADO DE LAS CLAVES PROMARIAS QUE CONTENGAN LAS TABLAS PARA LA CLAUSULA USING
     * PARA COLOCAR LA CLAUSULA 'ON' O 'USING' EXPLICITAMENTE SE COLOCA EL NOMBRE DE LA TABLA COMO INDICE
     * LOS CAMPOS 'USING' SI ES UNO COMO COMO VALOR STRING SI SON VARIOS EN UN ARRAY
     * Y PARA LA EXPRECION 'ON' UN STRING CON LA EXPRECION
     * SI $joins ES UN STRING ENTONCES SE UTILISARA COMO LA CLAUSULA GROUP Y
     * TODOS LOS PARAMETROS SE CORRERAN HACIA LA IZQUIERDA
     * @param string $group CLAUSULA GROUP
     * @param string $having CLAUSULA HAVING
     * @param string $order CLAUSULA ORDER
     * @param string $limit CLAUSULA LIMIT
     *  @return string LA SENTENCIA SQL GENERADA
     */
    public function Select($campos = NULL, $where = NULL, $joins = NULL, $group = NULL, $having = NULL, $order = NULL, $limit = NULL)
    {
        if (($query = $this->Query($this->SelectSql($campos, $where, $joins, $group, $having, $order, $limit))) === false)
        {
            ErrorHandle::Notice("Error al consultar tabla " . $this->tabla . " errno:" . $this->errno . " error:" . $this->error);
        }
        return $query;
    }

    /**
     * CREA, EJECUTA UNA BUSQUEDA Y ALMACENA EL RESULTADO EN EL OBJETO
     * @param string $cadena CADENA DE TEXTO QUE SERA BUSCADA
     * @param array $campo_bus ARRAY CON LOS CAMPOS DONDE SE BUSCARA EL TEXTO
     * @param array $campo_mos 	CAMPOS QUE SE MOSTRARAN DE LA TABLA  SI ES UN string ENTONCES SE UTILISARA COMO LA CLAUSULA WHERE Y
     * TODOS LOS PARAMETROS SE CORRERAN HACIA LA IZQUIERDA
     * @param string $where   CLAUSULA WHERE SI ES UN ARRAY SE TOMARA COMO LOS JOINS Y TODOS LOS PARAMETROS SE CORRERAN HACIA LA IZQUIERDA DESDE ESTE PARAMETRO SE PUEDE INDICAR 
     * EXPLICITAMENTE QUE CLAUSULA ES Y LOS PARAMETROS SIGUENTES SE CORRERAN HACIA LA IZQUIERDA SEGUN LA POSICION DEL PARAMETRO INDICADO <br>
     * EJEMPLO: <em>si en el parametro $where se coloca GRUP BY micampo entonces $where sera tomado como $group y $goup como $having y asi sucesivamente   </em>
     *  @param mixes $joins CLAUSULAS JOIS UTILIZANDO UN ARRAY CON LAS TABLAS QUE SE RELACIONARAN ATEPONIENDO LOS SINGNOS <,>,=
     *  < SERA PARA LEFT JOIN
     *  > SERA PARA RINGT JOIN
     *  = SERA INNERT JOIN
     *  SI NO SE ANTEPONE NADA SERA NATURAL JOIN
     *  SERA TOMADO DE LAS CLAVES PROMARIAS QUE CONTENGAN LAS TABLAS PARA LA CLAUSULA USING
     *  PARA COLOCAR LA CLAUSULA 'ON' O 'USING' EXPLICITAMENTE SE COLOCA EL NOMBRE DE LA TABLA COMO INDICE
     *  LOS CAMPOS 'USING' SI ES UNO COMO COMO VALOR STRING SI SON VARIOS EN UN ARRAY
     *  Y PARA LA EXPRECION 'ON' UN STRING CON LA EXPRECION
     *  SI $joins ES UN STRING ENTONCES SE UTILISARA COMO LA CLAUSULA GROUP Y
     *  TODOS LOS PARAMETROS SE CORRERAN HACIA LA IZQUIERDA
     *  @param string $group CLAUSULA GROUP
     *  @param string $having CLAUSULA HAVING
     *  @param string $order CLAUSULA ORDER
     *  @param string $limit CLAUSULA LIMIT
     *  @return this|false retorna false si ocurrio un error
     *  @uses SelectSql 
     *  @uses ResultManager::Query() 
     */
    public function Busqueda($cadena, $campo_bus, $campos = NULL, $where = NULL, $joins = NULL, $group = NULL, $having = NULL, $order = NULL, $limit = NULL)
    {
        if (is_null($campo_bus))
        {

            $campo_bus = $this->OrderColum;
        }
        $search = $this->SqlBusqueda($cadena, $campo_bus);

        list($Select, $campos, $where, $joins, $group, $having, $order, $limit) = $this->ResolvedSelectParams($campos, $where, $joins, $group, $having, $order, $limit);

        if (is_null($where) || $where == '')
        {
            $where = '(' . $search . ')>1';
        } else
        {
            $where = preg_replace('/^(\ {0, }where)/i', '', $where);
            $where = '(' . $where . ') and (' . $search . ')>1';
        }
        if (is_null($order))
        {
            $order = 'order by puntaje_busqueda DESC';
        }
        $campos[] = '(' . $search . ') as puntaje_busqueda';
        $sql = $Select
                . $this->Driver->Campos($campos) . "  FROM " . $this->Driver->ProtectIdentifiers($this->Tabla()) . $this->postTab . " "
                . implode(' ', $this->Join($joins)) . " "
                . $this->Where($where) . ' '
                . $group . ' '
                . $this->Having($having) . ' '
                . $order . ' '
                . $limit;

        // $sql = $this->SelectSql($campo_mos, $where, $joins, $group, $having, $order, $limit);
        // $sql = $this->CreateBusqueda($sql, $cadena, $campo_bus);
        if (($query = $this->Query($sql)) === false)
        {
            ErrorHandle::Notice("Error al consultar tabla " . $this->tabla . " errno:" . $this->errno . " error:" . $this->error);
        }
        return $query;
    }

    protected function SqlBusqueda($cadena, $campos)
    {
        if (is_array($cadena))
        {
            $cadena = implode(' ', $cadena);
        }
        $trozos = explode(' ', trim($cadena));
        $select = '';

        foreach ($campos as $campo)
        {
            $select.=" (" . $campo . " is NOT NULL AND " . $campo . " like '%" . $cadena . "%') + 
			(" . $campo . " is NOT NULL AND " . $campo . " like '" . $cadena . "%')+";
        }
        $noSearch = ['de', 'la', 'el', 'en', 'con', 'and', 'or', 'the', 'a', 'from', ' '];
        foreach ($trozos as $palabra)
        {
            if (in_array($palabra, $noSearch))
                continue;
            //if(strlen($palabra)>2 || (is_int($palabra) || is_float($palabra)))
            foreach ($campos as $campo)
            {
                $campo = $this->Driver->ProtectIdentifiers($campo);
                $select.=" (" . $campo . " is NOT NULL AND " . $campo . " like '%" . $palabra . "%') + (" . $campo . " is NOT NULL AND " . $campo . " like '" . $palabra . "%')+";
            }
        }
        $solo = '';
        if ($this->GetTypeDB() != self::SQLite)
        {
            if (count($campos) > 1)
            {
                $select.="(( CONCAT(";
                foreach ($campos as $i => $campo)
                {
                    $campo = $this->Driver->ProtectIdentifiers($campo);
                    $espace = !is_int($i) ? "''" : "' '";
                    $select.="IF($campo IS NOT NULL,$campo,' ')," . $espace . ",";
                }
                $select.="'') like '%" . $cadena . "%' )+1) ";
            } elseif (count($campos) == 1)
            {
                $select.="(0)";
                $campos[0] = $this->Driver->ProtectIdentifiers($campos[0]);
                $solo = "* (IF(" . $campos[0] . " = '" . $cadena . "',0 ,1))+(IF(" . $campos[0] . " = '" . $cadena . "',10,0))";
            }
        } else
        {
            $select.="(0)";
        }
        return $select;
    }

    /**
     * @access private
     * @return boolean|DBRow
     */
    public function current()
    {
        if (($current = parent::current()) !== false)
        {

            return $this->ActiveRow = new DBRow($this, $current);
        } else if ($this->sql == '')
        {
            $this->Select();
            return $this->current();
        }

        return false;
    }

    /**
     * retorna todo el resultado obtenido de la ultima consulta 
     * @param int $n
     * @param int $limit
     * @return array 
     * @depends Select
     * @depends Busqueda
     * @depends ExcUnion
     * @depends ResultManager::Query()
     */
    public function FetchAll($n = NULL, $limit = NULL)
    {
        $ret = [];
        while ($a = $this->fetch($n, $limit))
        {
            $ret[] = $a;
        }
        return $ret;
    }

    /**
     * 
     * @access private
     */
    public function jsonSerialize()
    {
        return $this->ResultJson()->Get();
    }

    /**
     * retorna la siguiente fila del resultado
     * <code>
     * <?php
     * $db= new PDO('mysqli:mysqli.db');
     * $mitabla=$db->Tab('mitabla');// creando un objeto DBtabla con PDO
     * $mitabla->Select();
     * $campo=$mitabla->fetch()
     * echo "campos1=".$campo['campo1']." campo2=".$campo['campo2'];
     * </code>
     * @param int $n
     * @param int $limit
     * @return \Cc\DBRow 
     */
    public function fetch($n = NULL, $limit = NULL)
    {
        return parent::fetch($n, $limit);
    }

    /**
     *  INSERTA UNA FILA EN LA TABLA
     * <code>
     * <?php
     * $db= new PDO('mysqli:mysqli.db');
     * $mitabla=$db->Tab('mitabla');// creando un objeto DBtabla con PDO
     * $mitabla->Insert("hola1","ejemplo");//insertando
     * </code>
     * <code>
     * <?php
     * $db= new PDO('mysqli:mysqli.db');
     * $mitabla=$db->Tab('mitabla');// creando un objeto DBtabla con PDO
     * $mitabla->Insert(["hola1","ejemplo"]);//insertando
     * </code>
     *  <code>
     * <?php
     * $db= new PDO('mysqli:mysqli.db');
     * $mitabla=$db->Tab('mitabla');// creando un objeto DBtabla con PDO
     * $mitabla->Insert(["campo1"=>"hola1","campo2"=>"ejemplo"]);//insertando
     * </code>
     *  @param ...$param LA FILA QUE SE INSERTARA SI ES UN ARRAY CON INDICES NUMERICOS INSERTAR POR ORDEN NUMERICO
     *  SI ES UN ARRAY DE INDICE ALFANUMERICO SE INSERTAR CON EL ORDEN QUE TENGA LA TABLA EN LA BASE DE DATOS
     *  TAMBIEN PUEDE SER UN OBJETO DBRow
     *  @return boolean TRUE SI OCURRIO EXITO DE LO CONTRARIO FALSE
     * @uses ResultManager::Excecute() 
     * 
     */
    public function Insert(...$param)
    {
        // $param = func_get_args();
        if ($this->Ttipe != self::table)
        {
            ErrorHandle::Notice("NO SE PUEDE INSERTAR YA QUE " . $this->Tabla() . " NO ES UNA TABLA");
            return false;
        }
        $nparams = count($param);
        if ($nparams == 0)
        {
            ErrorHandle::Notice("SE DEBE PASAR ALMENOS 1 PARAMETRO");
            return false;
        } elseif ($param[0] instanceof DBRow)
        {
            return $param[0]->Insert();
        } elseif ((is_array($param[0]) || $param[0] instanceof \Traversable) && $nparams == 1 && count($this->OrderColum) > 1)
        {

            $array = $param[0];
        } else
        {

            $array = $param;
        }

        $col = '';
        $int = false;
        foreach ($array as $i => $v)
        {
            if (is_numeric($i))
            {
                $int = true;
            }
            break;
        }
        $attrs = [];
        if (!$int)
        {

            foreach ($array as $coll => $v)
            {
                array_push($attrs, $coll);


                $col.=$this->Driver->FormatVarInsert($v, $coll) . ',';
            }
        } else
        {
            $attrs = $this->OrderColum;
            $count = count($array);

            for ($i = 1; $i <= $count; $i++)
            {
                //  if (isset($array[$i - 1]))
                $col.=$this->Driver->FormatVarInsert($array[$i - 1], $attrs[$i]) . ',';
            }
        }
        $colunas = '';
        if ($attrs)
        {
            $colunas = "(" . $this->Driver->Campos($attrs) . ")";
        }
        $sql = "INSERT INTO " . $this->Driver->ProtectIdentifiers($this->Tabla()) . $this->postTab . " " . $colunas . " VALUES (" . substr($col, 0, -1) . ");";

        if (!$this->Excecute($sql))
        {
            ErrorHandle::Notice("Error al insertar datos en la tabla " . $this->tabla . " errno:" . $this->errno . " error:" . $this->error);
            return false;
        }
        return true;
    }

    /**
     *  EJECUTA UNA SENTENCIA DELETE EN LA TABLA
     * <code>
     * <?php
     * $db= new PDO('mysqli:mysqli.db');
     * $mitabla=$db->Tab('mitabla');// creando un objeto DBtabla con PDO
     * $mitabla->Delete("campo1='hola'");//eliminado el registro que tenga hola en el campo1
     * </code>
     * <code>
     * <?php
     * $db= new PDO('mysqli:mysqli.db');
     * $mitabla=$db->Tab('mitabla');// creando un objeto DBtabla con PDO
     * $mitabla->Delete(["campo1"=>"hola"]);//eliminado el registro que tenga hola en el campo1
     * </code>
     *  @param string $where LA EXPRECION QUE SE UTILIZARA EN LA SENTENCIA WHERE
     *  @param array $values (OPCIONAL) VARIABLES SQL
     *  @return bool TRUE SI TUVO EXITO DE LO CONTRARIO FALSE
     * @uses ResultManager::Excecute() 
     * 
     */
    public function Delete($where)
    {
        if ($this->Ttipe != self::table)
        {
            ErrorHandle::Notice("NO SE PUEDE ELIMINAR YA QUE " . $this->Tabla() . " NO ES UNA TABLA");
            return false;
        }
        $sql = "DELETE FROM " . $this->Driver->ProtectIdentifiers($this->Tabla()) . " " . $this->Where($where);
        if (!$this->Excecute($sql))
        {
            ErrorHandle::Notice("Error al eliminar datos de la tabla " . $this->tabla . " errno:" . $this->errno . " error:" . $this->error);
            return false;
        }
        return true;
    }

    /**
     *  EJECUTA UNA SENTENCIA UPDATE EN LA TABLA
     * <code>
     * <?php
     * $db= new PDO('mysqli:mysqli.db');
     * $mitabla=$db->Tab('mitabla');// creando un objeto DBtabla con PDO
     * $mitabla->Update(["campo1"=>"hello"],"campo1"=>"hola");
     * </code>
     *  <code>
     * <?php
     * $db= new PDO('mysqli:mysqli.db');
     * $mitabla=$db->Tab('mitabla');// creando un objeto DBtabla con PDO
     * $mitabla->Update(["campo1"=>"hello"],"campo1='hola'");
     * </code>
     * @param mixes $SETS DATOS QUE SERAN CAMBIADOS array('campo'=>'value')
     * @param string $where LA EXPRECION QUE SE UTILIZARA EN LA SENTENCIA WHERE
     * @return bool TRUE SI TUVO EXITO DE LO CONTRARIO FALSE
     * @uses ResultManager::Excecute() 
     * 
     */
    public function Update($SETS, $where = NULL)
    {
        if ($this->Ttipe != self::table)
        {
            ErrorHandle::Notice("NO SE PUEDE ACTUALIZAR YA QUE " . $this->Tabla() . " NO ES UNA TABLA");
            return false;
        }
        if ($SETS instanceof DBRow)
        {
            return $SETS->Update();
        }
        $col = '';





        if (is_null($where))
        {
            if (count($this->GetPrimary()) > 0)
            {
                $where = array();

                foreach ($this->GetPrimary() as $v)
                {
                    $where+=[$v => $SETS[$v]];
                    unset($SETS[$v]);
                }
            } else
            {
                ErrorHandle::Notice("No se ha encontrado claves primarias en la tabla " . $this->Tabla() . " por lo tanto el parametro \$where es obligatorio");
                return false;
            }
        }
        foreach ($SETS as $i => $v)
        {

            $col.=$this->Driver->ProtectIdentifiers($i) . "=" . $this->Driver->FormatVarInsert($v, $i) . ",";
        }
        $col = substr($col, 0, -1);
        $sql = "UPDATE " . $this->Tabla() . " SET " . $col . " " . $this->Where($where);

        if (!$this->Excecute($sql))
        {
            ErrorHandle::Notice("Error al editar datos de la tabla " . $this->tabla . " errno:" . $this->errno . " error:" . $this->error);
            return false;
        }
        return true;
    }

    /**
     *  GENERA UNA NUEVA FILA | OBJETO DBRow Y LO ACTIVA EN LA CLASE,
     *  ESTO NO SINGNIFICA QUE SERA INSERTDA UNA NUEVA FILA EN LA TABLA A MENOS QUE SE EJECUTE EL METODO Insert
     * @param array $row fila 
     *  @return DBRow  REFERENCIA DEL OBJETO DBRow ACTIVO
     */
    public function NewRow($row = [])
    {
        return $this->ActiveRow = new DBRow($this, $row);
    }

    /**
     * 
     * @return array NOMBRES DE CLAVES PRIMARIAS
     */
    public function GetPrimary()
    {
        return $this->primary;
    }

    /**
     * 
     * @return string TIPO DE TABLA 
     */
    public function GetTypeTab()
    {
        return $this->Ttipe;
    }

    /**
     * INSERTA UNA VARIABLE SQL EN LA BASE DE DATOS
     *  @param mixes $values  NOMBRE DE LA VARIABLE SI SE LE PASA UN ARRAY ENTOCES INSERTARA LAS VARIABLES QUE ESTE CONTENGA
     *  @param mixes $v2 VALOR DE LA VARIABLE
     *  @return this  AUTOREFERENCIA
     */
    public function &SetVar($values = NULL, $v2 = NULL)
    {
        if (is_array($values))
        {

            foreach ($values as $i => $v)
            {

                $v = $this->FormatVarInsert($v);

                $this->db->query("SET @" . $i . ":=" . $v . ";");
            }
        } else
        {

            $this->db->query("SET @" . $values . ":=" . $this->FormatVarInsert($v2) . ";");
        }
        return $this;
    }

    /**
     *  RETORNA UN ARRAY CON TODOS LOS NOMBRES DE LAS COLUMNAS DE LA TABLA
     *  @return array
     */
    public function GetCol($name = NULL)
    {
        if (is_null($name))
        {
            return $this->colum;
        } elseif (isset($this->colum[$name]))
        {
            return $this->colum[$name];
        } else
        {
            ErrorHandle::Notice("LA COLUMNA " . $name . " NO EXISTE ");
        }
    }

    /**
     * 
     * ESTE METODO CREARA LAS CLAUSULAS JOINS
     * @param array $join  CLAUSULAS JOIS UTILIZANDO UN ARRAY CON LAS TABLAS QUE SE RELACIONARAN ATEPONIENDO LOS SINGNOS <,>,=  
     * < SERA PARA LEFT JOIN 
     * > SERA PARA RINGT JOIN
     * = SERA INNERT JOIN 
     * SI NO SE ANTEPONE NADA SERA NATURAL JOIN
     * SERA TOMADO DE LAS CLAVES PROMARIAS QUE CONTENGAN LAS TABLAS PARA LA CLAUSULA USING
     * PARA COLOCAR LA CLAUSULA 'ON' O 'USING' EXPLICITAMENTE SE COLOCA EL NOMBRE DE LA TABLA COMO INDICE 
     *  LOS CAMPOS 'USING' SI ES UNO COMO COMO VALOR STRING SI SON VARIOS EN UN ARRAY
     *  Y PARA LA EXPECION 'ON' UN STRING CON LA EXPRECION 
     * @return string Sentencias Jois 
     */
    protected function Join($join)
    {

        $J = array();
        $keys = $this->OrderColum;
        if (is_array($join))
            foreach ($join as $i => $v)
            {
                $using = '';
                $tipe = '';
                if (!is_numeric($i))
                {
                    $tabla = $i;
                    if (!is_array($v) && preg_match("/( and )|( or )|=|\!|>|</", $v))
                    {
                        $using = ' on(' . $v . ')';
                    } elseif (is_array($v))
                    {
                        $using = ' using(' . $this->SqlIdent . implode($this->SqlIdent . ',' . $this->SqlIdent, $v) . $this->SqlIdent . ')';
                    } else
                    {
                        $using = ' using(' . $this->SqlIdent . $v . $this->SqlIdent . ')';
                    }
                } else
                {
                    $tabla = $v;
                }
                switch ($tabla[0])
                {
                    case '>':
                        $tipe = 'left';
                        $tab = new static($this->db, substr($tabla, 1));
                        break;
                    case '<':
                        $tipe = 'right';
                        $tab = new static($this->db, substr($tabla, 1));
                        break;
                    case '=':
                        $tipe = 'INNERT';
                        $tab = new static($this->db, substr($tabla, 1));
                        break;
                    default:
                        $tipe = 'natural';
                        $tab = new static($this->db, $tabla);
                        $using = '';
                        break;
                }

                if ($using == '' && $tipe != 'natural')
                {
                    $key = array();


                    if (!$tab->primary)
                    {
                        foreach (array_keys($tab->colum) as $i => $p)
                        {
                            if (in_array($p, $keys))
                            {
                                array_push($key, $p);
                            }
                        }
                    } else
                    {
                        foreach ($tab->primary as $i => $p)
                        {
                            if (in_array($p, $keys))
                            {
                                array_push($key, $p);
                            }
                        }
                    }
                    $using = ' using(' . $this->SqlIdent . implode($this->SqlIdent . ',' . $this->SqlIdent, $key) . $this->SqlIdent . ')';
                }

                $keys = array_merge($keys, $tab->OrderColum);

                array_push($J, " " . $tipe . " JOIN " . $this->Driver->ProtectIdentifiers($tab->Tabla()) . " " . $using);
                unset($tab);
            }
        return $J;
    }

    /**
     * crea una exprecion where 
     * @param mixes $where si es un string se tomara ta cual si es un array el indice sera el atrivuto y el valor el valor valga la redundancia
     * @return string
     */
    protected function Where($where = NULL)
    {
        if (is_null($where))
        {
            return '';
        } elseif (is_array($where))
        {
            $valores = $where;
            $where = '';
            foreach ($valores as $i => $v)
            {
                $where.=' ' . $this->Driver->ProtectIdentifiers($i) . '=' . $this->Driver->FormatVarInsert($v) . ' and';
            }
            $where = substr($where, 0, -3);
        }
        if (!preg_match('/^(\ {0,}WHERE )/i', $where))
            return "WHERE " . $where;
    }

    /**
     * 
     * @param type $having
     * @return string
     */
    protected function Having($having)
    {
        if (is_null($having))
        {
            return '';
        }
        if (!preg_match('/^(\ {0,}HAVING )/i', $having))
        {
            return "HAVING " . $having;
        } else
        {
            return $having;
        }
    }

    /**
     * 
     * @return string nombre de la tabla asociada
     */
    protected function Tabla()
    {
        return $this->tabla;
    }

    /**
     * obtiene los atributos con sus indices de la tabla
     * @param string $tab
     * @return boolean
     */
    protected function keys($tab)
    {
        $this->Driver = $this->CreateDriver($tab);
        $this->Driver->CreateKeys();
        /* @var $driver DB\Drivers */
        $this->colum = $this->Driver->Colum();
        $this->primary = $this->Driver->PrimaryKey();
        $this->autoinrement = $this->Driver->auto_incremet();
        $this->Ttipe = $this->Driver->Ttipe;

        $this->SqlIdent = $this->Driver->_escape_char;
        $this->OrderColum = $this->Driver->OrderColum();
    }

    protected function CreateDriver($tab)
    {
        $class = __NAMESPACE__ . "\\DB\\Drivers\\" . $this->typeDB;
        if (!class_exists($class))
        {
            throw new Exception(" NO EXISTE EL DRIVER DE " . $class);
        }

        return new $class($this->db, $tab);
    }

    /**
     * 
     * @return DB\Drivers
     */
    public function &Driver()
    {
        return $this->Driver;
    }

    /**
     * RETORNA LOS VALORES ACEPTADOS POR UN ATRIBUTOS DE TIPO ENUM
     * @param string $attr
     * @return array 
     */
    public function GetValuesEnum($attr)
    {
        $a = [];
        if (preg_match("/enum\(.*\)/", $this->colum[$attr]['Type'], $a))
        {
            $exp = explode(",", substr($a[0], 5, -1));
            $ret = [];
            foreach ($exp as $v)
            {
                $ret[] = str_replace("'", "", $v);
            }
            return $ret;
        } else
        {
            return array();
        }
    }

    public function __call($name, $arguments)
    {
        if ($this->GetTypeDB() == self::PostgreSQL && 0 === strncmp('pg', $name, strlen('pg')))
        {
            $tabla = $this->postTab;
            switch ($name)
            {
                case 'pgSelect':
                    $this->postTab = '*';
                    $result = $this->Select(...$arguments);
                    $this->postTab = $tabla;
                    break;
                case 'pgInsert':
                    $this->postTab = '*';
                    $result = $this->Insert(...$arguments);
                    $this->postTab = $tabla;
                    break;
                case 'pgUpdate':
                    $this->postTab = '*';
                    $result = $this->Update(...$arguments);
                    $this->postTab = $tabla;
                    break;
                case 'pgDelete':

                    $result = $this->Delete(...$arguments);
                    $this->tabla = $tabla;
                    break;
                case 'pgLOBCreate':
                    if ($this->db instanceof \PDO)
                    {
                        $result = $this->db->pgsqlLOBCreate();
                    } else
                    {
                        throw new Exception("EL METODO " . self::class . "::" . $name . " NO EXISTE ");
                    }
                    break;
                case 'pgLOBOpen':
                    if ($this->db instanceof \PDO)
                    {
                        $result = $this->db->pgsqlLOBOpen(...$arguments);
                    } else
                    {
                        throw new Exception("EL METODO " . self::class . "::" . $name . " NO EXISTE ");
                    }

                    break;
                case 'pgLOBUnlink':
                    if ($this->db instanceof \PDO)
                    {
                        $result = $this->db->pgsqlLOBUnlink(...$arguments);
                    } else
                    {

                        throw new Exception("EL METODO " . self::class . "::" . $name . " NO EXISTE ");
                    }

                    break;

                default :

                    throw new Exception("EL METODO " . self::class . "::" . $name . " NO EXISTE ");
            }
            return $result;
        } else
        {
            throw new Exception("EL METODO " . self::class . "::" . $name . " NO EXISTE ");
        }
    }

}
