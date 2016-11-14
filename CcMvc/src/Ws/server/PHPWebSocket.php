<?php

namespace Cc\Ws;

include_once 'WsClient.php';
/*
 *  Based on PHP WebSocket Server 0.2
 *  - http://code.google.com/p/php-websocket-server/
 *  - http://code.google.com/p/php-websocket-server/wiki/Scripting
 *
 *  WebSocket Protocol 07
 *  - http://tools.ietf.org/html/draft-ietf-hybi-thewebsocketprotocol-07
 *  - Supported by Firefox 6 (30/08/2011)
 *
 *  Whilst a big effort is made to follow the protocol documentation, the current script version may unknowingly differ.
 * Please report any bugs you may find, all feedback and questions are welcome!
 */

/**
 * @package CcWs
 */
class PHPWebSocket
{

    protected $WS_MAX_CLIENTS = 100;

    // máximo de clientes que pueden conectarse a la vez
    const WS_MAX_CLIENTS = 100;
    // importe máximo de clientes que pueden conectarse a la vez en la misma dirección de IP v4
    const WS_MAX_CLIENTS_PER_IP = 15;
    // cantidad de segundos que un cliente tiene para enviar los datos al servidor, antes de enviar una solicitud de ping al cliente,
    // si el cliente no ha completado el apretón de manos de apertura, la solicitud de ping se omite y la conexión del cliente se cierra
    const WS_TIMEOUT_RECV = 1800;
    // cantidad de segundos que un cliente tiene para responder a una solicitud de ping, antes de la conexión del cliente se cierra
    const WS_TIMEOUT_PONG = 5;
    // la longitud máxima, en bytes, de los datos de carga útil de una trama (un mensaje consiste en 1 o más marcos), esto también se limita internamente al 2,147,479,538
    const WS_MAX_FRAME_PAYLOAD_RECV = 100000;
    // la longitud máxima, en bytes, de los datos de carga útil de un mensaje, esto también se limita internamente al 2,147,483,647
    const WS_MAX_MESSAGE_PAYLOAD_RECV = 500000;
    // internal
    const WS_FIN = 128;
    const WS_MASK = 128;
    const WS_OPCODE_CONTINUATION = 0;
    const WS_OPCODE_TEXT = 1;
    const WS_OPCODE_BINARY = 2;
    const WS_OPCODE_CLOSE = 8;
    const WS_OPCODE_PING = 9;
    const WS_OPCODE_PONG = 10;
    const WS_PAYLOAD_LENGTH_16 = 126;
    const WS_PAYLOAD_LENGTH_63 = 127;
    const WS_READY_STATE_CONNECTING = 0;
    const WS_READY_STATE_OPEN = 1;
    const WS_READY_STATE_CLOSING = 2;
    const WS_READY_STATE_CLOSED = 3;
    const WS_STATUS_NORMAL_CLOSE = 1000;
    const WS_STATUS_GONE_AWAY = 1001;
    const WS_STATUS_PROTOCOL_ERROR = 1002;
    const WS_STATUS_UNSUPPORTED_MESSAGE_TYPE = 1003;
    const WS_STATUS_MESSAGE_TOO_BIG = 1004;
    const WS_STATUS_TIMEOUT = 3000;

    // global vars
    public $wsClients = array();
    public $wsRead = array();
    public $wsClientCount = 0;
    public $wsClientIPCount = array();
    public $wsOnEvents = array();

    /*
      $this->wsClients[ integer ClientID ] = array(
      0 => resource  Socket,                            // client socket
      1 => string    MessageBuffer,                     // a blank string when there's no incoming frames
      2 => integer   ReadyState,                        // between 0 and 3
      3 => integer   LastRecvTime,                      // set to time() when the client is added
      4 => int/false PingSentTime,                      // false when the server is not waiting for a pong
      5 => int/false CloseStatus,                       // close status that wsOnClose() will be called with
      6 => integer   IPv4,                              // client's IP stored as a signed long, retrieved from ip2long()
      7 => int/false FramePayloadDataLength,            // length of a frame's payload data, reset to false when all frame data has been read (cannot reset to 0, to allow reading of mask key)
      8 => integer   FrameBytesRead,                    // amount of bytes read for a frame, reset to 0 when all frame data has been read
      9 => string    FrameBuffer,                       // joined onto end as a frame's data comes in, reset to blank string when all frame data has been read
      10 => integer  MessageOpcode,                     // stored by the first frame for fragmented messages, default value is 0
      11 => integer  MessageBufferLength                // the payload data length of MessageBuffer
      )

      $wsRead[ integer ClientID ] = resource Socket         // this one-dimensional array is used for socket_select()
      // $wsRead[ 0 ] is the socket listening for incoming client connections

      $wsClientCount = integer ClientCount                  // amount of clients currently connected

      $wsClientIPCount[ integer IP ] = integer ClientCount  // amount of clients connected per IP v4 address
     */

