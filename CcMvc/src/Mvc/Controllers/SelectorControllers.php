<?php

namespace Cc\Mvc;

use Cc\Mvc;
use Cc\DependenceInyector;
use Cc\Autoload\SearchClass;
use Cc\Cache;
use Cc\HelperArray;

/**
 * SelectorControllers                                             
 * ESTA CLASE SE ENCARGA DE BUSCAR Y EJECUTAR LOS CONTROLADORES IYECTANDO LAS   
 * DEPENDENCIAS QUE REQUIERA                                                    
 *                                                                              
 *                                                                              
 *                                                            
 *                                                        
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc
 * @subpackage Controladores 
 *              
 */
class SelectorControllers
{

    /**
     * controlador actual
     * @var string 
     */
    protected $controllers;

    /**
     * metodo actual
     * @var string 
     */
    protected $method;

    /**
     *
     * @var Controllers 
     */
    protected $ObjControllers;
    protected $error = false;
    protected $conf;
    protected $InyectDependence = [];

    /**
     *
     * @var ReflectionMethod
     */
    protected $Reflexion;

    /**
     *
     * @var ReflectionClass
     */
    protected $ReflectionClass;
    protected $DirController;
    protected $NoCalable = [];
    protected $ContextApp = false;

    /**
     *
     * @var DependenceInyector 
     */
    protected $Inyector;

    /**
     *
     * @var SearchClass 
     */
    protected $SearchClass;
    public $ReRouterMethod = false;

    /**
     * 
     * @param string $DirControllers
     * @param Config $conf
     * @param DependenceInyector &$R
     */
    public function __construct($DirControllers, $conf, DependenceInyector &$R)
    {
        $this->NoCalable = [ '__call', '__callStatic', '__get', '__set', '__isset', '__unset', '__sleep', '__wakeup', '__toString', '__invoke', '__set_state', '__clone', '__debugInfo'];
        $this->InyectDependence = $conf['Controllers']['Dependencias'];
        $this->conf = &$conf;
        $this->DirController = $DirControllers;
        $this->Inyector = &$R;
    }

    /**
     * 
     * @return \ReflectionClass
     */
    public function &GetReflectionController()
    {
        return $this->ReflectionClass;
    }

    public function GetReflectionMethod()
    {
        return $this->Reflexion;
    }

    /**
     * agrega un metodo a la lista de metodos no ejecutables mediante http
     * @param string $method
     */
    public function SetMethodNoCalableHttp($method)
    {
        if (is_array($method))
        {
            foreach ($method as $v)
            {
                $this->SetMethodNoCalableHttp($v);
            }
        } else
        {
            array_push($this->NoCalable, $method);
        }
    }

    /**
     * RETORNA UNA REFERENCIA DEL CONTROLADOR ACTUAL
     */
    public function &GetController()
    {
        return $this->ObjControllers;
    }

    /**
     * 
     * @param type $ex
     * @param ReflectionMethod $method
     * @param type $end
     * @ignore
     */
    protected function ExceptionManager($ex, $method, $end = NULL)
    {
        $plustrace = NULL;
        if ($method instanceof \ReflectionMethod)
        {
            $plustrace = [
                'file' => $method->getDeclaringClass()->getFileName(),
                'function' => $method->name,
                'type' => '->',
                'line' => $method->getStartLine(),
                'class' => $method->getDeclaringClass()->name,
                'args' => $this->ParamStringArray($method->getParameters())
            ];
        }
        if ($method instanceof \ReflectionFunction)
        {
            $plustrace = [
                'file' => $method->getDeclaringClass()->getFileName(),
                'function' => $method->name,
                'type' => '',
                'line' => $method->getStartLine(),
                'class' => '',
                'args' => $this->ParamStringArray($method->getParameters())
            ];
        }
        ErrorHandle::ExceptionManager($ex, 0, $end, $plustrace);
    }

