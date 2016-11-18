<?php

/**
 * 
 */

namespace Cc;

use Cc\Ws\SocketServer;
use Cc\Ws\WsClient;
use Cc\Ws\Event;
use Cc\Autoload\CoreClass;
use Cc\Autoload\SearchClass;

include_once __DIR__ . '/../Cc/Autoload/CoreClass.php';

/**
 * App                                                                
 * CLASE PRINCIPAL DE LA RECUBIERTA MVC 
 * DONDE SE EJECUTARA TODA LA APLICACION
 *                     
 *                                                                              
 *                                                                                                                 
 * @autor: ENYREBER FRANCO                                                      
 * @email: <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @copyright © 2015, Enyerber Franco, Todos Los Derechos Reservados 
 * @package CcWs
 * @uses CoreClass.php se usa para cargar automaticamente las clases de core 
 * @uses Router enruta las peticiones a los Eventos 
 * @uses DependenceInyector para proporcionar las dependencias para los Eventos 
 * @uses Config para cargar la configuracion
 * @uses iDataBase para el manejo de bases de datos
 * @uses SocketServer para el servidor 
 * @uses WsClient para administrar os clientes 
 * @uses WsEvent para capturar los eventos producidos por cada cliente
 * @todo SE NESESITA AGREGAR SOPORTE A THREADS O MULTIHILOS  
 */
class Ws
{

    /**
     *
     * @var  SocketServer
     */
    protected $WebSocket;
    protected $DirEvents = '';

    /**
     *
     * @var Config 
     */
    protected $conf;
    private static $Instance;
    private $procedures;
    private $stdErr;
    public $SocketConfig;

    /**
     *  @var Event
     *  
     */
    private $Event;

    /**
     *
     * @var DependenceInyector 
     */
    public $DependenceInyector;
    private $Database;
    private $LoopStatics = [];

    /**
     * @var Ws\Comand
     */
    private $comandos;

    use CoreClass;

    /**
     * 
     * @param string $SocketConf direccion del archivo de configuracion
     */
    public function __construct($SocketConf = NULL)
    {


        self::$Instance = &$this;
        $defaultConf = __DIR__ . '/config/SocketConfDefault.php';
        if (is_null($SocketConf))
        {
            $SocketConf = $defaultConf;
        }
        $this->StartAutoloadCore(realpath(dirname(__FILE__) . '/../'));
        $this->conf = new Config($defaultConf);

        $this->conf->Load($SocketConf);
        if (is_bool($this->conf['debung']))
        {
            if (!$this->conf['debung'])
            {
                $this->conf['debung'] = [false, 'ModoExeption' => 0, 'error_reporting' => 0, 'NoReenviarFiles' => false];
            } else
            {
                $this->conf['debung'] = $this->conf->default['debung'];
                $this->stdErr = fopen($this->conf['debung']['file'], 'a+');
                $this->time = 0;
                register_tick_function([$this, 'tick']);
            }
        } else
        {
            $this->stdErr = fopen($this->conf['debung']['file'], 'a+');

            $this->time = 0;
            register_tick_function([$this, 'tick']);
        }
        $this->Log("Cargando Archivo de configuracion :" . $SocketConf . "...");
        $this->procedures = $this->conf['App']['procedimientos'];

        SearchClass::AddDirAutoload($this->conf['App']['model'], true);
        SearchClass::AddDirAutoload($this->conf['App']['extern'], true);
        //SearchClass::AddDirAutoload($this->conf['App']['SocketEvent'], true);
        SearchClass::StartAutoloadClass();
        $this->LoadPorcedures();
        CcException::SetMode($this->conf['debung']['ModoExeption']);
        error_reporting($this->conf['debung']['error_reporting']);
        $this->DirEvents = $this->conf['App']['ServerRoot'];
        $this->WebSocket = new SocketServer($this->conf['MaxClients']);
        $this->WebSocket->bind('message', ['OnMenssaje', &$this]);
        $this->WebSocket->bind('open', ['OnOpen', &$this]);
        $this->WebSocket->bind('close', ['OnClose', &$this]);
        $this->WebSocket->bind('OnLoop', ['OnLoopClient', &$this]);
        $this->WebSocket->bind('OnLoopAll', ['OnLoopAll', &$this]);

        $this->SocketConfig = $this->conf;
        $this->DependenceInyector = new DependenceInyector();
        $this->DependenceInyector->AddDependenceInstanciable($this->conf['Dependencias']);
        $this->DependenceInyector->AddDependence('{config}', $this->conf);
        $this->DependenceInyector->AddDependence('{this}', $this);
    }

