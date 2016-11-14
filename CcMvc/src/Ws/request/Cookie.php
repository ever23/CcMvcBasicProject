<?php

namespace Cc\Ws;

use Cc\Config;

/**
 * representa la cookie que es recibida del cliente cuando este se conecta 
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>      
 * @package CcWs
 * @subpackage Request 
 */
class Cookie extends \Cc\Cookie implements \JsonSerializable
{

    private $client;
    private $CookieParam = [];

    /**
     * 
     * @param Config $conf
     * @param WsClient $client
     */
    public function __construct(Config $conf, WsClient &$client)
    {
        $this->host = $conf['host'];
        if(isset($conf['protocol']))
            $this->secure = $conf['protocol'] == 'https';
        $this->httponly = false;
        $this->client = $client;
        $cookie = isset($client->HeadersRequest['Cookie']) ? $client->HeadersRequest['Cookie'] : '';
        $cookie = str_replace('; ', '&', $cookie);
        parse_str(urldecode($cookie), $this->Cookie);
    }

    public function __destruct()
    {
        //parent::__destruct();
        unset($this->CookieParam);
        unset($this->client);
        unset($this->Cookie);
    }

    /**
     * @ignore
     */
    public function SendHeader()
    {
        $heder = '';
        $time = time();
        foreach($this->CookieParam as $i => $v)
        {
            $heder = 'Set-Cookie: ' . $i . '=' . urlencode($v['value']);
            if(!is_null($v['expire']))
                $heder.='; expires=Mon, ' . date('d-M-Y H:i:s', $time + $v['expire']);
            if(!is_null($v['path']))
                $heder.='; path=' . $v['path'];
            if(!is_null($v['dominio']))
                $heder.= '; domain=' . $v['dominio'];
            $heder.= '; ' . ($v['secure'] ? 'secure' : '') . '';
            $this->client->Header($heder);
        }
    }

    public function jsonSerialize()
    {
        return $this->Cookie;
    }

    protected function SaveCookie($name, $value, $expire = NULL, $path = NULL, $dominio = NULL, $secure = NULL, $httponly = NULL)
    {
        $this->Cookie[$name] = $value;
        $this->CookieParam[$name] = [ 'value' => $value, 'expire' => $expire, 'path' => $path, 'dominio' => $dominio, 'secure' => $secure, 'httponly' => $httponly];
    }

}
