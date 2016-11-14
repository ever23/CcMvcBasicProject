<?php

namespace Cc\Ws;

/**
 * esta clase representa la coneccion de un cliente con el servidor                                                         
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>      
 * @package CcWs
 * @subpackage Client
 */
class WsClient
{

    public $Query_string;

    /**
     *
     * @var type 
     */
    public $CalledSend;

    /**
     *  @var WsEvent
     *  
     */
    public $Event;

    /**
     * contendra el directorio requerido por el cliente
     * @var string
     */
    public $RequestDirname = '';

    /**
     * contendra un array con todas las cabeceras enviadas por el cliente 
     * @var array  
     */
    public $HeadersRequest = [];

    /**
     * contendra un array con las cabeceras de respuesta 
     * @var array 
     */
    public $HeaderResponse = [];
    private $headerSend = false;
    private $wsOnEvents = [];
    private $ID;
    private $CountIp = NULL;
    private $active = true;
    private $Socket;
    private $MensajeBuffer = '';
    public $ReadyState;
    public $LastRecvTime;
    public $PingSentTime = false;
    private $CloseStatus = 0;
    private $IPv4;
    private $FramePayloadDataLength = false;
    private $FrameBytesRead = 0;
    private $FrameBuffer = '';
    private $MessageOpcode = 0;
    private $MessageBufferLength = 0;

    /**
     * @access private
     * @param array $conf
     * @param int $id
     * @param array $Events
     */
    public function __construct(array $conf, $id, &$Events = [])
    {
        $this->Socket = $conf[0];
        $this->MensajeBuffer = $conf[1];
        $this->ReadyState = $conf[2];
        $this->LastRecvTime = $conf[3];
        $this->PingSentTime = $conf[4];
        $this->CloseStatus = $conf[5];
        $this->IPv4 = $conf[6];
        $this->FramePayloadDataLength = $conf[7];
        $this->FrameBytesRead = $conf[8];
        $this->FrameBuffer = $conf[9];
        $this->MessageOpcode = $conf[10];
        $this->MessageBufferLength = $conf[11];


        $this->wsOnEvents = $Events;
        $this->ID = $id;
    }

    public function __destruct()
    {
        unset($this->Socket);
        unset($this->MensajeBuffer);
        unset($this->ReadyState);
        unset($this->LastRecvTime);
        unset($this->PingSentTime);
        unset($this->CloseStatus);
        unset($this->IPv4);
        unset($this->FramePayloadDataLength);
        unset($this->FrameBytesRead);
        unset($this->FrameBuffer);
        unset($this->MessageOpcode);
        unset($this->MessageBufferLength);
        unset($this->wsOnEvents);
        unset($this->RequestDirname);
        unset($this->HeaderResponse);
        unset($this->HeadersRequest);
        unset($this->Event);
    }

    /**
     * 
     * @return resource socket al cual esta conectado
     */
    public function GetSocket()
    {
        return $this->Socket;
    }

    /**
     * 
     * @return mixes el Ipv4 
     */
    public function GetIp()
    {
        return $this->IPv4;
    }

    /**
     * 
     * @return string
     */
    public function Ip()
    {
        return long2ip($this->IPv4);
    }

    /**
     * id del cliente 
     * @return int
     */
    public function GetId()
    {
        return $this->ID;
    }

    /**
     * @access private 
     * @param type $fun
     */
    public function SetFunctionProcesSends($fun)
    {
        $this->CalledSend = $fun;
    }

    /**
     * envia un mensaje al cliente 
     * @param string $message
     * @param boolean $binary indica si el mensaje es binario
     */
    public function Send($message, $binary = false)
    {
        if(!is_null($this->CalledSend))
        {
            $func = $this->CalledSend;
            if(is_array($func))
            {
                list($class, $method) = $func;

                if(is_object($class))
                {
                    $message = $class->$method($message);
                } else
                {
                    $message = $class::$method($message);
                }
            } else
            {

                $message = $func($message);
            }
        }
        $this->SendMensaje($binary ? PHPWebSocket::WS_OPCODE_BINARY : PHPWebSocket::WS_OPCODE_TEXT, $message);
    }

