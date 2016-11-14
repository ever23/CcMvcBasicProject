<?php

/**
 * La clase SearchClass es software libre: usted puede redistribuirlo y / o modificarlo
 * bajo los términos de la General Public License, de GNU según lo publicado por
 * la Free Software Foundation, bien de la versión 3 de la Licencia, o
 * cualquier versión posterior.
 * 
 * ESTE SOFTWARE ES PROPORCIONADO POR EL PROPIETARIO DEL COPYRIGHT Y COLABORADORES 
 * "TAL COMO ESTÁ" SIN GARANTIA EXPRESA O IMPLÍCITAS DE CUALQUIER TIPO, INCLUYENDO, PERO NO LIMITADO A,
 * LAS GARANTÍAS IMPLÍCITAS DE COMERCIABILIDAD E IDONEIDAD PARA UN PROPÓSITO. 
 * EN NINGÚN CASO LOS DERECHOS DE AUTOR DEL PROPIETARIO O COLABORADORES SERÁN RESPONSABLES DE DAÑOS DIRECTOS,
 * INDIRECTOS, INCIDENTALES, ESPECIAL, EJEMPLARES O CONSECUENTES( INCLUYENDO,
 * PERO NO LIMITADO A,ADQUISISION Y SUSTITUCIÓN DE BIENES Y SERVICIOS; 
 * PÉRDIDA DE USO, DE DATOS, O BENEFICIOS O INTERRUPCIÓN DEL NEGOCIO) CAUSADOS COMO FUERE EN CUALQUIER TEORÍA DE RESPONSABILIDAD
 * CONTRACTUAL, RESPONSABILIDAD ESTRICTA O RESPONSABILIDAD CIVIL( INCLUYENDO NEGLIGENCIA O CUALQUIER OTRA FORMA) 
 * QUE SURJAN DE NINGUNA MANERA DEL USO DE ESTE SOFTWARE, AUNQUE INFORMADOS DE LA POSIBILIDAD DE TALES DAÑOS.
 */

namespace Cc\Autoload;

/**
 * SearchClass                                                      
 * BUSCA UNA CLASE, INTERFACE O TRAIT DEFINIDA EN UN DIRECTORIO, FABRICA UN OBJETO O PUEDE 
 * ACTIVAR UNA FUNCION EN LA PILA spl_autoload                                                       
 *                                                                              
 * @version 1.0.3.0                                                            
 * @fecha 2016-05-26                                                            
 * @autor ENYREBER FRANCO       <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                  
 * @copyright © 2015-2016, Enyerber Franco
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License  
 * 
 * @package Cc
 * @subpackage Autoload
 * @internal
 */
class SearchClass
{

    /**
     * directorio en el que sera realizada la busqueda 
     * @var string|array
     */
    private $dir; //directorio de busqueda
    /**
     * directorios en los que se buscaran las clases
     * @var array|string 
     */
    private static $DIR_GLOBAL = '';

    /**
     * extenciones de archivos a buscar
     * @var array 
     */
    private static $ext = array('php');

    /**
     * nombre de la clase a buscar
     * @var string 
     */
    protected $class;

    /**
     *  espacios con nombre de la clase a buscar
     * @var array 
     */
    protected $namespaces = [];

    /**
     * nombre del archivo en el que se encontro la clase
     * @var string
     */
    protected $filename = false; //nombre de archivo donde se encontro clase
    /**
     * directorios y archivos donde se busco la clase
     * @var array 
     */
    protected $directorios = array(); //archivos donde se busco
    /**
     *
     * @var arrray 
     */
    public static $DirFiles = array();

    /**
     *
     * @var bool 
     */
    protected $avance_searh = false;

    /**
     *
     * @var array 
     */
    public static $classes = array();

    /**
     *
     * @var array 
     */
    protected static $SearchDefines = ['class', 'interface', 'trait'];

    /**
     *
     * @var bool 
     */
    protected $CaseSensitive = false;

