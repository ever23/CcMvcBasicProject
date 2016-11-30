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
 *
 *  
 */

namespace Cc;

use Cc\Mvc\ErrorHandle;
use Cc\Mvc\Config;
use Cc\Autoload\SearchClass;
use Cc\Autoload\CoreClass;
use Cc\Mvc\Autenticate;
use Cc\Mvc\DocumentBuffer;
use Cc\Mvc\SESSION;
use Cc\Mvc\Controllers;
use Cc\Mvc\ViewController;
use Cc\Mvc\Request;
use Cc\Mvc\ResponseConten;
use Cc\Mvc\SelectorControllers;
use Cc\Mvc\Router;
use Cc\Mvc\SQLi;
use Cc\Mvc\MvcEvents;
use Cc\Mvc\ReRouterMethod;

//use Cc\OpCache;

include_once __DIR__ . '/../Cc/Autoload/CoreClass.php';

/**
 * Cc\Mvc                                                                
 * CLASE PRINCIPAL DE LA RECUBIERTA MVC  PARA LA APLICACION 
 * DONDE SE EJECUTARA TODA LA APLICACION
 *                     
 *                                                                                                                                   
 * @autor: ENYREBER FRANCO                                                      
 * @email: <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @copyright © 2015-2016, Enyerber Franco, Todos Los Derechos Reservados 
 * @package CcMvc    
 * @uses CoreClass.php se usa para cargar automaticamente las clases de core 
 * @uses Mvc\Router enruta las peticiones 
 * @uses Mvc\ResponseConten para el Objeto de respuesta 
 * @uses Mvc\Autenticate PARA AUTENTICAR USUARIOS 
 * @uses DependenceInyector para proporcionar las dependencias para los controladores y clase autenticadora 
 * @uses Mvc\Config para cargar la configuracion
 * @uses Mvc\SelectorControllers para ejecutar los controladores 
 * @uses iDataBase para el manejo de bases de datos
 * @uses Mvc\ErrorHandle para la captura de errores y excepciones 
 */
class Mvc
{

    /**
     * Directivas apache  para el redireccionamiento de todas las peticiones hacia el archivo de ejecucion 
     */
    const Htaccess = "RewriteEngine on\nRewriteCond %{REQUEST_URI} !\.(php|inc)$\nRewriteRule . ";

    /**
     * 
     */
    const Version = '1.0';

    /**
     *
     * @var string NOMBRE DE LA APLICACION 
     */
    public $Name = 'CcMvc Web App';

    /**
     * instancia del  objeto Enrutador
     * @var Router 
     */
    public $Router;

    /**
     * instancia del  objeto de respuesta 
     * @var ResponseConten 
     */
    public $Response = NULL;

    /**
     * Instancia del objeto de autenticacion
     * @var Autenticate
     */
    public $Session;

    /**
     *  EL TIPO DE CONTENIDO QUE SE ENVIARA 
     * @var string 
     */
    public $Content_type;

    /**
     * EL TIPO DE CONTENIDO QUE SE ENVIARA 
     * @var string 
     */
    private $ContentTypeOrig;

    /**
     * Instancia del objeto Request
     * @var Request 
     */
    public $Request = NULL;

    /**
     * instancia del controlador de views 
     * @var ViewController 
     * 
     */
    private $View = '';

    /**
     * directorio de procedimientos
     * @var string 
     */
    private $procedures;

    /**
     * directorios de controladores
     * @var string 
     */
    private $Controllers = '';

    /**
     *
     * @var this 
     */
    private static $Instance = NULL;

    /**
     *
     * @var iDataBase 
     */
    private $DataBase = NULL;

    /**
     *
     * @var Config 
     */
    private $conf;

    /**
     * <code>
     * array(
     *       'controller' => string,   // controllador 
     *       'method' => string,       // metodo 
     *       'paquete' => string,      // paquete 
     *       'extencion' => string     // extencion  
     * );
     * </code>
     * @var array 
     */
    private $page = array();

    /**
     *
     * @var string 
     */
    private static $ExecuteFile = NULL;

    /**
     * INDICA SI EL OBJETO ES EL ENRUTADOR O NO 
     * @var boolean 
     */
    public $isRouter = false;

    /**
     *
     * @var Controllers 
     */
    private $ObjController;

    /**
     *
     * @var type 
     */
    private $stdErr;

    /**
     * Objeto utilizado para ejecutar los controladores
     * @var SelectorControllers 
     */
    public $SelectorController;

    /**
     * objeto utilizado para inyectar dependencias
     * @var DependenceInyector 
     */
    public $DependenceInyector;

    /**
     *
     * @var Mvc\LayautManager 
     */
    public $LayautManager = NULL;

    /**
     *
     * @var AutoloadExternLib 
     */
    public $AutoloaderLib;
    private $id = NULL;
    private $time = NULL;
    private $CacheCore = [];
    private $CacheRouter = ['expire' => NULL, 'request' => '', 'requestStatic' => ''];
    private $InternalSession;

    /**
     *
     * @var DocumentBuffer 
     */
    public $Buffer;

    /**
     * indica si el contenido sera procesado
     * @var bool 
     */
    public $ProcessConten = true;

    /**
     *
     * @var bool 
     */
    private $fin = false;

    use CoreClass
    {
        autoloadCore as private Autoload;
    }