    // server state functions
    public function __construct($MaxClients = 100)
    {
        $this->WS_MAX_CLIENTS = $MaxClients;
        $this->bind('remove', ['wsRemoveClient', &$this]);
    }

    function wsStartServer($host, $port)
    {
        if(isset($this->wsRead[0]))
            return false;

        if(!$this->wsRead[0] = socket_create(AF_INET, SOCK_STREAM, SOL_TCP))
        {
            return false;
        }
        if(!socket_set_option($this->wsRead[0], SOL_SOCKET, SO_REUSEADDR, 1))
        {
            socket_close($this->wsRead[0]);
            return false;
        }
        if(!socket_bind($this->wsRead[0], $host, $port))
        {
            socket_close($this->wsRead[0]);
            return false;
        }
        if(!socket_listen($this->wsRead[0], 10))
        {
            socket_close($this->wsRead[0]);
            return false;
        }
        $this->Loop();
    }

    private function Loop()
    {
        $write = array();
        $except = array();

        $nextPingCheck = time() + 1;
        while(isset($this->wsRead[0]))
        {
            $changed = $this->wsRead;
            $result = socket_select($changed, $write, $except, 1);

            if($result === false)
            {
                socket_close($this->wsRead[0]);
                return false;
            } elseif($result > 0)
            {
                foreach($changed as $clientID => $socket)
                {
                    if($clientID != 0)
                    {
                        // client socket changed
                        $buffer = '';
                        $bytes = @socket_recv($socket, $buffer, 4096, 0);

                        if($bytes === false)
                        {
                            // error on recv, remove client socket (will check to send close frame)
                            $this->wsSendClientClose($clientID, self::WS_STATUS_PROTOCOL_ERROR);
                        } elseif($bytes > 0)
                        {

                            // process handshake or frame(s)
                            if(!$this->wsProcessClient($clientID, $buffer, $bytes))
                            {

                                $this->wsSendClientClose($clientID, self::WS_STATUS_PROTOCOL_ERROR);
                            }
                        } else
                        {
                            // 0 bytes received from client, meaning the client closed the TCP connection
                            $this->wsClients[$clientID]->Remove();
                        }
                    } else
                    {
                        // listen socket changed
                        $client = socket_accept($this->wsRead[0]);
                        if($client !== false)
                        {
                            // fetch client IP as integer
                            //$clientIP = '';
                            //TOMA COMO ID DEL USUARIO SU IP LOCAL, SE CAMBIO A ASIGNARLE CON STRTOTMIE
                            $clientIP = strtotime(date("Y-m-d H:i:s"));
                            $result = socket_getpeername($client, $clientIP);
                            $clientIP = ip2long($clientIP);

                            if($result !== false && $this->wsClientCount < $this->WS_MAX_CLIENTS && (!isset($this->wsClientIPCount[$clientIP]) || $this->wsClientIPCount[$clientIP] < self::WS_MAX_CLIENTS_PER_IP))
                            {
                                $this->wsAddClient($client, $clientIP);
                            } else
                            {
                                socket_close($client);
                            }
                        }
                    }
                }
            }
            $this->CallEvent('OnLoopAll');
            if(time() >= $nextPingCheck)
            {
                $this->wsCheckIdleClients();

                $nextPingCheck = time() + 1;
            }
        }

        return true; // returned when wsStopServer() is called
    }

    private function CallEvent($type, $agrs = [])
    {
        if(array_key_exists($type, $this->wsOnEvents))
        {
            foreach($this->wsOnEvents[$type] as $func)
            {

                if(is_array($func))
                {
                    list($method, $class) = $func;

                    if(is_object($class))
                    {
                        $class->$method(...$agrs);
                    } else
                    {
                        $class::$method(...$agrs);
                    }
                } else
                {

                    $func(...$agrs);
                }
            }
        }
    }

    function wsStopServer()
    {
        // check if server is not running
        if(!isset($this->wsRead[0]))
            return false;

        // close all client connections
        foreach($this->wsClients as $clientID => $client)
        {
            // if the client's opening handshake is complete, tell the client the server is 'going away'
            if($client[2] != self::WS_READY_STATE_CONNECTING)
            {
                $this->wsSendClientClose($clientID, self::WS_STATUS_GONE_AWAY);
            }
            socket_close($client[0]);
        }

        // close the socket which listens for incoming clients
        socket_close($this->wsRead[0]);

        // reset variables
        $this->wsRead = array();
        $this->wsClients = array();
        $this->wsClientCount = 0;
        $this->wsClientIPCount = array();

        return true;
    }