    /*
      $this->comandos= new Ws\Comand();

      $this->comandos->AddComand('Stop', [$this,'StopServer']);
      $this->comandos->AddComand('CountCliets', [$this,'CountCliets']);




      //$this->DependenceInyector->AddDependence('{Server}', $this->WebSocket);
      }
      public function CountCliets(Ws\Comand $c)
      {
      $c->Write(count($this->WebSocket->wsClients));
      echo count($this->WebSocket->wsClients);
      }
      public function StopServer()
      {

      $this->WebSocket->wsStopServer();
      unset($this);

      } */

    /**
     * 
     * @param type $string
     * @return type
     * @access private
     */
    public function ProcessSend($string)
    {

        return (string) $string;
    }

    /**
     * AGREGA UN EVENTO A LA PILA DE EVENTOS DEL SERVIDOR 
     * @param string $type tipo del evento este puede ser messaje, open, close, OnLoop, OnLoopAll
     * @param callable $func una function de retrollamada 
     */
    public function bind($type, $func)
    {
        $this->WebSocket->bind($type, $func);
    }

    /**
     * pone en marcha el servidor 
     */
    public function Run()
    {
        $this->Log("Servidor WebSocket creado ws://" . $this->SocketConfig['host'] . ":" . $this->SocketConfig['port']);
        $this->WebSocket->wsStartServer($this->SocketConfig['host'], $this->SocketConfig['port']);
    }

    /**
     * es el metodo que atendera el evento OnLoop por defecto 
     * @access private
     * @param WsClient $v
     */
    public function OnLoopClient(WsClient &$v)
    {
        if (method_exists($v->Event, 'Loop'))
        {
            $v->Event->Loop(... $this->DependenceInyector->SetFunction([$v->Event, 'Loop'])->Param());
        }
    }

    /**
     * es el metodo que atendera el evento OnLoopAll
     * @access private
     */
    public function OnLoopAll()
    {

        //$this->comandos->Read();
        // $this->log(memory_get_usage());
        foreach ($this->LoopStatics as $static)
        {
            if (count($static::$Clients) > 0 && method_exists($static, 'LoopStatic'))
            {
                $static::LoopStatic(... $this->DependenceInyector->SetFunction([$static, 'LoopStatic'])->Param());
            }
        }
        $this->DependenceInyector->LimpiarDependenceForParam();
    }

    /**
     * atendera el evento OnOpen
     * @param WsClient $client
     * @return type
     * @access private
     */
    public function OnOpen(WsClient &$client)
    {
        $this->Log("Ip:" . $client->Ip() . " ESTABLECIENDO CONECCION  ");
        $this->Log("Ip:" . $client->Ip() . " HEADERS DE PETICION:");
        $this->Log($client->HeadersRequest);
        $this->DependenceInyector->AddDependence('{cliente}', $client);
        $client->SetFunctionProcesSends([$this, "ProcessSend"]);
        $client->Event = $this->Router($client);
        if (is_null($client->Event))
        {
            $client->Remove();
            return;
        }
        try
        {
            /* $conf = $this->conf;
              $session=NULL;
              if(!empty($conf['Autenticate']['class']) && !empty($conf['Autenticate']['SessionName']))
              {
              $this->Log("Autenticando....");

              $class = $conf['Autenticate']['class'];
              @var $session Autenticate
              $session = new $class(...$conf['Autenticate']['param']);
              $session->SetDependenceInyector($this->DependenceInyector);
              $session->SetReadAndClose(true);
              $session->Start();
              $session->Auth();
              } */
            if ($client->Event->SESSION)
            {
                $client->Event->SESSION->Start();
            }
            $client->Event->OnOpen(...$this->DependenceInyector->SetFunction([ $client->Event, 'OnOpen'])->Param());
            if ($client->Event->SESSION)
            {
                $client->Event->SESSION->Commit();
            }
            $client->Event->EndOnOpen();
            // unset($_SESSION);
        } catch (\Exception $ex)
        {
            $client->Event->OnException($ex);
        } catch (\Error $ex)
        {
            $client->Event->OnException($ex);
        }

        $this->Log("Ip:" . $client->Ip() . " HEADERS DE RESPUESTA:");
        $this->Log($client->HeaderResponse);
    }