    /**
     * 
     */
    const AllClass = '.*';

    /**
     * 
     * 
     */
    const NamespaceSeparator = '\\';

    /**
     *
     * @var array 
     */
    protected static $LastSeacrhClass = [T_CLASS => [], T_TRAIT => [], T_INTERFACE => []
            /*
              [class,traits,interfaces],
             */
    ];

    /**
     *  BUSCA UNA CLASE, INTERFACE O TRAIT DEFINIDAD EN UN DIRECTORIO
     *  @param string $class nombre de la clase a buscar
     *  @param string $dir directorio donde se buscara la clase
     */
    public function __construct($class, $dir = NULL, $avance_searh = false, $CaseSensitive = false)
    {
        $this->CaseSensitive = $CaseSensitive;
        $c = explode(self::NamespaceSeparator, $class);
        $this->class = array_pop($c);
        $this->namespaces = $c;
        $this->avance_searh = $avance_searh;
        $this->SetDir(is_null($dir) ? self::$DIR_GLOBAL : $dir);
        $this->directorios = array();
    }

    public function getLastFiles()
    {
        return $this->directorios;
    }

    /**
     * limpia la lista de clases encontradas
     */
    public static function ClearListClass()
    {
        self::$classes = [];
    }

    /**
     * GENERA UN ARRAY TIPO CLAVE=>VALOR CON LOS NOMBRES DE LAS CLASE, INTERFACE O TRAIT QUE SE ENCUENTRAN EN EL COMO INDICE
     * Y COMO VALOR LA DIRECCION LOCAL
     * @param string $dir
     * @return array
     */
    public static function GetListAllClass($dir)
    {
        self::$classes = [];
        $e = new SearchClass(self::AllClass, $dir, true);
        $e->Search();
        $ARRAY = [];
        foreach (self::$classes as $i => $v)
        {
            $ARRAY[$i] = str_replace($dir, '', $v);
        }
        return $ARRAY;
    }

    /**
     * RETORNA UN ARRAY CON TODOS LOS DIRECTORIOS DONDE LA CLASE BUSCO
     * @return array
     */
    public static function GetDirFiles()
    {
        return self::$DirFiles;
    }

    /**
     *  FABRICA UN OBJETO DE LA CLASE PASADA EN EL CONSTRUCTOR
     * @param array $param PARAMETROS QUE SE LE PASARAN AL CONSTRUCTOR DE LA CLASE
     * @param bool $autoload indica se se incluira de el directorio resultante de la llamada a Search()
     * o no
     * @return mixes
     * @throws SearchClassException
     */
    public function FactoryObject($param = array(), $autoload = false)
    {
        $class = implode(self::NamespaceSeparator, $this->namespaces) . self::NamespaceSeparator . $this->class;
        if (!class_exists($class, $autoload) && $this->filename)
        {
            $this->Include_('require_once');
        }
        //  $class = $this->class;
        $classname = NULL;


        try
        {
            // echo '<pre>', var_dump( debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            return new $class(...$param);
        } catch (SearchClassException $e)
        {

            throw $e;
        }
    }

    /**
     * @deprecated 
     */
    public static function &Factory($class = NULL, $param = [], $dir = NULL, $avance = NULL, $use_load = true)
    {
        if ($use_load)
        {
            if (!$autoload = self::Load($class, $dir, $avance, true, 'include'))
                return NULL;
        }

        return $autoload->FactoryObject($param, !$use_load);
    }