    // client timeout functions
    private function wsCheckIdleClients()
    {
        $time = time();
        /* @var $client WsClient */
        foreach($this->wsClients as $clientID => &$client)
        {
            if($client->ReadyState != self::WS_READY_STATE_CLOSED)
            {
                // client ready state is not closed
                if($client->PingSentTime !== false)
                {
                    // ping request has already been sent to client, pending a pong reply
                    if($time >= $client->PingSentTime + self::WS_TIMEOUT_PONG)
                    {
                        // client didn't respond to the server's ping request in self::WS_TIMEOUT_PONG seconds

                        $this->wsClients[$clientID]->SendClose(self::WS_STATUS_TIMEOUT);
                        $this->wsClients[$clientID]->Remove();
                    }
                } elseif($time >= $client->LastRecvTime + self::WS_TIMEOUT_RECV)
                {
                    // last data was received >= self::WS_TIMEOUT_RECV seconds ago
                    if($client->ReadyState != self::WS_READY_STATE_CONNECTING)
                    {
                        // client ready state is open or closing
                        $this->wsClients[$clientID]->SetPingSentTime(time());
                        $this->wsClients[$clientID]->SendMensaje(self::WS_OPCODE_PING, '');
                    } else
                    {
                        // client ready state is connecting
                        $this->wsClients[$clientID]->Remove();
                    }
                }
                if($client instanceof WsClient)
                    $this->CallEvent('OnLoopClient', [&$client]);
            }
        }
    }

    // client existence functions
    private function wsAddClient($socket, $clientIP)
    {
        // increase amount of clients connected
        $this->wsClientCount++;


        // increase amount of clients connected on this client's IP
        if(isset($this->wsClientIPCount[$clientIP]))
        {
            $this->wsClientIPCount[$clientIP] ++;
        } else
        {
            $this->wsClientIPCount[$clientIP] = 1;
        }

        // fetch next client ID
        $clientID = $this->wsGetNextClientID();
        $client = array($socket, '', self::WS_READY_STATE_CONNECTING, time(), false, 0, $clientIP, false, 0, '', 0, 0);
        $this->wsClients[$clientID] = new WsClient($client, $clientID, $this->wsOnEvents);
        // store initial client data
        // store socket - used for socket_select()
        $this->wsRead[$clientID] = $socket;
    }

    function wsRemoveClient(WsClient &$client)
    {


        // decrease amount of clients connected on this client's IP
        $clientIP = $client->GetIp();
        $clientID = $client->GetId();
        if($this->wsClientIPCount[$clientIP] > 1)
        {
            $this->wsClientIPCount[$clientIP] --;
        } else
        {
            unset($this->wsClientIPCount[$clientIP]);
        }

        // decrease amount of clients connected
        $this->wsClientCount--;

        // remove socket and client data from arrays
        unset($this->wsRead[$clientID], $this->wsClients[$clientID]);
    }

    // client data functions
    private function wsGetNextClientID()
    {
        $i = 1; // starts at 1 because 0 is the listen socket
        while(isset($this->wsRead[$i]))
            $i++;
        return $i;
    }

    protected function wsGetClientSocket($clientID)
    {
        return $this->wsClients[$clientID]->GetSocket();
    }

    // client read functions
    private function wsProcessClient($clientID, &$buffer, $bufferLength)
    {

        return $this->wsClients[$clientID]->Process($buffer, $bufferLength);
    }

    protected function wsSendClientClose($clientID, $status = false)
    {
        $this->wsClients[$clientID]->SendClose($status);
    }

    // client non-internal functions
    public function wsClose($clientID)
    {
        return $this->wsSendClientClose($clientID, self::WS_STATUS_NORMAL_CLOSE);
    }

    function log($message)
    {
        //echo date_default_timezone_set('America/Mexico_City');
        //echo date('Y-m-d H:i:s: ') . $message . "\n";
    }

    function bind($type, $func)
    {
        if(!isset($this->wsOnEvents[$type]))
            $this->wsOnEvents[$type] = array();
        // if(!in_array($func,    $this->wsOnEvents[$type]))
        $this->wsOnEvents[$type][] = $func;
    }

    function unbind($type = '')
    {
        if($type)
            unset($this->wsOnEvents[$type]);
        else
            $this->wsOnEvents = array();
    }

}