    /**
     * @ignore
     */
    protected function ParamStringArray($param)
    {
        $a = [];
        /* @var $p ReflectionParameter */
        foreach ($param as $p)
        {
            $class = '';
            try
            {
                $class = $p->getClass();
            } catch (\Exception $ex)
            {
                $class = (object) ['name' => '(undefined_class)'];
            }

            $ref = $p->isPassedByReference() ? '&' : '';
            $def = $p->isDefaultValueAvailable() ? '=' . $p->getDefaultValue() : '';
            if (is_object($class))
            {
                $a[] = $class->name . ' ' . $ref . '$' . $p->name . $def;
            } else
            {
                if (method_exists($p, 'getType'))
                {
                    $a[] = $p->getType() . ' ' . $ref . '$' . $p->name . $def;
                } elseif ($p->isArray())
                {
                    $a[] = 'array ' . $ref . '$' . $p->getName() . $def;
                } else
                {
                    $a[] = $ref . '$' . $p->getName() . $def;
                }
            }
        }
        return $a;
    }

    public function CreateClousure($ContexApp = false)
    {
        $this->controllers = ClouserController::class;
        $this->ReflectionClass = new \ReflectionClass(ClouserController::class);
        $this->ContextApp = $ContexApp;
        $this->ObjControllers = new ClouserController();
    }

    /**
     * 
     * @param string $controllers el directorio donde se encuentran los controladores
     * @param string $paquete paquete a ser ejecutado
     * @param string $method metodo a ser ejecutado
     * @param bool $ContexApp indica si el contexto en que se llamo es de Mvc
     * @return boolean
     * @throws Exception
     */
    public function CreateController($controllers, $paquete = NULL, $method = NULL, $ContexApp = false)
    {

        $this->controllers = __NAMESPACE__ . (!is_null($paquete) ? '\\' . $paquete : '') . '\\' . $this->conf['Controllers']['Prefijo'] . $controllers;
        $this->ContextApp = $ContexApp;


        $dir = $this->DirController . (is_null($paquete) ? '' : $paquete . '/');
        if (!is_dir($dir) && !is_null($paquete))
        {
            Mvc::App()->LoadError(404, "EL PAQUETE " . $paquete . " NO EXISTE");
            Mvc::EndApp();
            return false;
        }
        $CACHE = Cache::IsSave(self::class) ? Cache::Get(self::class) : [];
        if (Mvc::App()->IsDebung() && strtolower(__NAMESPACE__ . '\\' . $controllers) == strtolower(TestController::class))
        {
            $this->controllers = __NAMESPACE__ . '\\' . $controllers;
            $this->SearchClass = new SearchClass(__NAMESPACE__ . '\\' . $controllers, dirname(__FILE__), true);
            $filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TestController.php';
        } else
        {

            $this->SearchClass = new SearchClass($this->controllers, $dir, true);
            $filename = '';
            if (isset($CACHE[$this->controllers]))
            {
                $filename = $CACHE[$this->controllers];
            } else
            {
                $this->SearchClass->Search(false);

                unset(SearchClass::$classes[$this->controllers]);
                $filename = $this->SearchClass->GetFileName();
                if ($filename != '')
                {
                    $CACHE[$this->controllers] = $filename;
                    Cache::Set(self::class, $CACHE);
                }
            }
        }

        //error_reporting($conf['debung']['error_reporting']);

        if ($filename != '')
        {

            try
            {
                require_once($filename);
                // $search->Include_('require_once');
            } catch (\Exception $ex)
            {
                if ($ContexApp)
                {
                    $this->ExceptionManager($ex, NULL, 0);
                } else
                {
                    throw $ex;
                }
            } catch (\Error $ex)
            {
                if ($ContexApp)
                {
                    $this->ExceptionManager($ex, NULL, 0);
                } else
                {
                    throw $ex;
                }
            }
            $this->ReflectionClass = new \ReflectionClass($this->controllers);





            if ($ContexApp)
            {
                return [$paquete, $controllers, $method];
            } else
                return $this->InstanceController($method, $ContexApp);
        } else
        {

            if (is_dir($this->DirController . $controllers))
            {

                return $this->CreateController($method, $controllers, 'index', $ContexApp);
            }
            Mvc::App()->LoadError(404, "EL CONTRLADOR " . $controllers . " NO EXISTE");
            exit;
            return false;
        }
        return $this->ObjControllers;
    }

    private function ProtectedInterface($metodo)
    {
        /* @var $interface \ReflectionClass */
        foreach ($this->ReflectionClass->getInterfaces()as $interface)
        {

            if ($interface->isSubclassOf(iProtected::class) && $interface->hasMethod($metodo))
            {

                $this->SetMethodNoCalableHttp($metodo);
                return;
            }
        }
    }