    /**
     * AGREGA UN DIRECTORIO DONDE SERA BUSCADA LA CLASE, INTERFACE O TRAIT 
     * @param mixes $dir DIRECTORIO 
     * @param bool $avance
     */
    public static function AddDirAutoload($dir, $avance = NULL)
    {
        if (is_array(self::$DIR_GLOBAL))
        {
            if (is_array($dir))
            {
                foreach ($dir as $v)
                {
                    self::AddDirAutoload($v['dir'], $v['avance']);
                }
// self::$DIR_GLOBAL+=$dir;
            } else
            {
                array_push(self::$DIR_GLOBAL, array(
                    'dir' => $dir,
                    'avance' => $avance));
            }
        } else
        {

            self::$DIR_GLOBAL = array();
            if (is_array($dir))
            {
                foreach ($dir as $v)
                {
                    self::AddDirAutoload($v['dir'], $v['avance']);
                }
// self::$DIR_GLOBAL+=$dir;
            } else
            {
                array_push(self::$DIR_GLOBAL, array(
                    'dir' => $dir,
                    'avance' => $avance));
            }
        }
    }

    /**
     *  FUNCION STATIC DEFINE LA FUNCION __autoload UNA VES LLAMADO ESTE METODO LAS CLASES SE CARGARAN DEL O LOS DIRECTORIOES ESPECIFICADOS
     *  @param mixes $dir DIRECTORIO DONDE SE BUSCARA LA CLASE
     *  @param bool $avance INDICA SI SEREALIZARA UNA BUSQUEDA AVANZADA
     */
    public static function StartAutoloadClass($dir = NULL, $avance = NULL)
    {
        if (!is_null($dir))
        {
            self::AddDirAutoload($dir, $avance);
        }
        return spl_autoload_register(array(static::class, 'Load'));
    }

    public static function StopAutoloadClass()
    {
        return spl_autoload_unregister(array(static::class, 'Load'));
    }

    /**
     *  BUSCA Y CARGA UNA CLASE
     *  @param string $class NOMBRE SE LA CLASE A BUSCAR
     *  @param string $dir DIRECTORIO DONDE SE BUSCARA LA CLASE (OPCIONAL)
     *  @param bool $avance INDICA SI SEREALIZARA UNA BUSQUEDA AVANZADA
     *  @param bool $is_autoload IDICA SI SE USARA LA VARIABLE DIRECTORIO DE AddDirLoad
     *  @param string $fnload le funcion de craga
     *  @return bool TRUE SI TUBO EXITO
     */
    public static function &Load($class, $dir = NULL, $avance = false, $is_autoload = true, $fnload = 'require_once')
    {

        if (is_null($fnload))
        {
            if (!defined('__FN_AUNTOLOAD__'))
            {
                $FN = 'include';
            } else
            {
                $FN = __FN_AUNTOLOAD__;
            }
        } else
        {
            $FN = $fnload;
        }
        if ($is_autoload)
        {
            if (is_null($dir))
            {
                $dir = self::$DIR_GLOBAL;
            }
        } else
        {
            if (is_null($dir))
            {
                if (!defined('__DIR_AUNTOLOAD__'))
                {
                    $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
                } else
                {
                    $dir = __DIR_AUNTOLOAD__;
                }
            }
        }
        $autoload = new SearchClass($class, $dir, $avance);
        if (($autoload->Search()) == NULL)
        {
//$dir=$autoload->PrintArray($autoload->directorios);
//self::$DirFiles=array_merge(self::$DirFiles,array($class=>$autoload->directorios));;
//echo new SearchClassException(' Warning: CLASE "'.$class.'" NO ENCONTRADA O NO DEFINIDA EN EL DIRECTORIO (<pre>'.$dir."</pre>)"); 
            $d = false;
            return $d;
        }
        self::$DirFiles = array_merge(self::$DirFiles, array(
            $class => $autoload->directorios));
        ;
        $autoload->Include_($FN);
        return $autoload;
    }