    /**
     * Atendera el evento messaje
     * @param WsClient $client
     * @param string $message
     * @param int $messageLength
     * @param bool $binary
     * @return type
     * @access private
     */
    public function OnMenssaje(WsClient &$client, $message, $messageLength, $binary)
    {
        if ($messageLength == 0)
        {
            $this->WebSocket->wsClose($client->GetId());
            return;
        }

        $this->DependenceInyector->AddDependence('{cliente}', $client);
        $this->DependenceInyector->AddDependence('{messaje}', $message);
        $this->DependenceInyector->AddDependence('{messageLength}', $messageLength);
        $this->DependenceInyector->AddDependence('{binary}', $binary);


        if (!empty($client->Event))
            if (method_exists($client->Event, 'OnMessaje'))
            {
                try
                {
                    $this->DependenceInyector->SetFunction([$client->Event, 'OnMessaje']);
                    if ($client->Event->SESSION)
                    {
                        $client->Event->SESSION->Start();
                    }
                    $client->Event->OnMessaje(...$this->DependenceInyector->Param());
                    if ($client->Event->SESSION)
                    {
                        $client->Event->SESSION->Commit();
                    }
                } catch (\Exception $ex)
                {
                    $client->Event->OnException($ex);
                } catch (\Error $ex)
                {
                    $client->Event->OnException($ex);
                }
            }
        $this->DependenceInyector->LimpiarDependenceForParam();
        $h = '';
        $this->DependenceInyector->AddDependenceForParam('{messaje}', $h);
        $this->DependenceInyector->AddDependence('{messageLength}', $h);
        $this->DependenceInyector->AddDependence('{binary}', $h);

        //$this->DependenceInyector->SetDependenceForParamArray($array);
    }

    /**
     * atiende el evento close del servidor
     * @param WsClient $client
     * @access private
     */
    public function OnClose(WsClient &$client)
    {
        $this->Log("Ip:" . $client->Ip() . "   HA CERRADO LA CONNECION ");
        $this->DependenceInyector->AddDependence('{cliente}', $client);
        if (!empty($client->Event))
            if (method_exists($client->Event, 'OnMessaje'))
            {
                try
                {
                    $this->DependenceInyector->SetFunction([$client->Event, 'OnClose']);
                    if ($client->Event->SESSION)
                    {
                        $client->Event->SESSION->Start();
                    }
                    $client->Event->OnClose(...$this->DependenceInyector->Param());
                    if ($client->Event->SESSION)
                    {
                        $client->Event->SESSION->Commit();
                    }
                } catch (\Exception $ex)
                {
                    $client->Event->OnException($ex);
                } catch (\Error $ex)
                {
                    $client->Event->OnException($ex);
                }
            }
        unset($client->Event);
    }

    /**
     * 
     * @param WsClient $client
     * @return \Event
     */
    private function Router(WsClient &$client)
    {
        $_SERVER['REQUEST_URI'] = $client->RequestDirname;

        $router = new Router([]);
        if ($router->IsEnrutableFile(realpath($this->DirEvents), $client->RequestDirname) && $router->InfoFile->getExtension() == 'php')
        {
            try
            {
                self::LoadEvent($router->InfoFile);
            } catch (\Error $ex)
            {
                echo $ex;
                $n = NULL;
                return $n;
            }
        } else
        {
            $client->Send('');
            $this->Log("Ip:" . $client->Ip() . "  No se pudo enrutar el cliente Direccion invalida:" . $client->RequestDirname);
            $client->Remove();
            $n = NULL;
            return $n;
        }
        $Event = __NAMESPACE__ . '\\Ws\\' . $router->InfoFile->getBasename('.php');

        if (class_exists($Event, false) && is_subclass_of($Event, Event::class))
        {
            $this->LoopStatics[] = $Event;
        } else
        {
            $this->Log("Ip:" . $client->Ip() . " No se pudo enrutar el cliente  Direccion invalida:" . $client->RequestDirname);
            $client->Remove();
            $n = NULL;
            return $n;
        }

        $this->Log("Ip:" . $client->Ip() . " El cliente ha sido enrutado hacia el evento " . $router->InfoFile);
        unset($router);
        return new $Event($client);
    }

    private static function LoadEvent($______)
    {
        include_once($______);
    }

    /**
     * 
     */
    private function LoadPorcedures()
    {
        var_dump($this->procedures);
        if (file_exists($this->procedures))
        {
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
    }

    /**
     * @ignore
     */
    public function tick()
    {
        $this->time++;
    }

    /**
     * @ignore
     * @param type $msj
     * @param type $fin
     */
    public function Log($msj, $fin = false)
    {

        if (!empty($this->conf))
            if ($this->conf['debung'] && !isset($this->conf['debung'][0]))
            {
                fwrite($this->stdErr, ' Tick:' . ( $this->time) . '; ' . var_export($msj, true) . "\n");
                if ($fin)
                {
                    $conten = '';
                    if ($this->conf['debung']['file'] != 'php://stderr')
                    {
                        $conten = file_get_contents('php://stderr');
                    }
                    fwrite($this->stdErr, $conten);
                    fclose($this->stdErr);
                }
            }
    }

    /**
     * retorna una referencia al servidor en ejecucion
     * @return Ws
     */
    public static function Server()
    {
        return self::$Instance;
    }

    /**
     * OBJETO DE CONFIGURACION
     * @return Config
     */
    public function Conf()
    {
        return $this->conf;
    }

}