    /**
     * crea una instancia del controlador y ejecuta un metodo
     *
     * @param string $method
     * @param bool $ContexApp
     * @return boolean
     * @throws \Exception si la clase o el metodo no existe 
     */
    public function InstanceController($method, $ContexApp)
    {

        if (!$this->ReflectionClass->isSubclassOf(Controllers::class))
        {
            if ($ContexApp)
                Mvc::App()->LoadError(403, "LA CLASE " . $this->controllers . " DEBE SER EXTENDIDA DE LA CLASE Controllers PARA SER ACCESIBLE AL NAVEGADOR");
            return false;
        }
        $costruc = $this->ReflectionClass->getConstructor();
        if (!is_null($method))
        {

            $this->method = $method;

            if ($this->CreateReflectionMethod($method))
            {
                if ($this->Reflexion->isStatic())
                {
                    return true;
                }
            } else
            {

                if ($ContexApp)
                {

                    if ($this->ReflectionClass->implementsInterface(ReRouterMethod::class))
                    {
                        $this->ReRouterMethod = true;
                    } else
                    {
                        Mvc::App()->LoadError(404, "EL METODO " . $this->method . " NO EXISTE EN EL CONTROLADOR " . $this->controllers);
                        return false;
                    }
                }
            }
        }

        $param = [];
        if ($costruc)
        {
            try
            {
                $param = $this->Inyector->SetFunction($costruc)->Param();
            } catch (\Exception $ex)
            {
                if ($ContexApp)
                {
                    $this->ExceptionManager($ex, $this->Reflexion, -5);
                } else
                {
                    throw $ex;
                }
            } catch (\Error $ex)
            {
                if ($ContexApp)
                {
                    $this->ExceptionManager($ex, $this->Reflexion, -5);
                } else
                {
                    throw $ex;
                }
            }


            $this->ContextApp = false;
        }
        try
        {


            $this->ObjControllers = $this->SearchClass->FactoryObject($param, false);
            $this->ContextApp = $ContexApp;
        } catch (\Exception $ex)
        {
            if ($ContexApp && $costruc)
            {
                $this->ExceptionManager($ex, $costruc, -7);
            } else
            {
                throw $ex;
            }
        } catch (\Error $ex)
        {
            if ($ContexApp && $costruc)
            {
                $this->ExceptionManager($ex, $costruc, -7);
            } else
            {
                throw $ex;
            }
        }

        return true;
    }

    /**
     * crea una instancia de {@link \ReflectionMethod} del metodo pasado por parametro
     * @param string $method
     * @return boolean
     */
    private function CreateReflectionMethod($method)
    {
        $this->method = $method;

        if (!$this->ReflectionClass->hasMethod($method))
        {
            return false;
        } else
        {
            $this->Reflexion = $this->ReflectionClass->getMethod($this->method);
            $this->ProtectedInterface($method);
            return true;
        }
    }

    /**
     * llama un metodo sel controlador actual por ser metodo magico generalmente se llama automaticamente
     * @param string $name
     * @param array $param
     * @return mixes
     */
    public function __call($name, $param)
    {


        return $this->Call($name);
    }