    /**
     * CARGA LA CLASE, INTERFACE O TRAIT CON LA FUNCION DE CRAGA PASADA EN $FN 
     * QUE DEVE SER 
     * include,include_once,require o require_once
     * @param string $FN
     * @throws SearchClassException
     */
    public function Include_($FN = 'include_once')
    {
        if (!file_exists($this->filename))
        {
            throw new SearchClassException("EL ARCHIVO " . $this->filename . " NO EXISTE");
        }
        switch ($FN)
        {
            case "include":
                include $this->filename;
                break;
            case "include_once":
                include_once $this->filename;
                break;
            case "require":
                require $this->filename;
                break;
            case "require_once":
//echo $this->class.'<br>';
                // echo $this->filename;
                require_once $this->filename;
                break;
            default:
                throw new SearchClassException(" " . $FN . " NO ES UNA FUNCION VALIDA DE CARGA DE ARCHIVOS PHP");
        }
    }

    /**
     * establece el directorio donde se buscaran las clases
     * @param string $dir
     * @return \Cc\Autoload\SearchClass
     * @throws SearchClassException
     */
    public function &SetDir($dir)
    {
        if (is_array($dir))
        {
            $this->dir = $dir;
            foreach ($this->dir as $d)
            {
                if (!is_dir($d['dir']))
                {
                    throw new SearchClassException("EL DIRECTORIO " . $this->dir . " NO EXISTE ");
                }
            }
        } else
        {
            $this->dir = is_null($dir) ? dirname(__FILE__) . DIRECTORY_SEPARATOR : $dir;
            if (!is_dir($this->dir))
            {
                throw new SearchClassException("EL DIRECTORIO " . $this->dir . " NO EXISTE ");
            }
        }

        return $this;
    }

    /**
     *  BUSCA Y RETORNA EL NOMBRE DEL ARCHIVO DONDE SE ENCUENTRA LA CLASE PASADA AL CONSTRUCTOR
     *  @param bool $recursive indica si se realizara una busqueda recursiva en los directorios
     *  @return string NOMBRE DEL ARCHIVO DONDE SE ENCUENTA DEFINIDA LA CLASE
     */
    public function Search($recursive = true)
    {
        if ($file = $this->SearchLastClass())
        {
            $this->filename = $file;
            return true;
        }
        $this->directorios = array();
        if (is_array($this->dir))
        {
            $avance = $this->avance_searh;
            foreach ($this->dir as $dir)
            {
                $this->avance_searh = $dir['avance'];

                if ($this->NombClassFileExists($dir['dir']))//VERIFICO SI EXISTE UN ARCHIVO CON EL NOMBRE DE LA CLASE
                {

                    if ($dir['avance'])
                    {

                        if ($this->FileSearch($this->GetFileName()))
                        {
                            $file = $this->GetFileName();
                            array_push($this->directorios, $file);

                            return $file;
                        } else
                        {
                            $this->DirSearch($dir['dir'], $dir['avance'], $recursive);
                            if ($this->GetFileName())
                            {

                                return $this->GetFileName();
                            }
                        }
                    } else
                    {
                        $file = $this->GetFileName();
                        array_push($this->directorios, $file);

                        return $file;
                    }
                } else
                {
                    $this->DirSearch($dir['dir'], $dir['avance'], $recursive);
                    if ($this->GetFileName())
                    {

                        return $this->GetFileName();
                    }
                }
            }

            $this->avance_searh = $avance;
        } else
        {
            if ($this->NombClassFileExists($this->dir))
            {

                if ($this->avance_searh)
                {
                    if ($this->FileSearch($this->GetFileName()))
                    {

                        return $this->GetFileName();
                    } else
                    {
                        $this->DirSearch($this->dir, $this->avance_searh, $recursive);
                    }
                } else
                {
                    return $this->GetFileName();
                }
            } else
            {
                $this->DirSearch($this->dir, $this->avance_searh, $recursive); //busco la clase en todos los archivos .php
            }
        }

        if ($this->GetFileName())
        {
            return $this->GetFileName();
        }

        return NULL;
    }

