<?php

namespace Cc\Ws;

/**
 * representara un mensaje recibido por el cliente 
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>      
 * @package CcWs
 * @subpackage Request
 */
class Messaje
{

    public $messaje;
    public $messageLength;
    public $binary;

    /**
     * 
     * @param string $messaje 
     * @param int $messageLength
     * @param boolean $binary
     */
    public function __construct($messaje, $messageLength = NULL, $binary = NULL)
    {
        $this->messageLength = $messageLength;
        $this->messaje = $messaje;
        $this->binary = $binary;
    }

    /**
     * 
     * @return string el mesaje recibido 
     */
    public function __toString()
    {
        return $this->messaje;
    }

}