    /**
     * llama un metodo sel controlador actual 
     * @param string $metod
     * @param bool $ContexApp
     * @return boolean
     * @throws Exception
     */
    public function Call($metod = NULL, $ContexApp = false)
    {
        if (!is_null($metod))
        {
            if (!$this->CreateReflectionMethod($metod))
            {
                if ($ContexApp)
                {
                    if ($this->ReflectionClass->implementsInterface(ReRouterMethod::class) && $this->ObjControllers->__RouterMethod($metod) !== false)
                    {
                        return;
                    } else
                    {
                        Mvc::App()->LoadError(404, "EL METODO " . $this->method . " NO EXISTE EN EL CONTROLADOR " . $this->controllers);
                    }
                }

                return false;
            }
        }
        if ($this->ReflectionClass->implementsInterface(ReRouterMethod::class) && $this->ReRouterMethod)
        {
            if ($this->ObjControllers->__RouterMethod($this->method) === false)
            {
                Mvc::App()->LoadError(404, "EL METODO " . $this->method . " NO EXISTE EN EL CONTROLADOR " . $this->controllers);
                return;
            }
            return;
        }

        if ($this->ReflectionClass->implementsInterface(ProtectedMetodHttp::class))
        {
            $class = $this->ReflectionClass->name;

            if (in_array($this->method, HelperArray::TolowerRecursive($class::MethodsNoHttp())))
            {
                Mvc::App()->LoadError(403, "EL METODO " . $this->method . " NO SE PUEDE EJECUTAR YA QUE SE ESTA PROTEGIDO POR " . ProtectedMetodHttp::class . "::MethodsNoHttp()");
                return false;
            }
        }


        if ($ContexApp && in_array($this->method, $this->NoCalable))
        {
            Mvc::App()->LoadError(403, "EL METODO " . $this->method . " NO SE PUEDE EJECUTAR YA QUE SE ENCUENTRA EL LA LISTA NoCalable");
            return false;
        }

        if ($this->Reflexion->isPublic() && !$this->Reflexion->isConstructor() && !$this->Reflexion->isDestructor() && !$this->Reflexion->isAbstract())
        {
            $this->ExecuteMethod();
            return true;
        } else
        {
            $ERROR = "NO ES POSIBLE ACCESAR AL METODO " . $this->method
                    . " DEL CONTROLADOR " . $this->controllers
                    . " PUEDE SER POR QUE NO SEA UN METODO "
                    . "DEFINIDO COMO PUBLIC O SEA EL CONSTRUCTOR O DESTRUCTOR";
            if ($ContexApp)
            {
                Mvc::App()->LoadError(403, $ERROR);
                return false;
            } elseif ($this->conf['debung']['ModoExeption'] != 0)
            {
                throw new Exception($ERROR);
            }
        }
        $this->ContextApp = false;
    }

    public function CallFunction($fun)
    {

        if (is_array($fun) && $fun[1] instanceof Controllers)
        {
            $call = \Closure::bind($fun);
        } else
        {
            $call = \Closure::bind($fun, $this->ObjControllers, get_class($this->ObjControllers));
        }
        try
        {
            $param = $this->Inyector->SetFunction($fun)->Param();
        } catch (\Exception $ex)
        {
            if ($ContexApp)
            {
                $this->ExceptionManager($ex, $fun, -5);
            } else
            {
                throw $ex;
            }
        } catch (\Error $ex)
        {
            if ($ContexApp)
            {
                $this->ExceptionManager($ex, $fun, -5);
            } else
            {
                throw $ex;
            }
        }
        try
        {
            $call(...$param);

            // call_user_func_array([$this->ObjControllers, $this->method], $param);
        } catch (\Exception $ex)
        {
            if ($ContexApp)
            {
                $this->ExceptionManager($ex, $this->Reflexion, -5);
            } else
            {
                throw $ex;
            }
        } catch (\Error $ex)
        {
            if ($ContexApp)
            {
                $this->ExceptionManager($ex, $this->Reflexion, -5);
            } else
            {
                throw $ex;
            }
        }
    }

    /**
     * ejecuta un metodo del controlador
     * @throws \Exception
     */
    protected function ExecuteMethod()
    {
        $ContexApp = $this->ContextApp;
        $this->ContextApp = false;
        $method = $this->method;
        try
        {
            $param = $this->Inyector->SetFunction($this->Reflexion)->Param();
        } catch (\Exception $ex)
        {
            if ($ContexApp)
            {
                $this->ExceptionManager($ex, $this->Reflexion, -5);
            } else
            {
                throw $ex;
            }
        } catch (\Error $ex)
        {
            if ($ContexApp)
            {
                $this->ExceptionManager($ex, $this->Reflexion, -5);
            } else
            {
                throw $ex;
            }
        }


        try
        {
            if (!$this->Reflexion->isStatic())
            {

                $this->ObjControllers->$method(...$param);
            } else
            {

                $class = $this->controllers;
                $class::$method(...$param);
            }

            // call_user_func_array([$this->ObjControllers, $this->method], $param);
        } catch (\Exception $ex)
        {
            if ($ContexApp)
            {
                $this->ExceptionManager($ex, $this->Reflexion, -5);
            } else
            {
                throw $ex;
            }
        } catch (\Error $ex)
        {
            if ($ContexApp)
            {
                $this->ExceptionManager($ex, $this->Reflexion, -5);
            } else
            {
                throw $ex;
            }
        }
    }

}