    /**
     * @access private
     * CONSTRUCTOR DE LA CLASE
     * @param string $conf nombre del archivo de configuracion
     */
    public function __construct($conf)
    {

        if (!isset($_SERVER['REQUEST_URI']))
        {
            $_SERVER['REQUEST_URI'] = '';
        }
        $defaultConf = __DIR__ . '/config/DefaultConfig.php';
        if (is_null($conf))
        {
            $conf = $defaultConf;
        }
        self::$Instance = &$this;
        $this->StartAutoloadCore(realpath(dirname(__FILE__) . '/../'));

//$this->CoreClass['Cc\\Config'] = 'Cc/Config/Config.php';
        $this->conf = new Config($defaultConf);

        $this->conf->Load($conf);

        MvcEvents::Start($this->conf);
        $this->View = new ViewController($this->conf['App']['view']);
        MvcEvents::$View = &$this->View;
        $this->Debung();
        $this->Log(" Archivo de configuracion :" . $conf . " cargado...");

        $this->procedures = $this->conf['App']['procedimientos'];

        $this->Controllers = $this->conf['App']['controllers'];
        if (!is_dir($this->conf['App']['controllers']))
        {
            throw new Exception("EL DIRECTORIO DE CONTROLADORES (" . $this->conf['App']['controllers'] . ") NO EXISTE");
        }
        if (!is_dir($this->conf['App']['model']))
        {
            throw new Exception("EL DIRECTORIO DE MODELOS (" . $this->conf['App']['model'] . ") NO EXISTE");
        } if (!is_dir($this->conf['App']['extern']))
        {
            throw new Exception("EL DIRECTORIO DE LIBRERIAS EXTERNAS (" . $this->conf['App']['extern'] . ") NO EXISTE");
        }
        Cache::Start($this->conf);

        $this->Log("Abriendo Cache ...");
        if (!Cache::IsSave('AppPath') || Cache::Get('AppPath') != $this->conf->App['app'])
        {
            Cache::Clean();
            Cache::Set('AppPath', $this->conf->App['app']);
            $this->Log(" Cache Reiniciado ...");
        }
        if (Cache::IsSave('AutoloadCore'))
        {
            $this->CacheCore = Cache::Get('AutoloadCore');
        }
        $this->LoadProcedures();
        $prot = !empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS']) ? 'https' : 'http';
        if ($prot != $this->conf['Router']['protocol'])
        {
            header("Location: " . $this->conf['Router']['protocol'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        }


        if (is_null(self::$ExecuteFile))
        {
            $bak = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $t = array_pop($bak);
            self::$ExecuteFile = $t['file'];
            $this->isRouter = true;
        }
        if (is_null($this->conf['Router']['DocumentRoot']))
        {
            $ExecuteFile = str_replace(DIRECTORY_SEPARATOR, "/", dirname(self::$ExecuteFile));
            $match = '/' . preg_quote(str_replace(DIRECTORY_SEPARATOR, "/", $_SERVER["DOCUMENT_ROOT"]), '/') . '/';
            $conf = $this->conf['Router'];
            $conf['DocumentRoot'] = preg_replace($match, '', $ExecuteFile);

            if ($conf['DocumentRoot'] != '/')
            {
                $conf['DocumentRoot'].='/';
            }
            if ($conf['DocumentRoot'][0] != '/')
                $conf['DocumentRoot'] = '/' . $conf['DocumentRoot'];
            $this->conf['Router'] = $conf;
        }
        if (!file_exists(realpath('.') . '/.htaccess'))
        {
            $file = basename(self::$ExecuteFile);
            $f = fopen(dirname(self::$ExecuteFile) . '/.htaccess', 'w+');
            fwrite($f, self::Htaccess . $file);
            fclose($f);
        }
        $this->Log('Cofigurando Dependencias ....');

        $this->Router = new Router($this->conf['Router']);
        $this->CacheRouter['request'] = strtolower($this->Router->GetRequestFile());
        $this->CacheRouter['requestStatic'] = $this->CacheRouter['request'];
        if (count($_GET) > 0)
        {
            $this->CacheRouter['requestStatic'] .= '?' . http_build_query($_GET);
        }
        if ($this->conf['Router']['GetControllerFormat'] == Router::Get)
        {
// $this->CacheRouter['expire'] = '+1 day';
            $get = isset($_GET[$this->conf['Router']['GetControllers']]) ? '?' . $this->conf['Router']['GetControllers'] . '=' . $_GET[$this->conf['Router']['GetControllers']] : '';
            $this->CacheRouter['request'] = strtolower($this->Router->GetRequestFile() . $get);
        }//requestStatic
        $this->Log('Seleccionando la clase de Respuesta ....');

        $this->Content_type = $this->SelectResponseConten();


        MvcEvents::$Layaut = &$this->LayautManager;
        $this->DependenceInyector = new DependenceInyector();
        $this->DependenceInyector->AddDependenceInstanciable($this->conf['Controllers']['Dependencias']);
        $this->DependenceInyector->AddDependence("{Response}", $this->Response);
        $this->DependenceInyector->AddDependence("{DB}", $this->DataBase);
        $this->DependenceInyector->AddDependence("{Autenticate}", $this->Session);
        $this->DependenceInyector->AddDependence("{config}", $this->conf);
        $this->DependenceInyector->AddDependence("{Router}", $this->Router);
        $this->DependenceInyector->AddDependence("{Request}", $this->Request);
        $this->DependenceInyector->AddDependence("{SelectorControllers}", $this->SelectorController);

        $confAutoload = $this->conf->AutoloadLibs;
        $libs = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR;
        $vendor = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
        if (is_bool($confAutoload['UseStandarAutoloader']))
        {
            $confAutoload['UseStandarAutoloader'] = [];
            $confAutoload['UseStandarAutoloader'][] = [$libs, true];
            if ($this->conf->AutoloadLibs['UseStandarAutoloader'])
            {
                $confAutoload['UseStandarAutoloader'][] = $this->conf->App['extern'];
            }

            $confAutoload['UseStandarAutoloader'][] = [$this->conf->App['model'], true];
        } else
        {
            $confAutoload['UseStandarAutoloader'][] = [$libs, true];
            $confAutoload['UseStandarAutoloader'][] = [$this->conf->App['model'], true];
        }

        $this->AutoloaderLib = new AutoloadExternLib($confAutoload, $this->conf->App['extern'], $this->IsDebung());
        $this->AutoloaderLib->StartAutoloader();
        if (file_exists($vendor))
        {
            $this->AutoloaderLib->AddAutoloader($vendor);
        }
    }

    /**
     * returna una referencia al objeto se session interna 
     * @return Mvc\InternalSession
     */
    public function &GetInternalSession()
    {
        return $this->InternalSession;
    }

    /**
     * @access private
     * @return \Cc\Mvc
     */
    public function __debugInfo()
    {
        return [];
    }

    /**
     * Retorna el nombre del cache para el enrrutamiento
     * @return string
     */
    public function GetNameCacheRouter()
    {
        return $this->CacheRouter['request'];
    }

    /**
     * Retorna el nombre del cache para el enrrutamiento estatico
     * @return string
     */
    public function GetNameStaticCacheRouter()
    {
        return $this->CacheRouter['requestStatic'];
    }

    /**
     * indica si la aplicacion se encuentra en modo de depuracion 
     * @return bool
     */
    public function IsDebung()
    {
        return !isset($this->conf['debung'][0]);
    }

    /**
     * procesa la configuracion para determinar si esta en modo de depuracion o de produccion 
     */
    private function Debung()
    {
        $this->t = microtime(true);
        if (is_bool($this->conf['debung']))
        {
            if (!$this->conf['debung'])
            {
                $this->conf['debung'] = [false, 'ModoExeption' => 0, 'error_reporting' => 0, 'NoReenviarFiles' => false, 'UseErrorResponseCode' => true];
            } else
            {
                $this->conf['debung'] = $this->conf->default['debung'];
                $this->stdErr = fopen($this->conf['debung']['file'], 'a+');
                $this->id = rand(10000, 90000);
                $this->time = 0;

                register_tick_function([$this, 'tick']);
            }
        } else
        {
            $this->stdErr = fopen($this->conf['debung']['file'], 'a+');
            $this->id = rand(10000, 90000);
            $this->time = 0;

            register_tick_function([$this, 'tick']);
        }
        CcException::SetMode($this->conf['debung']['ModoExeption']);
        error_reporting($this->conf['debung']['error_reporting']);

        ErrorHandle::$debung = $this->conf['debung'];
        ErrorHandle::SetHandle();
    }

    /**
     * @internal callback para spl_autoload_register
     * @param string $class
     * @return boolean
     */
    public function autoloadCore($class)
    {
        if ($this->Autoload($class))
        {
            return true;
        } else if (isset($this->CacheCore[$class]) && file_exists($this->CacheCore[$class]))
        {
            include_once( $this->CacheCore[$class]);
            if (class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * RETORNA EL NOMBRE DEL ARCHIVO EN QUE SE EJECUTA LA APLICACION 
     * @return string
     */
    public function GetExecutedFile()
    {
        return self::$ExecuteFile;
    }

    /**
     * @internal solo deberia ser ejecutado automaticamente 
     */
    public function __destruct()
    {
        if ($this->fin === false)
        {
            self::EndApp(true);
        }
        if (count(SearchClass::$classes) > 0)
            Cache::Set('AutoloadCore', SearchClass::$classes + $this->CacheCore + $this->AutoloaderLib->GetLastLoadFiles());
        Cache::Save();
// $this->Log(Cache::GetObjectCache());

        $this->Log("Fin de la Aplicacion ...", true);
        unset($this->Response);
        unset($this->Controllers);
        unset($this->DataBase);
        unset($this->conf);
        unset($this->Session);
        unset($this->page);
        unset($this->CoreClass);
        unset($this->Sitio);
        unset($this->procedures);
    }

    /**
     * OBTINENE UN ARRAY TIPO CLAVE=>VALOR QUE CONTIENE EN EL INDICE paquete EL NOMBRE DEL PAQUETE ,EN EL INDICE controller EL NOMBRE DEL CONTROLADOR QUE SE EJECUTARA,
     * EN EL INDICE method EL NOMBRE DEL METODO DEL CONTROLADOR QUE SE EJECUTARA
     *  @return array
     */
    public static function GetController()
    {
        return static::App()->page;
    }

    /** 		
     *  REALIZA LAS OPERACIONDES QUE FINALIZAN LA EJECUCION DE LA APLICACION
     * @uses exit()
     */
    public static function EndApp($contex = false)
    {

        if (!self::App())
        {
            return;
        }

        self::App()->Log("Finalizando la Aplicacion ...");
        if (self::App()->Response instanceof ResponseConten)
        {
            $pattern = "/Content-type:/i";
            $header = preg_grep($pattern, headers_list());
            $preg = array_pop($header);

            if (!empty($preg))
            {
                $ex = explode(";", trim(preg_replace($pattern, "", $preg)));
                if (strtoupper($ex[0]) != strtoupper(self::App()->ContentTypeOrig) && strtoupper($ex[0]) != strtoupper(self::App()->Content_type))
                {
                    self::App()->ProcessConten = false;
                    self::App()->Log("Cancelando el procesamineto de respuesta ...");
                }
            }
            if (http_response_code() == 304)
            {
                self::App()->ProcessConten = false;
                self::App()->fin = true;
                self::App()->Log("No se envia El navegador tiene el archivo en cache  ...");
            }
            self::App()->Log("Enviando Contenido de Respuesta ...");
            if (self::App()->ProcessConten && !self::App()->fin)
            {

                self::App()->LoadLayaut();
            }
            MvcEvents::TingerAndDependence('OnEndApp');
        }
// var_dump(Mvc::App()->ProcessConten);
        self::App()->fin = true;

//  var_dump(ob_list_handlers());

        self::App()->Buffer->EndConten();

        if (self::App()->conf['debung'])
        {
            foreach (headers_list() as $head)
            {
                self::App()->Log($head);
            }
        }
        if (!$contex)
        {
            self::$Instance = NULL;
            exit;
        }
    }

    /**
     * carga un view de error establecido el cual puede se 403, 404, 500
     * @param int $num
     * @param string $msj
     * @uses ViewController::LoadInternalView() 
     * @uses Error401.php SE CARGA CUANDO OCURRE UN ERROR DE TIPO 401
     * @uses Error403.php SE CARGA CUANDO OCURRE UN ERROR DE TIPO 403
     * @uses Error404.php SE CARGA CUANDO OCURRE UN ERROR DE TIPO 404
     * @uses Error500.php SE CARGA CUANDO OCURRE UN ERROR DE TIPO 500
     */
    public function LoadError($num, $msj)
    {
        if (!($this->Buffer instanceof DocumentBuffer))
        {
            $this->Buffer = new DocumentBuffer([$this, 'Handle'], true);
        }
        if (!($this->Response instanceof ResponseConten))
        {
            $this->ContentTypeOrig = $this->Content_type = 'text/html';
            $this->IntanceResponseConten();
            $this->Buffer->SetCompres(false);
        }
        if (!($this->View instanceof ViewController))
        {
            $this->View = new ViewController(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'view');
        }

        $this->View->config = $this->conf;
        $this->View->error = '';
        if ($this->IsDebung())
            $this->View->error = $msj;
        $this->Log($msj);
        if ($this->conf->debung['UseErrorResponseCode'])
            http_response_code($num);
        if ($this->IsDebung())
            error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
        MvcEvents::Tinger('Error' . $num, $this->View->error);
    }

    /**
     *  REDIRECCIONA LA PAGINA
     *  @param string $page EL NOMBRE DE LA CLASE Y METODO DONDE SE REDIRECCIONARA ESTE DEVE CUMPLIR CON LA SINTAXIS ESTABLECIDA PARA LA NAVEGACION DE CONTROLADORES
     *  EN EL DOCUMENTO DE CONFIGURACION
     *  @param array $get VARIABLES QUE SERAN ENVIADAS MEDIANTE GET
     */
    public static function Redirec($page, array $get = array())
    {

        $conf = self::Config();
        if ($conf['Router']['GetControllerFormat'] == Router::Get && !empty($_SERVER['REQUEST_URI']) && $_SERVER['SCRIPT_NAME'] == substr($_SERVER['REQUEST_URI'], 0, strlen($_SERVER['SCRIPT_NAME'])))
        {
            $doc = $_SERVER['PHP_SELF'];
        } else
        {
            $doc = $conf['Router']['DocumentRoot'];
        }
        if (self::App()->Response instanceof ResponseConten)
            self::App()->ProcessConten = false;
        if (filter_var($page, FILTER_VALIDATE_URL))
        {
            $redirec = $page;
        } else
        {
            $redirec = Router::Href($page, $get, $doc);
        }

        self::App()->Log("Redireccionando a " . $redirec);
        header("Location: " . $redirec);
        exit;
    }

    /**
     * ESTE METODO RETORNA EL OBJETO DE CONFIGURACION
     * @return Config
     */
    public static function &Config()
    {
        return self::App()->conf;
    }

    /**
     * FILTRA LAS VARIABLES PROVENIENTES DEL CLIENTE PARA EVITAR ATAQUES XSS
     */
    private function FilterXss()
    {
        foreach ($this->conf['VarAceptXss'] as $i => $v)
        {
            FilterXss::SetFilterXssExeption($i, $v);
        }
        if (!empty($this->conf['Autenticate']['SessionName']))
            FilterXss::SetFilterXssExeption(FilterXss::FilterXssCookie, $this->conf['Autenticate']['SessionName']);
        FilterXss::SetFilterXssExeption(FilterXss::FilterXssGet, $this->conf['Router']['GetControllers']);
        FilterXss::Filter(FilterXss::FilterXssCookie);
    }

    /**
     * METODO DONDE SE EJECUTARA LA CLASE DE  AUNTENTIFICACION
     */
    private function Auth()
    {
        $conf = self::Config();
        ErrorHandle::SetHandle(-5);
        $session = false;
        $this->InternalSession = new Mvc\InternalSession();
        if (!empty($conf['Autenticate']['class']) && !empty($conf['Autenticate']['SessionName']))
        {

            $session = $conf['Autenticate']['SessionCookie'];
            $class_name = $conf['Autenticate']['class'];
            if (!class_exists($class_name, true))
            {
                throw new Exception("LA CLASE " . $class_name . " MANEJADORA DE AUTENTIFICACION NO EXISTE ");
            }
            $this->Session = new $class_name(...$conf['Autenticate']['param']);
        } else
        {
            $this->Session = [];
        }
        $this->InternalSession->SetName($conf['Autenticate']['SessionName']);
        //$this->Session->SetName($conf['Autenticate']['SessionName']);
        if ($session)
        {
            $session['path'] = !is_null($session['path']) ? $session['path'] : $this->conf['Router']['DocumentRoot'];
            $this->InternalSession->SetCookie($session['cahe'], $session['time'], $session['path'], $session['dominio'], $conf['Router']['protocol'] == 'https', $session['httponly']);
        }

        $this->InternalSession->Start();
        if ($this->Session instanceof Autenticate)
        {
            $this->Session->SetDependenceInyector($this->DependenceInyector);

            $this->Session->Start($this->InternalSession);
            $this->Log("Autenticando....");
            $this->Session->Auth();
        }
        ErrorHandle::RecoverHandle();
        ErrorHandle::SetHandle();
        $this->Log("Autenticacion finalizada....");
    }

    /**
     *  RETONNA LA  INSTANCIA DEL APP QUE SE ESTA EJECUTANDO
     *  @return Mvc
     */
    public static function &App()
    {
        return self::$Instance;
    }

    /**
     * Inicia la ejecucion de la aplicacion 
     */
    public function Run()
    {
        if (ob_get_level() > 0 && ob_get_contents())
        {
            trigger_error("ASEGURESE QUE NO ESTA IMPRIMIENDO NUNGUN CARACTER ANTES DE EJECUTAR LA APLICACION ", E_USER_ERROR);
        }
        header_remove("X-Powered-By");
        header("X-Powered-By: PHP/" . PHP_VERSION . " + " . static::class . "/" . static::Version);
        $this->Log("Ejecutando App....");
        $this->Buffer = new DocumentBuffer([$this, 'Handle'], true);


        $this->LayautManager = Mvc\LayautManager::BeginConten(null);
        $this->LayautManager->Obj = &$this->Response;
        if (!$this->RouterByCache())
        {

            $this->Router();
        }

        $this->LoadController();

        $this->RouterExt();
        $this->SecurityRequest();
        $this->ConetDataBase();


        $this->Auth();
        $this->ExecuteController();
        self::EndApp();
    }

    /**
     * ENRUTA BASADO EN LA INFORMACION ALMACENADA EN CACHE 
     * @return boolean
     */
    private function RouterByCache()
    {
// return false;
        $cookie = '';
        if (isset($_COOKIE['GDmaxW']))
        {
            $cookie = 'COOKIE';
            $cookie.= $_COOKIE['GDmaxW'];
        }

        $cache = Cache::IsSave($this->CacheRouter['request']) ? Cache::Get($this->CacheRouter['request']) : (Cache::IsSave($this->CacheRouter['requestStatic']) ? Cache::Get($this->CacheRouter['requestStatic']) : []);
        $nameCache = $this->CacheRouter['request'];

        if (!isset($cache['type']))
        {

            return false;
        }
        switch ($cache['type'])
        {
            case 'file':

                $this->IntanceResponseConten();
                if (Cache::IsSave($this->CacheRouter['requestStatic']))
                {
                    $cache2 = Cache::Get($this->CacheRouter['requestStatic']);
                    $nameCache = $this->CacheRouter['requestStatic'];
                    $cache = Cache::IsSave($this->CacheRouter['requestStatic'] . $cookie) ? Cache::Get($this->CacheRouter['requestStatic'] . $cookie) : $cache;
                }
                if (isset($cache['RealFile']))
                {

                    $realfile = new \SplFileInfo($cache['RealFile']);
                    $file = new \SplFileInfo($cache['Controller']);

                    if (file_exists($realfile) && $file->getMTime() <= $realfile->getMTime())
                    {
                        $this->Log("Enrutado a  " . $realfile . " desde el cache");
                        $this->Router->InfoFile = $realfile;
                        $this->ProcessConten = false;
                        $this->Buffer->SetAutoMin(false);
                        if ($this->Response instanceof Mvc\Response)
                            $this->Response->SetMin(false);
                        $this->Router->RouterFile($realfile);
                        exit;
                    } else
                    {
                        Cache::Delete($nameCache);
                    }
                }

                $this->Router->InfoFile = new \SplFileInfo($cache['Controller']);
                $this->Log("Enrutado a  " . $this->Router->InfoFile . " desde el cache");
                $this->Router->RouterFile($this->Router->InfoFile);


                exit;
                break;
            case 'Controllers':
                return $this->RouteControllerCache($cache);
            default:
                return false;
        }
        return false;
    }

    /**
     * ENRUTA UN CONTROLADOR BASADO EN LA INFORMACION ALMACENADA EN CACHE 
     * @param array $cache
     * @return boolean
     */
    private function RouteControllerCache($cache)
    {
        $this->page = $cache['Controller'];
        if ($this->Content_type == '*/*')
        {
            $this->Content_type = 'text/html';
            $this->ContentTypeOrig = "text/html";
        }
        if (isset($this->page['extencion']) && !is_null($this->page['extencion']))
        {
            $this->Content_type = $this->conf['Response']['ExtencionContenType'][$this->page['extencion']];
        }



        if (Cache::IsSave($this->CacheRouter['requestStatic']))
        {
            $nameCache = $this->CacheRouter['requestStatic'];
            $cache2 = Cache::Get($this->CacheRouter['requestStatic']);
        } else
        {
            $cache2 = $cache;
        }

        if (isset($cache2['RealFile']))
        {

            $realfile = new \SplFileInfo($cache2['RealFile']);
            $time = new \DateTime();
// $age = $realfile->getMTime() - $time->getTimestamp();
            if (file_exists($realfile))
            {
                if (!$_POST && ( $time->getTimestamp() - $realfile->getMTime() ) < $cache2['LifeTime'])
                {
                    $this->Log("Enrutado a  " . $realfile . " desde el cache");

                    if (isset($cache2['Content-Type']))
                    {
                        $this->ContentTypeOrig = $cache2['Content-Type'];
                        $this->Content_type = $cache2['Content-Type'];
                        header("Content-type: " . $cache2['Content-Type']);
                    }
                    $this->IntanceResponseConten();
                    $this->ProcessConten = false;
                    if (isset($cache2['CacheClient']))
                    {
                        if (!Router::HeadersReponseFiles($cache2['MTime'] ? $cache2['MTime'] : new \SplFileInfo($realfile), Mvc::App()->Content_type, $cache2['CacheClient']))
                        {
                            exit;
                        }
                    }
//echo ( $time->getTimestamp() - $realfile->getMTime()), ':', $cache2['LifeTime'];
// echo "<!--Response by cache MaxAge:" . $cache2['LifeTime'] . " Age:" . ( $time->getTimestamp() - $realfile->getMTime() ) . " s -->\n";

                    readfile($realfile);
                    exit;
                } else
                {
                    Cache::Delete($this->CacheRouter['requestStatic']);
                    unlink($realfile);
                }
            } else
            {
                Cache::Delete($this->CacheRouter['requestStatic']);
            }
        }
        $this->IntanceResponseConten();


        if (isset($this->page['routeVars']))
        {
            $this->DependenceInyector->SetDependenceForParamArray($this->page['routeVars']);
        }
        $this->Log("Enrutado a " . (is_null($this->page['paquete']) ? '' : 'paquete: ' . $this->page['paquete'] . ',') . ' '
                . 'Controlador: ' . $this->page['controller'] . ', Metodo: ' . $this->page['method'] . " desde el cache");
        return true;
    }

    /**
     * ENRUTA LAS PETICIONES DEL CLIENTE
     */
    private function Router()
    {

        Cache::Delete($this->CacheRouter['request']);
        Cache::Delete($this->CacheRouter['requestStatic']);
        $cache = [];

        $this->Log("Enrutando ....");
        if ($this->isRouter && $this->Router->IsEnrutableFile() && $this->Router->InfoFile->getPathname() != $this->GetExecutedFile())
        {

            $UserAppDir = $this->conf['App']['app'];
            $this->Log("Enrutado a  " . $this->Router->InfoFile->getPathname());
// if ($this->AppDir == substr($this->Router->InfoFile->getPathname(), 0, strlen($this->AppDir)) || $UserAppDir == substr($this->Router->InfoFile->getPathname(), 0, strlen($UserAppDir)))
            $sujeto = $this->Router->InfoFile->getPathname();
            if (preg_match('/^(' . preg_quote($this->AppDir . DIRECTORY_SEPARATOR, '/') . ')/', $sujeto) || preg_match('/^(' . preg_quote($UserAppDir, '/') . ')/', $sujeto))
            {
                $this->LoadError(403, 'EL SISTEMA PROHIBIO EL ACCESO A ESTE ARCHIVO');
                exit;
            } else
            {
                $cache['type'] = 'file';
                $cache['Controller'] = $this->Router->InfoFile->__toString();

                Cache::Set($this->CacheRouter['request'], $cache, $this->CacheRouter['expire']);
                $this->IntanceResponseConten();
                $this->Router->RouterFile();
                exit;
            }
        } elseif ($this->Router->IsNoPath())
        {
            $this->Log("Ruta " . $this->Router->GetPath() . " no existe");
            $this->LoadError(404, 'LA RUTA NO EXISTE');

            exit;
        } else
        {

            $this->Router->InfoFile = NULL;
            if ($this->Content_type == '*/*')
            {
                $this->Content_type = $this->ContentTypeOrig = 'text/html';
            }
            $this->IntanceResponseConten();
            $this->page = $this->Router->GetController();

            $this->Log("Enrutado a " . (is_null($this->page['paquete']) ? '' : 'paquete: ' . $this->page['paquete'] . ',') . ' '
                    . 'Controlador: ' . $this->page['controller'] . ', Metodo: ' . $this->page['method']);
        }
    }

    /**
     * 
     */
    private function RouterExt()
    {
        $accept = [];

        if (Controllers::GetReflectionClass()->implementsInterface(Mvc\ExtByController::class))
        {
            $class = Controllers::GetReflectionClass()->name;
            $ext = $class::ExtAccept();

            $method = $this->page['method'];
            if (isset($this->page['orig']) && !Controllers::GetReflectionClass()->hasMethod($this->page['method']))
            {
                if (!Controllers::GetReflectionClass()->hasMethod($this->page['method']))
                {
                    $method = $this->page['orig'];
                }
            }
            if (Controllers::GetReflectionClass()->implementsInterface(ReRouterMethod::class))
            {
                if (!Controllers::GetReflectionClass()->hasMethod($this->page['method']))
                {
                    $method = '__routermethod';
                }
            }


            if (isset($ext['accept']) && is_array($ext['accept']) && isset($ext['accept'][$method]))
            {
                if (is_array($ext['accept'][$method]))
                {
                    foreach ($ext['accept'][$method] as $v)
                    {
                        $accept[] = $v;
                    }
                } else
                {
                    $accept[] = $ext['accept'][$method];
                }
            }
            if (isset($ext['accept']) && is_array($ext['accept']) && isset($ext['accept']['*']))
            {
                if (is_array($ext['accept']['*']))
                {
                    foreach ($ext['accept']['*'] as $v)
                    {
                        $accept[] = $v;
                    }
                } else
                {
                    $accept[] = $ext['accept']['*'];
                }
            }
            if (isset($ext['require']) && is_array($ext['require']) && isset($ext['require'][$method]))
            {
                if ((is_array($ext['require'][$method]) && !in_array($this->page['extencion'], $ext['require'][$method])) || (is_string($ext['require'][$method]) && $this->page['extencion'] != $ext['require'][$method]))
                {
                    $this->LoadError(404, $this->Router->RouterError('El controlador nego expresamente la extencion que sea  .' . $ext['require'][$method]));
                    exit;
                } else
                {
                    $accept[] = $this->page['extencion'];
                }
            }
            if (isset($ext['require']) && is_array($ext['require']) && isset($ext['require']['*']))
            {
                if ((is_array($ext['require']['*']) && !in_array($this->page['extencion'], $ext['require']['*'])) || (is_string($ext['require']['*']) && $this->page['extencion'] != $ext['require']['*']))
                {
                    $this->LoadError(404, $this->Router->RouterError('El controlador nego expresamente la extencion requiere que sea  .' . $ext['require']['*']));
                    exit;
                } else
                {
                    $accept[] = $this->page['extencion'];
                }
            }
        }

        if ($error = $this->Router->ValidateExt($this->page['extencion'], $accept))
        {
            $this->LoadError(404, $error);
            exit;
        }
    }

    /**
     * RETORNA UNA REFERENCIA A EL OBJETO MANEJADOR DE BASES DE DATOS 
     * @return iDataBase
     * @throws Exception si el objeto no es una instancia de iDataBase o es NULL
     */
    public function &DataBase()
    {
        if ($this->DataBase instanceof iDataBase)
        {
            return $this->DataBase;
        } else
        {
            throw new Exception("OCURRIO UN ERROR EL OBJETO MANEJADOR DE BASES DE DATOS ES INVALIDO ");
        }
    }

    /**
     * CARGA LOS PROCEDIMIENTOS 
     */
    private function LoadProcedures()
    {
        if (!is_dir($this->procedures))
        {
            return false;
        }

        $proc = dir($this->procedures);
        while ($f = $proc->read())
        {
            $file = $this->procedures . $f;
            if (is_file($file))
            {

                include_once($file);
            }
        }
    }

    /**
     * resuelve y cargar el controlador 
     */
    private function LoadController()
    {
        Controllers::$View = &$this->View;
        Controllers::$Layaut = &$this->LayautManager;
        $this->Log("Cargando y Resolviendo controlador ...");

        $this->SelectorController = new SelectorControllers($this->Controllers, $this->conf, $this->DependenceInyector);
        if (($controller = $this->Router->GetRoute($this->page)) == false)
        {
            if (($controller = $this->SelectorController->CreateController($this->page['controller'], $this->page['paquete'], $this->page['method'], true)) !== false)
            {
                list($paquete, $class, $method) = $controller;

                if ($this->page['paquete'] != $paquete || $this->page['controller'] != $class || $this->page['method'] != $method)
                {
                    list($this->page['paquete'], $this->page['controller'], $this->page['method']) = $controller;
                    Cache::Set($this->CacheRouter['request'], ['type' => 'Controllers', 'Controller' => $this->page], $this->CacheRouter['expire']);
                }

                $this->Log("Controlador  Elegido " . $this->SelectorController->GetReflectionController()->name . "::" . $this->page['method'] . "...");
            }
        } else
        {
            $this->SelectorController->CreateClousure(true);
        }
    }

    /**
     * 
     * @param array $aceptXss
     * @param array $xss
     * @return array
     */
    private function SecurityConf($aceptXss, $xss)
    {
        if (!isset($aceptXss['_POST']) || !is_array($aceptXss['_POST']))
        {
            $aceptXss['_POST'] = [];
        }
        if (isset($xss['_POST']))
        {
            foreach ($xss['_POST'] as $v)
            {
                $aceptXss['_POST'][] = $v;
            }
        }
        if (!isset($aceptXss['_GET']) || !is_array($aceptXss['_GET']))
        {
            $aceptXss['_GET'] = [];
        }
        if (isset($xss['_GET']))
        {
            foreach ($xss['_GET'] as $v)
            {
                $aceptXss['_GET'][] = $v;
            }
        }
        if (!isset($aceptXss['_COOKIE']) || !is_array($aceptXss['_COOKIE']))
        {
            $aceptXss['_COOKIE'] = [];
        }
        if (isset($xss['_COOKIE']))
        {
            foreach ($xss['_COOKIE'] as $v)
            {
                $aceptXss['_COOKIE'][] = $v;
            }
        }
        return $aceptXss;
    }

    /**
     * filtra las variables de entrada 
     */
    private function SecurityRequest()
    {

        if ($this->SelectorController->GetReflectionController()->implementsInterface(\Cc\Mvc\SecurityRequest::class))
        {

            $class = $this->SelectorController->GetReflectionController()->name;
            $this->Log("Cargando excepciones de xss de " . $class . "::XssAcept() y sqli " . $class . "::SQliAcept() ...");
            $this->conf->VarAceptXss = $this->SecurityConf($this->conf->VarAceptXss, $class::XssAcept());
            $this->conf->VarAceptSqlI = $this->SecurityConf($this->conf->VarAceptSqlI, $class::SQliAcept());
        }
        $this->Log("Filtrando Xss...");
        $this->FilterXss();
        $this->Request = new Request();
        if ($this->Request->method() == 'POST')
        {
            $this->DependenceInyector->SetDependenceForParamArray($this->Request->Post);
        }
        $this->DependenceInyector->SetDependenceForParamArray($this->Request->Get);
    }

    /**
     * carga las librerias externas del controlador 
     */
    private function LoadLibsExtern()
    {

        if ($this->SelectorController->GetReflectionController()->implementsInterface(Mvc\AutoloaderLibs::class))
        {

            $class = $this->SelectorController->GetReflectionController()->name;
            $this->Log("Cargando librerias externas con " . $class . "::LoadExternLib() ...");
            $method = $this->page['method'];
            if (isset($this->page['orig']) && !Controllers::GetReflectionClass()->hasMethod($this->page['method']))
            {
                if (!Controllers::GetReflectionClass()->hasMethod($this->page['method']))
                {
                    $method = $this->page['orig'];
                }
            }
            if ($this->SelectorController->GetReflectionController()->implementsInterface(ReRouterMethod::class))
            {
                if ($this->SelectorController->GetReflectionController()->hasMethod($this->page['method']))
                {
                    $method = '__routermethod';
                }
            }

            foreach ($class::LoadExternLib() as $i => $v)
            {
                if (is_string($i) && strtolower($i) == $method)
                {
                    if (is_array($v))
                    {
                        foreach ($v as $f)
                        {
                            $this->AutoloaderLib->AddAutoloader($f);
                        }
                    } else
                    {
                        $this->AutoloaderLib->AddAutoloader($v);
                    }
                } else
                {
                    $this->AutoloaderLib->AddAutoloader($v);
                }
            }
        }
    }

    /**
     * EJECUTA EL CONTROLADOR
     */
    private function ExecuteController()
    {
        $c = $this->conf['Controllers']['Prefijo'];
//echo get_class(self::$Response);

        ErrorHandle::SetHandle(-4);
        /* if(!http_response_code())
          exit; */
        $this->LoadLibsExtern();

        $this->Log("Ejecutando el constuctor del  Controlador " . $c . $this->page['controller'] . " ...");
        if (($clousure = $this->Router->GetRoute($this->page)) == false)
        {
            if ($this->SelectorController->InstanceController($this->page['method'], true))
            {
                $this->ObjController = &$this->SelectorController->GetController();

                $this->Log("Ejecutando el metodo " . $c . $this->page['controller'] . "->" . $this->page['method'] . " ...");
                $this->SelectorController->Call(NULL, TRUE);
                $this->Log("Controlador ejecutado con exito ...");
            }
        } else
        {
            $this->ObjController = &$this->SelectorController->GetController();
            $this->SelectorController->CallFunction($clousure);
        }

        ErrorHandle::RecoverHandle();
        ErrorHandle::SetHandle();
    }

    /**
     * SELECCIONA EL CONTEN-TYPE
     * @return string
     */
    private function SelectResponseConten()
    {
        $Conten = NULL;
        if (isset($_SERVER["HTTP_ACCEPT"]))
            foreach (explode(',', $_SERVER["HTTP_ACCEPT"]) as $C)
            {
                $C2 = explode(";", $C);
                $type1 = trim($C2[0]);
                $e = explode('/', $type1);
                $type2 = $e[0] . '/*';


                if ($type1 != '*/*')
                {
                    if (key_exists($type1, $this->conf['Response']['Accept']))
                    {
                        $Conten = $type1;
                        $this->ContentTypeOrig = $type1;
                        break;
                    } elseif (key_exists($type2, $this->conf['Response']['Accept']))
                    {
                        $Conten = $type2;
                        $this->ContentTypeOrig = $type1;
                        break;
                    }
                }
            }

        if (is_null($Conten))
        {
            $this->ContentTypeOrig = $Conten = "*/*";
        }
        $this->Log($_SERVER);
        return $Conten;
    }

    /**
     * CREA UNA INSTANCIA DEL OBJETO DE RESPUESTA SELECCIONADO 
     */
    private function IntanceResponseConten()
    {

        $conf = self::Config();
        $conten = $this->Content_type;
        $this->Log("Instanceando el Objeto Response ...");
        if (isset($conf['Response']['Accept'][$conten]))
        {
            $Response = $conf['Response']['Accept'][$conten];

            if (!class_exists($Response['class'], true))
            {
                throw new Exception("LA CLASE CLASE MEJADORA DE RESPUESTA " . $Response['class'] . " NO SE ENCOTRO");
            }
            $class = $Response['class'];



            $this->Response = new $class(...$Response['param']);

            $this->Content_type = $this->ContentTypeOrig;
        } else
        {
            $this->ResponseContenDefault($conten);
        }
        if (isset($Response['layaut']))
        {
            $this->Response->SetLayaut($Response['layaut'], $conf['App']['layauts']);
        }
    }

    /**
     * CAMBIA EL OBJETO RESPONSE 
     * @param string $conten_type mime-type
     * @param array $equal
     * @return boolean
     */
    public function ChangeResponseConten($conten_type, $equal = false)
    {
        if (isset($this->conf['Response']['Accept'][$conten_type]))
        {

            $this->Response->SetLayaut(NULL);
            $this->Response = NULL;
            $this->ContentTypeOrig = $conten_type;
            $this->Content_type = $conten_type;
            header('Content-type: ' . $conten_type);
            $this->IntanceResponseConten();
            return true;
        }

        return false;
    }

    /**
     * cambia el objeto de respuesta al objeto por defecto
     * @param string $Content_type
     */
    public function ResponseContenDefault($Content_type)
    {
        $this->Content_type = $this->ContentTypeOrig = $Content_type;
        $conf = self::Config();
        $class = $conf['Response']['Accept']['*/*']['class'];
        $this->Response = new $class(...$conf['Response']['Accept']['*/*']['param']);
    }

    /**
     * CONECTA CON LA BASE DE DATOS
     * @param array $param
     * @return iDataBase
     * @throws Exception si la clase manejadora no se encuentra
     */
    public function &ConetDataBase(array $param = array())
    {
        $this->Log("Conectando con la base de datos ...");
        $conf = self::Config();
        $NULL = NULL;
        if (empty($conf['DB']['class']))
            return $NULL;

        $p = $conf['DB']['param'];
        foreach ($param as $i => $v)
        {
            $p[$i] = $v;
        }
        if (!class_exists($conf['DB']['class'], true))
        {
            throw new Exception("ERROR LA CLASE MANEJADORA DE BD " . $conf['DB']['class'] . " NO SE ENCONTRO");
        }
        $DBClass = $conf['DB']['class'];

        $this->DataBase = new $DBClass(...$p);

        if ($this->DataBase instanceof iDataBase)
        {
            if (!$this->DataBase->error())
            {
                $_GET = SQLi::Filter($_GET, isset($conf['VarAceptSqlI']['_GET']) ? $conf['VarAceptSqlI']['_GET'] : []);
                $_POST = SQLi::Filter($_POST, isset($conf['VarAceptSqlI']['_POST']) ? $conf['VarAceptSqlI']['_POST'] : []);
                $_COOKIE = SQLi::Filter($_COOKIE, isset($conf['VarAceptSqlI']['_COOKIE']) ? $conf['VarAceptSqlI']['_COOKIE'] : []);
            }
        }
        return $this->DataBase;
    }

    /**
     * @ignore 
     */
    public function tick()
    {
        $this->time++;
    }

    /**
     * 
     * @access private 
     */
    public function Log($msj, $fin = false)
    {
        if (!empty($this->conf) && $this->stdErr)
            if ($this->conf['debung'] && !isset($this->conf['debung'][0]))
            {


                if ($fin)
                {
                    fputs($this->stdErr, 'ID:' . $this->id . "; " . ' Tick:' . ( $this->time) . '; ' . @var_export($msj, true) . "\r\n"
                            . "Time-Excecution:" . ( microtime(true) - $this->t ) . " segundos\r\n"
                            . "Memory-Usage:" . (memory_get_usage(true)) . " bytes\r\n\r\n\r\n");
                    $conten = '';
                    if ($this->conf['debung']['file'] != 'php://stderr')
                    {
                        $conten = file_get_contents('php://stderr');
                    }
                    fwrite($this->stdErr, $conten);
                    fclose($this->stdErr);
                } else
                {
                    fputs($this->stdErr, 'ID:' . $this->id . "; " . ' Tick:' . ( $this->time) . '; ' . @var_export($msj, true) . "\r\n");
                }
            }
    }

    /**
     * EJECUTA LOS LAYAUTS 
     */
    private function LoadLayaut()
    {
        if (!($this->Response instanceof ResponseConten) || $this->fin == true)
            return;
        $this->fin = true;
        if ($this->LayautManager)
            $this->LayautManager->EndConten();
    }

    /**
     * callback para ob_start 
     * @param string $conten
     * @return string
     * @throws Exception
     * @internal 
     */
    public function Handle($conten)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD')
            return '';
        if (($this->Response instanceof ResponseConten) && $this->ProcessConten === true)
        {
            Mvc::App()->Log("PROCESANDO LA RESPUESTA  ...");

//   Mvc::App()->Log(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));

            try
            {
                $Conten2 = Mvc::App()->Response->ProccessConten($conten);
            } catch (\Exception $ex)
            {
                http_response_code(500);
                Mvc::App()->Log($ex);
                return $this->Buffer->Handle($ex);
            } catch (\Error $ex)
            {
                http_response_code(500);
                Mvc::App()->Log($ex);
                return $this->Buffer->Handle($ex);
            }

            if (!is_null($Conten2))
            {
                return $this->Buffer->Handle($Conten2);
            } else
            {
                http_response_code(500);
                Mvc::App()->Log("RESPUESTA FALLIDA  ...");
                return $this->Buffer->Handle(new Exception("EL METODO ProccessConten NO RETORNO UN VALOR VALIDO"));
            }
        } ELSE
        {
            Mvc::App()->Log("PROCESANDO LA RESPUESTA DEFAULT  ...");
            return $this->Buffer->Handle($conten);
        }
    }

}
