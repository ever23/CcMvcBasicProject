<?php

namespace Cc\Ws;

use Cc\Json;

/**
 * representara un mensaje recibido por el cliente en formato json
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>      
 * @package CcWs
 * @subpackage Request 
 */
class MessajeJson extends Json
{

    public $messageLength;
    public $binary;

    /**
     * 
     * @param string $messaje 
     * @param int $messageLength
     * @param boolean $binary
     */
    public function __construct($messaje = '', $messageLength = NULL, $binary = NULL)
    {
        if (!is_null($messageLength))
        {
            $this->messageLength = $messaje;
        } else
        {
            unset($this->messageLength);
        }
        if (!is_null($binary))
        {
            $this->binary = $binary;
        } else
        {
            unset($this->binary);
        }
        if (!is_string($messaje))
        {
            parent::__construct();
            $this->CreateJson($messaje, true);
        } else
        {
            parent::__construct($messaje);
        }
    }

}
