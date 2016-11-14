<?php

namespace Cc\Ws;

use Cc\Ws;

/**
 * de esta clase se extenderan todas las clases manejadoras de eventos del servidor 
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>      
 * @package CcWs
 * @subpackage Events 
 * @example ../examples/WebSocket/WebSocket/Wsdocs/MiChat.php EJEMPLO DE UNA CLASE MANEJADORA DE EVENTOS
 */
abstract class Event
{

    /**
     * contiene los clientes que estan conectados a el evento
     * @var array 
     */
    public static $Clients = [];

    /**
     * cliente conectado al objeto de evento 
     * @var WsClient 
     */
    public $Client;

    /**
     *  cookie recibida por el cliente
     * @var Cookie 
     */
    public $Cookie;

    /**
     * variables get recibidas en la coneccion 
     * @var array 
     */
    public $GET = [];

    /**
     * 
     */
    const SendAll = 123;

    /**
     *
     * @var \ReflectionClass 
     */
    private $Reflection = NULL;

    /**
     *
     * @var Session 
     */
    public $SESSION = NULL;

    /**
     * @access private
     * @param WsClient $client
     */
    public final function __construct(WsClient &$client)
    {
        static::$Clients[$client->GetId()] = $this->Client = &$client;

        $this->Cookie = new Cookie(Ws::Server()->Conf(), $client);
        parse_str(urldecode($client->Query_string), $this->GET);
        $this->Reflection = new \ReflectionClass($this);
        if ($this->Reflection->implementsInterface(UseSession::class))
        {
            $this->SESSION = new Session($this, ...$this->SessionParams());
        }
    }

    public function __destruct()
    {
        unset(static::$Clients[$this->Client->GetId()]);
        unset($this->Cookie);
        unset($this->Client);
        unset($this->GET);
        unset($this->SESSION);
    }

    /**
     * @access private
     */
    public function EndOnOpen()
    {
        $this->Cookie->SendHeader();
    }

    /**
     * 
     * @return resource socket al cual esta conectado
     */
    public function Socket()
    {
        return $this->Client->GetSocket();
    }

    /**
     * envia un mensaje a todos los clientes conectados a el evento del objeto 
     * @param string $message
     * @param boolean $binary
     * 
     */
    protected function Send($message, $binary = false, $clients = 0)
    {
        /* @var $v WsClient */
        foreach (static::$Clients as $i => $v)
        {
            if ($this->Client->GetId() != $v->GetId() || $clients == self::SendAll)
                $v->Send($message, $binary);
        }
    }

}