    /**
     * realiza una busqueda el los archivos antes abiertos 
     * @return boolean
     */
    protected function SearchLastClass()
    {
        $actNamespace = '';
        if (count($this->namespaces))
        {
            $actNamespace = implode(self::NamespaceSeparator, $this->namespaces) . self::NamespaceSeparator;
        }
        $class = $actNamespace . $this->class;
        //echo $class.'<br><pre>';
        // var_dump(self::$LastSeacrhClass);
        if (isset(self::$LastSeacrhClass[T_CLASS][$class]) && $this->VerificaDirectory(self::$LastSeacrhClass[T_CLASS][$class]))
        {
            return self::$LastSeacrhClass[T_CLASS][$class];
        } elseif (isset(self::$LastSeacrhClass[T_TRAIT][$class]) && $this->VerificaDirectory(self::$LastSeacrhClass[T_TRAIT][$class]))
        {
            return self::$LastSeacrhClass[T_TRAIT][$class];
        } elseif (isset(self::$LastSeacrhClass[T_INTERFACE][$class]) && $this->VerificaDirectory(self::$LastSeacrhClass[T_INTERFACE][$class]))
        {
            return self::$LastSeacrhClass[T_INTERFACE][$class];
        }
        return false;
    }

    /**
     * verifica que la clase se encuentre en el directorio de busqueda 
     * @param string $file
     * @return boolean
     */
    protected function VerificaDirectory($file)
    {
        $FILE = new \SplFileInfo($file);
        if (is_array($this->dir))
        {
            foreach ($this->dir as $d)
            {
                if (preg_match('/' . addcslashes($d['dir'], '\\,/,:') . '/', $file))
                {
                    return true;
                }
            }
        } else
        {
            if (preg_match('/' . addcslashes($this->dir, '\\,/,:') . '/', $file))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * retorna el nombre del archivo 
     */
    public function GetFileName()
    {
        return $this->filename;
    }

    /**
     * verifica si existe un archivo con el mismo nombre de la clase buscada 
     * @param string $dir
     * @return boolean
     */
    protected function NombClassFileExists($dir)
    {

        foreach (self::$ext as $ext)
        {
            $classfile1 = $dir . $this->class . "." . $ext;

            $classfile2 = $dir . strtolower($this->class) . "." . $ext;
            $cf1 = file_exists($classfile1);

            if ($cf1 || file_exists($classfile2))
            {
                $this->filename = $cf1 ? $classfile1 : $classfile2;
                return true;
            }

            $namespace1 = $dir . implode(DIRECTORY_SEPARATOR, $this->namespaces) . DIRECTORY_SEPARATOR . $this->class . "." . $ext;
            $namespace2 = $dir . strtolower(implode(DIRECTORY_SEPARATOR, $this->namespaces) . DIRECTORY_SEPARATOR . $this->class . "." . $ext);
            $cf1 = file_exists($namespace1);

            if ($cf1 || file_exists($namespace2))
            {
                $this->filename = $cf1 ? $namespace1 : $namespace1;
                return true;
            }
        }
        return false;
    }

    /**
     *  BUCA LA DEFINICION DE LA  CLASE EN UN ARCHIVO
     *  @param string $file NOMBRE DEL ARCHIVO
     *  @return bool true si se encontro
     */
    protected function FileSearch($file)
    {
        $cadena = file_get_contents($file);




        $r = $this->SearchTonkens($cadena, $file);

        if (!$r)
        {
            $this->filename = false;
        } else
        {
            $this->filename = $file;
        }
        return $r;
    }

    /**
     * realiza una busqeuda de clase ,interfaces, traits y namespaces 
     * @param string $cadena
     * @param string $file
     * @return boolean
     */
    protected function SearchTonkens($cadena, $file)
    {


        $tokens = token_get_all($cadena);

        $TokensSave = [T_CLASS => [], T_INTERFACE => [], T_TRAIT => [], T_NAMESPACE => []];
        $TokensBool = [T_CLASS => false, T_INTERFACE => false, T_TRAIT => false, T_NAMESPACE => false, T_CONST => false];
        $actualNamespace = '';


        $Namespaces = ['' => [T_CLASS => [], T_INTERFACE => [], T_TRAIT => []]];
        $namespace = '';
        foreach ($tokens as $iToken => $token)
        {
            $id = $text = NULL;
            if (!is_array($token))
            {
                // echo $token;
                if ($TokensBool[T_NAMESPACE] && (trim($token) == ';' || trim($token) == '{'))
                {

                    $TokensBool[T_NAMESPACE] = false;


                    $Namespaces[$actualNamespace] = [T_CLASS => [], T_INTERFACE => [], T_TRAIT => []];
                    $namespace = $actualNamespace;

                    $actualNamespace = '';

                    // $actualClases = [];
                }
                if (trim($token) == ';')
                {

                    $TokensBool[T_CONST] = false;
                }
                if (trim($token) == '{')
                {

                    if ($TokensBool[T_CLASS])
                    {
                        $TokensBool[T_CLASS] = false;
                        /* if($file == 'E:\www\CcMvc\CcMvc\app\App.php')
                          {
                          echo '<pre>', var_dump($TokensSave[T_CLASS]), '<br>';
                          //  echo $file;
                          } */
                        if (isset($TokensSave[T_CLASS][0]))
                            array_push($Namespaces[$namespace][T_CLASS], $TokensSave[T_CLASS][0]);
                        $TokensSave[T_CLASS] = [];
                    } elseif ($TokensBool[T_INTERFACE])
                    {
                        $TokensBool[T_INTERFACE] = false;
                        if (isset($TokensSave[T_INTERFACE][0]))
                            array_push($Namespaces[$namespace][T_INTERFACE], $TokensSave[T_INTERFACE][0]);
                        $TokensSave[T_INTERFACE] = [];
                    } elseif ($TokensBool[T_TRAIT])
                    {
                        $TokensBool[T_TRAIT] = false;
                        if (isset($TokensSave[T_TRAIT][0]))
                            array_push($Namespaces[$namespace][T_TRAIT], $TokensSave[T_TRAIT][0]);
                        $TokensSave[T_TRAIT] = [];
                    }
                }
            }else
            {

                list($id, $text) = $token;
                // echo token_name($id) . ' ' . $text;
                switch ($id)
                {
                    case T_COMMENT:
                    // $TokensBool[$id] = true;
                    case T_DOC_COMMENT: // y esto
                        break;
                    case T_NS_SEPARATOR:
                        if ($TokensBool[T_NAMESPACE])
                        {
                            $actualNamespace.=$text;
                        }
                        break;
                    case T_CONST:
                        $TokensBool[T_CONST] = true;
                        break;
                    case T_CLASS:
                        if (is_array($tokens[$iToken - 1]) && $tokens[$iToken - 1][0] == T_DOUBLE_COLON)
                        {
                            $TokensBool[$id] = false;
                        } else
                        {
                            $TokensBool[$id] = true;
                        }
                        break;
                    case T_TRAIT:
                    case T_INTERFACE:
                    case T_NAMESPACE:
                        $TokensBool[$id] = true;
                        // $namespace = '';
                        // echo $text;
                        break;
                    case T_STRING:

                        if ($this->CaseSensitive)
                        {
                            $text = strtoupper($text);
                        }

                        if ($TokensBool[T_CLASS])
                        {
                            //$TokensBool[T_CLASS] = false;
                            array_push($TokensSave[T_CLASS], $text);
                            // array_push($Namespaces[$namespace][T_CLASS], $text);
                        } elseif ($TokensBool[T_INTERFACE])
                        {
                            array_push($TokensSave[T_INTERFACE], $text);
                        } elseif ($TokensBool[T_TRAIT])
                        {
                            array_push($TokensSave[T_TRAIT], $text);
                        } elseif ($TokensBool[T_NAMESPACE])
                        {
                            $actualNamespace.=$text;
                        }

                        break;
                }
            }
        }
        // if($file == 'E:\www\CcMvc\CcMvc\app\App.php')  exit;
        return $this->MapingTokenNamespace($Namespaces, $file);
        //  exit; 
    }

    private function MapingTokenNamespace(array $TokesNamespasce, $file)
    {
        if ($this->class == self::AllClass)
        {
            foreach ($TokesNamespasce as $namespace => $Tokens)
            {
                foreach ($Tokens as $class)
                {
                    foreach ($class as $c)
                    {
                        $name = '';
                        if ($namespace != '')
                            if ($namespace != '')
                            {
                                $name = $namespace . self::NamespaceSeparator;
                            }
                        self::$classes+=array($name . $c => $file);
                    }
                }
            }
            return false;
        }
        $actNamespace = implode(self::NamespaceSeparator, $this->namespaces);
        $return = false;
        foreach ($TokesNamespasce as $namespace => $Tokens)
        {
            foreach ($Tokens as $i => $class)
            {
                foreach ($class as $ic => $c)
                {
                    $name = '';
                    if ($namespace != '')
                    {
                        $name = $namespace . self::NamespaceSeparator;
                    }
                    self::$classes+=array($name . $c => $file);
                    self::$LastSeacrhClass[$i]+=[$name . $c => $file];
                    //self::$classes+=array($name . $c => $file);
                    if (!$this->CaseSensitive)
                    {
                        if (strtolower($this->class) == strtolower($c) && strtolower($namespace) == strtolower($actNamespace))
                        {
                            $return = true;
                        }
                        continue;
                    }
                    if ($this->class == $c && $namespace == $actNamespace)
                    {
                        $return = true;
                    }
                }
            }
        }
        return $return;
    }

    /**
     * agrega un extencion de archivo
     * @param array|string $ext
     */
    public static function PushExt($ext)
    {
        if (!is_array($ext))
        {
            array_push(self::$ext, $ext);
        } else
        {
            self::$ext = array_merge(self::$ext, $ext);
        }
    }

    private function GetType($file)
    {
        $fic = explode('.', $file);
        return strtolower(array_pop($fic));
    }

    private function DirSearch($dir, $avance, $recursive = true)
    {
        if ($this->GetFileName() != '')
            return [];
        $directorios = array();
        $carpetas = array();
        $direct = dir($dir);
        array_push($directorios, $dir);
        array_push($this->directorios, $dir);
        while ($fichero = $direct->read())
        {
            if ($fichero != '.' && $fichero != '..')
            {
                $directorio = ($dir == '.') ? '' : $dir;
                $ext = '';
                if (is_file($directorio . $fichero))
                {
                    $ext = $this->GetType($fichero);
                    if (in_array($ext, self::$ext) && $avance)
                    {
                        array_push($directorios, $directorio . $fichero);
                        array_push($this->directorios, $directorio . $fichero);
                        if ($this->FileSearch($directorio . $fichero))
                        {
                            $this->filename = $directorio . $fichero;
                            return $directorios;
                        }
                    }
                } elseif ($recursive)
                {
                    if ($this->NombClassFileExists($directorio . $fichero . DIRECTORY_SEPARATOR))
                    {
                        array_push($this->directorios, $this->filename);
                        array_push($directorios, $this->filename);

                        if ($avance)
                        {
                            if ($this->FileSearch($this->GetFileName()) == true)
                            {
                                return $directorios;
                            } else
                            {
                                $this->filename = false;
                            }
                        } else
                        {
                            return $directorios;
                        }
                    }
                    array_push($carpetas, $directorio . $fichero . DIRECTORY_SEPARATOR);
                }
            }
        }
        $direct->close();
        if (!$recursive)
        {
            return $directorio;
        }
        foreach ($carpetas as $carpeta)
        {
            array_push($directorios, $this->DirSearch($carpeta, $avance));
            if ($this->GetFileName())
            {
                return $directorios;
            }
        }
        return $directorios;
    }

}

/**
 * Excepcion de SearchClass
 * @package Cc
 * @subpackage SearchClass
 */
class SearchClassException extends \Exception
{
    
}
