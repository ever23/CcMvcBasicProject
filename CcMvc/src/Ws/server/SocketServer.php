<?php

namespace Cc\Ws;

/**
 * SERVIDOR WEB SOCKET                                                         
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>      
 * @package CcWs
 * @subpackage Server  
 */
class SocketServer extends PHPWebSocket
{

    /**
     * 
     * @param int $MaxClients
     */
    public function __construct($MaxClients = 100)
    {
        parent::__construct($MaxClients);
        /// $this->bind('open', ['OnOpen', &$this]);
    }

    /**
     * envia un mensaje a todos los clientes conectados al servidor
     * @param string $mensaje
     * @param boolean $binary
     */
    public function Send($mensaje, $binary = false)
    {
        /* @var $clie WsClient */
        foreach($this->wsClients as $id => &$clie)
        {
            $clie->Send($mensaje, $binary);
        }
    }

}