    /**
     * 
     * cierra la coneccion de el cliente 
     */
    public function Close()
    {
        return $this->SendClose(PHPWebSocket::WS_STATUS_NORMAL_CLOSE);
    }

    /**
     * @access private
     * @param type $time
     */
    public function SetPingSentTime($time)
    {
        $this->PingSentTime = $time;
    }

    /**
     * @access private
     * @param type $type
     * @param type $func
     */
    function bind($type, $func)
    {
        if(!isset($this->wsOnEvents[$type]))
            $this->wsOnEvents[$type] = array();
        $this->wsOnEvents[$type][] = $func;
    }

    /**
     * 
     * @param type $type
     * @access private 
     */
    function unbind($type = '')
    {
        if($type)
            unset($this->wsOnEvents[$type]);
        else
            $this->wsOnEvents = array();
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
                        $class->$method($this, ...$agrs);
                    } else
                    {
                        $class::$method($this, ...$agrs);
                    }
                } else
                {
                    $func($this, ...$agrs);
                }
            }
        }
    }

    /**
     * envia un ping al cliente 
     */
    public function Ping()
    {
        $this->SetPingSentTime(time());
        $this->SendMensaje(PHPWebSocket::WS_OPCODE_PING, '');
    }

    /**
     * @ignore
     * @param type $opcode
     * @param type $message
     * @return boolean
     */
    public function SendMensaje($opcode, $message)
    {
        if(!$this->headerSend)
        {
            $this->SendHeader();
        }
        // check if client ready state is already closing or closed
        if($this->ReadyState == PHPWebSocket::WS_READY_STATE_CLOSING || $this->ReadyState == PHPWebSocket::WS_READY_STATE_CLOSED)
            return true;

        // fetch message length
        $messageLength = strlen($message);

        // set max payload length per frame
        $bufferSize = 4096;

        // work out amount of frames to send, based on $bufferSize
        $frameCount = ceil($messageLength / $bufferSize);
        if($frameCount == 0)
            $frameCount = 1;

        // set last frame variables
        $maxFrame = $frameCount - 1;
        $lastFrameBufferLength = ($messageLength % $bufferSize) != 0 ? ($messageLength % $bufferSize) : ($messageLength != 0 ? $bufferSize : 0);

        // loop around all frames to send
        for($i = 0; $i < $frameCount; $i++)
        {
            // fetch fin, opcode and buffer length for frame
            $fin = $i != $maxFrame ? 0 : PHPWebSocket::WS_FIN;
            $opcode = $i != 0 ? PHPWebSocket::WS_OPCODE_CONTINUATION : $opcode;

            $bufferLength = $i != $maxFrame ? $bufferSize : $lastFrameBufferLength;

            // set payload length variables for frame
            if($bufferLength <= 125)
            {
                $payloadLength = $bufferLength;
                $payloadLengthExtended = '';
                $payloadLengthExtendedLength = 0;
            } elseif($bufferLength <= 65535)
            {
                $payloadLength = PHPWebSocket::WS_PAYLOAD_LENGTH_16;
                $payloadLengthExtended = pack('n', $bufferLength);
                $payloadLengthExtendedLength = 2;
            } else
            {
                $payloadLength = PHPWebSocket::WS_PAYLOAD_LENGTH_63;
                $payloadLengthExtended = pack('xxxxN', $bufferLength); // pack 32 bit int, should really be 64 bit int
                $payloadLengthExtendedLength = 8;
            }

            // set frame bytes
            $buffer = pack('n', (($fin | $opcode) << 8) | $payloadLength) . $payloadLengthExtended . substr($message, $i * $bufferSize, $bufferLength);

            // send frame
            $socket = $this->Socket;

            $left = 2 + $payloadLengthExtendedLength + $bufferLength;
            do
            {
                $sent = @socket_send($socket, $buffer, $left, 0);
                if($sent === false)
                    return false;

                $left -= $sent;
                if($sent > 0)
                    $buffer = substr($buffer, $sent);
            }
            while($left > 0);
        }

        return true;
    }

    /**
     * @ignore
     * @param type $status
     * @return boolean
     */
    public function SendClose($status = false)
    {

        // check if client ready state is already closing or closed
        if($this->ReadyState == PHPWebSocket::WS_READY_STATE_CLOSING || $this->ReadyState == PHPWebSocket::WS_READY_STATE_CLOSED)
            return true;

        // store close status
        $this->CloseStatus = $status;

        // send close frame to client
        $status = $status !== false ? pack('n', $status) : '';
        $this->SendMensaje(PHPWebSocket::WS_OPCODE_CLOSE, $status);

        // set client ready state to closing
        $this->ReadyState = PHPWebSocket::WS_READY_STATE_CLOSING;
    }

    /**
     * @ignore
     * @param type $buffer
     * @return boolean
     */
    public function ProcessHandshake(&$buffer)
    {

        // fetch headers and request line
        $sep = strpos($buffer, "\r\n\r\n");
        if(!$sep)
            return false;

        $headers = explode("\r\n", substr($buffer, 0, $sep));
        $headersCount = sizeof($headers); // includes request line
        if($headersCount < 1)
            return false;

        // fetch request and check it has at least 3 parts (space tokens)
        $request = &$headers[0];
        $requestParts = explode(' ', $request);
        $requestPartsSize = sizeof($requestParts);
        if($requestPartsSize < 3)
            return false;

        // check request method is GET
        if(strtoupper($requestParts[0]) != 'GET')
            return false;

        // check request HTTP version is at least 1.1
        $exp = explode('?', isset($requestParts[1]) ? $requestParts[1] : '');
        $this->RequestDirname = $exp[0];
        unset($exp[0]);

        $this->Query_string = implode('?', $exp);


        $httpPart = &$requestParts[$requestPartsSize - 1];
        $httpParts = explode('/', $httpPart);
        if(!isset($httpParts[1]) || (float) $httpParts[1] < 1.1)
            return false;

        // store headers into a keyed array: array[headerKey] = headerValue
        $headersKeyed = array();
        for($i = 1; $i < $headersCount; $i++)
        {
            $parts = explode(':', $headers[$i]);
            if(!isset($parts[1]))
                return false;

            $headersKeyed[trim($parts[0])] = trim($parts[1]);
        }
        $this->HeadersRequest = $headersKeyed;
        // check Host header was received
        if(!isset($headersKeyed['Host']))
            return false;

        // check Sec-WebSocket-Key header was received and decoded value length is 16
        if(!isset($headersKeyed['Sec-WebSocket-Key']))
            return false;
        $key = $headersKeyed['Sec-WebSocket-Key'];
        if(strlen(base64_decode($key)) != 16)
            return false;

        // check Sec-WebSocket-Version header was received and value is 7
        if(!isset($headersKeyed['Sec-WebSocket-Version']) || (int) $headersKeyed['Sec-WebSocket-Version'] < 7)
            return false; // should really be != 7, but Firefox 7 beta users send 8







            
// work out hash to use in Sec-WebSocket-Accept reply header
        $hash = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));

        // build headers
        $headers = array(
            'HTTP/1.1 101 Switching Protocols',
            'Upgrade: websocket',
            'Connection: Upgrade',
            'Sec-WebSocket-Accept: ' . $hash
        );
        $this->HeaderResponse = $headers;

        RETURN true;
    }

    /**
     * AGREGA UNA CABECERA DE RESPUESTA SOLO TENDRA EFECTO EN EL EVENTO OnOpen
     * @param string $header
     */
    public function Header($header)
    {
        array_push($this->HeaderResponse, $header);
    }

    /**
     * @ignore
     * @return boolean
     */
    public function SendHeader()
    {
        if($this->headerSend)
            return;
        $this->headerSend = true;
        $headers = implode("\r\n", $this->HeaderResponse) . "\r\n\r\n";
        $left = strlen($headers) . "\r\n";
        do
        {
            $sent = @socket_send($this->Socket, $headers, $left, 0);
            if($sent === false)
                return false;

            $left -= $sent;
            if($sent > 0)
                $headers = substr($headers, $sent);
        } while($left > 0);
    }

    /**
     * @ignore
     * @param type $buffer
     * @param type $bufferLength
     * @return boolean
     */
    public function Process(&$buffer, $bufferLength)
    {
        if($this->ReadyState == PHPWebSocket::WS_READY_STATE_OPEN)
        {
            // handshake completed
            $result = $this->BuildFrame($buffer, $bufferLength);
        } elseif($this->ReadyState == PHPWebSocket::WS_READY_STATE_CONNECTING)
        {

            // handshake not completed
            $result = $this->ProcessHandshake($buffer);
            if($result)
            {
                $this->ReadyState = PHPWebSocket::WS_READY_STATE_OPEN;
                $this->CallEvent('open');
                $this->SendHeader();
            }
        } else
        {

            // ready state is set to closed
            $result = false;
        }

        return $result;
    }

    /**
     * @ignore
     * @return boolean
     */
    function ProcessFrame()
    {
        // store the time that data was last received from the client
        $this->LastRecvTime = time();

        // fetch frame buffer
        $buffer = &$this->FrameBuffer;

        // check at least 6 bytes are set (first 2 bytes and 4 bytes for the mask key)
        if(substr($buffer, 5, 1) === false)
            return false;

        // fetch first 2 bytes of header
        $octet0 = ord(substr($buffer, 0, 1));
        $octet1 = ord(substr($buffer, 1, 1));

        $fin = $octet0 & PHPWebSocket::WS_FIN;
        $opcode = $octet0 & 15;

        $mask = $octet1 & PHPWebSocket::WS_MASK;
        if(!$mask)
            return false; // close socket, as no mask bit was sent from the client








            
// fetch byte position where the mask key starts
        $seek = $this->FramePayloadDataLength <= 125 ? 2 : ($this->FramePayloadDataLength <= 65535 ? 4 : 10);

        // read mask key
        $maskKey = substr($buffer, $seek, 4);

        $array = unpack('Na', $maskKey);
        $maskKey = $array['a'];
        $maskKey = array(
            $maskKey >> 24,
            ($maskKey >> 16) & 255,
            ($maskKey >> 8) & 255,
            $maskKey & 255
        );
        $seek += 4;

        // decode payload data
        if(substr($buffer, $seek, 1) !== false)
        {
            $data = str_split(substr($buffer, $seek));
            foreach($data as $key => $byte)
            {
                $data[$key] = chr(ord($byte) ^ ($maskKey[$key % 4]));
            }
            $data = implode('', $data);
        } else
        {
            $data = '';
        }

        // check if this is not a continuation frame and if there is already data in the message buffer
        if($opcode != PHPWebSocket::WS_OPCODE_CONTINUATION && $this->MessageBufferLength > 0)
        {
            // clear the message buffer
            $this->MessageBufferLength = 0;
            $this->MensajeBuffer = '';
        }

        // check if the frame is marked as the final frame in the message
        if($fin == PHPWebSocket::WS_FIN)
        {
            // check if this is the first frame in the message
            if($opcode != PHPWebSocket::WS_OPCODE_CONTINUATION)
            {
                // process the message
                return $this->ProcessMensaje($opcode, $data, $this->FramePayloadDataLength);
            } else
            {
                // increase message payload data length
                $this->MessageBufferLength += $this->FramePayloadDataLength;

                // push frame payload data onto message buffer
                $this->MensajeBuffer .= $data;

                // process the message
                $result = $this->ProcessMensaje($this->MessageOpcode, $this->MensajeBuffer, $this->MessageBufferLength);

                // check if the client wasn't removed, then reset message buffer and message opcode
                if($this->active)
                {
                    $this->MensajeBuffer = '';
                    $this->MessageOpcode = 0;
                    $this->MessageBufferLength = 0;
                }

                return $result;
            }
        } else
        {
            // check if the frame is a control frame, control frames cannot be fragmented
            if($opcode & 8)
                return false;

            // increase message payload data length
            $this->wsClients[$clientID][11] += $this->wsClients[$clientID][7];

            // push frame payload data onto message buffer
            $this->wsClients[$clientID][1] .= $data;

            // if this is the first frame in the message, store the opcode
            if($opcode != PHPWebSocket::WS_OPCODE_CONTINUATION)
            {
                $this->wsClients[$clientID][10] = $opcode;
            }
        }

        return true;
    }

    /**
     * @ignore
     * @param type $buffer
     * @param type $bufferLength
     * @return boolean
     */
    public function BuildFrame(&$buffer, $bufferLength)
    {
        // increase number of bytes read for the frame, and join buffer onto end of the frame buffer
        $this->FrameBytesRead += $bufferLength;
        $this->FrameBuffer .= $buffer;

        // check if the length of the frame's payload data has been fetched, if not then attempt to fetch it from the frame buffer
        if($this->FramePayloadDataLength !== false || $this->CheckSizeFrame() == true)
        {
            // work out the header length of the frame
            $headerLength = ($this->FramePayloadDataLength <= 125 ? 0 : ($this->FramePayloadDataLength <= 65535 ? 2 : 8)) + 6;

            // check if all bytes have been received for the frame
            $frameLength = $this->FramePayloadDataLength + $headerLength;
            if($this->FrameBytesRead >= $frameLength)
            {
                // check if too many bytes have been read for the frame (they are part of the next frame)
                $nextFrameBytesLength = $this->FrameBytesRead - $frameLength;
                if($nextFrameBytesLength > 0)
                {
                    $this->FrameBytesRead -= $nextFrameBytesLength;
                    $nextFrameBytes = substr($this->FrameBuffer, $frameLength);
                    $this->FrameBuffer = substr($this->FrameBuffer, 0, $frameLength);
                }

                // process the frame
                $result = $this->ProcessFrame();

                // check if the client wasn't removed, then reset frame data
                if($this->active)
                {
                    $this->FramePayloadDataLength = false;
                    $this->FrameBytesRead = 0;
                    $this->FrameBuffer = '';
                }

                // if there's no extra bytes for the next frame, or processing the frame failed, return the result of processing the frame
                if($nextFrameBytesLength <= 0 || !$result)
                    return $result;

                // build the next frame with the extra bytes
                return $this->BuildFrame($nextFrameBytes, $nextFrameBytesLength);
            }
        }

        return true;
    }

    /**
     * @ignore
     * @return boolean
     */
    public function CheckSizeFrame()
    {
        $clientID = $this->ID;
        // check if at least 2 bytes have been stored in the frame buffer
        if($this->FrameBytesRead > 1)
        {
            //9 fetch payload length in byte 2, max will be 127
            $payloadLength = ord(substr($this->FrameBuffer, 1, 1)) & 127;

            if($payloadLength <= 125)
            {
                //7 actual payload length is <= 125
                $this->FramePayloadDataLength = $payloadLength;
            } elseif($payloadLength == 126)
            {
                // actual payload length is <= 65,535
                if(substr($this->FrameBuffer, 3, 1) !== false)
                {
                    // at least another 2 bytes are set
                    $payloadLengthExtended = substr($this->FrameBuffer, 2, 2);
                    $array = unpack('na', $payloadLengthExtended);
                    $this->FramePayloadDataLength = $array['a'];
                }
            } else
            {
                // actual payload length is > 65,535
                if(substr($this->FrameBuffer, 9, 1) !== false)
                {
                    //9 at least another 8 bytes are set
                    $payloadLengthExtended = substr($this->FrameBuffer, 2, 8);

                    // check if the frame's payload data length exceeds 2,147,483,647 (31 bits)
                    // the maximum integer in PHP is "usually" this number. More info: http://php.net/manual/en/language.types.integer.php
                    $payloadLengthExtended32_1 = substr($payloadLengthExtended, 0, 4);
                    $array = unpack('Na', $payloadLengthExtended32_1);
                    if($array['a'] != 0 || ord(substr($payloadLengthExtended, 4, 1)) & 128)
                    {
                        $this->SendClose(PHPWebSocket::WS_STATUS_MESSAGE_TOO_BIG);
                        return false;
                    }

                    // fetch length as 32 bit unsigned integer, not as 64 bit
                    $payloadLengthExtended32_2 = substr($payloadLengthExtended, 4, 4);
                    $array = unpack('Na', $payloadLengthExtended32_2);

                    // check if the payload data length exceeds 2,147,479,538 (2,147,483,647 - 14 - 4095)
                    // 14 for header size, 4095 for last recv() next frame bytes
                    if($array['a'] > 2147479538)
                    {
                        $this->SendClose(PHPWebSocket::WS_STATUS_MESSAGE_TOO_BIG);
                        return false;
                    }

                    // store frame payload data length
                    $this->FramePayloadDataLength = $array['a'];
                }
            }

            // check if the frame's payload data length has now been stored
            if($this->FramePayloadDataLength !== false)
            {

                // check if the frame's payload data length exceeds self::WS_MAX_FRAME_PAYLOAD_RECV
                if($this->FramePayloadDataLength > PHPWebSocket::WS_MAX_FRAME_PAYLOAD_RECV)
                {
                    $this->FramePayloadDataLength = false;
                    $this->SendClose(PHPWebSocket::WS_STATUS_MESSAGE_TOO_BIG);
                    return false;
                }

                // check if the message's payload data length exceeds 2,147,483,647 or self::WS_MAX_MESSAGE_PAYLOAD_RECV
                // doesn't apply for control frames, where the payload data is not internally stored
                $controlFrame = (ord(substr($this->FrameBuffer, 0, 1)) & 8) == 8;
                if(!$controlFrame)
                {
                    $newMessagePayloadLength = $this->MessageBufferLength + $this->FramePayloadDataLength;
                    if($newMessagePayloadLength > PHPWebSocket::WS_MAX_MESSAGE_PAYLOAD_RECV || $newMessagePayloadLength > 2147483647)
                    {
                        $this->SendClose($clientID, PHPWebSocket::WS_STATUS_MESSAGE_TOO_BIG);
                        return false;
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @ignore
     * @param type $opcode
     * @param type $data
     * @param type $dataLength
     * @return boolean
     */
    public function ProcessMensaje($opcode, &$data, $dataLength)
    {
        // check opcodes

        if($opcode == PHPWebSocket::WS_OPCODE_PING)
        {
            // received ping message
            return $this->SendMensaje(PHPWebSocket::WS_OPCODE_PONG, $data);
        } elseif($opcode == PHPWebSocket::WS_OPCODE_PONG)
        {
            // received pong message (it's valid if the server did not send a ping request for this pong message)
            if($this->PingSentTime !== false)
            {
                $this->PingSentTime = false;
            }
        } elseif($opcode == PHPWebSocket::WS_OPCODE_CLOSE)
        {
            // received close message
            if(substr($data, 1, 1) !== false)
            {
                $array = unpack('na', substr($data, 0, 2));
                $status = $array['a'];
            } else
            {
                $status = false;
            }

            if($this->ReadyState == PHPWebSocket::WS_READY_STATE_CLOSING)
            {
                // the server already sent a close frame to the client, this is the client's close frame reply
                // (no need to send another close frame to the client)
                $this->ReadyState = PHPWebSocket::WS_READY_STATE_CLOSED;
            } else
            {
                // the server has not already sent a close frame to the client, send one now
                $this->SendClose(PHPWebSocket::WS_STATUS_NORMAL_CLOSE);
            }

            $this->Remove();
        } elseif($opcode == PHPWebSocket::WS_OPCODE_TEXT || $opcode == PHPWebSocket::WS_OPCODE_BINARY)
        {
            $this->CallEvent('message', [$data, $dataLength, $opcode == PHPWebSocket::WS_OPCODE_BINARY]);
        } else
        {
            // unknown opcode
            return false;
        }

        return true;
    }

    /**
     * cierra la coneccion del cliente y lo remueve del servidor
     */
    public function Remove()
    {
        // fetch close status (which could be false), and call wsOnClose
        $closeStatus = $this->CloseStatus;
        $this->CallEvent('close', [$closeStatus]);


        // close socket
        $socket = $this->Socket;
        socket_close($socket);

        // decrease amount of clients connected on this client's IP
        $clientIP = $this->IPv4;
        if($this->CountIp[$clientIP] > 1)
        {
            $this->CountIp[$clientIP] --;
        } else
        {
            unset($this->CountIp[$clientIP]);
        }
        $this->active = false;
        $this->CallEvent('remove', [$closeStatus]);
        unset($this);
    }

}
