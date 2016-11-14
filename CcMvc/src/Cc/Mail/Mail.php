<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cc;

/**
 * clase encargada de enviar mails
 *
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com> 
 *     
 * @package Cc
 * @subpackage Mail
 * @todo Se nesesita merorar el envio de mails
 * 
 */
class Mail
{

    protected $config;
    protected $Destinatarios = [];
    protected $Title = '';
    protected $messaje = '';
    protected $header = [];

    public function __construct(Config $conf)
    {
        $this->config = $conf;
        $this->Header('From', $conf->WebMaster['email']);
        $this->Header('Reply-To', $conf->WebMaster['email']);
    }

    public function Destino($mail)
    {
        if (is_array($mail))
        {
            foreach ($mail as $v)
            {
                if (filter_var($v, FILTER_VALIDATE_EMAIL))
                    $this->Destinatarios[] = $v;
            }
        } else
        {
            if (filter_var($mail, FILTER_VALIDATE_EMAIL))
                $this->Destinatarios[] = $mail;
        }
    }

    public function Titulo($title)
    {
        $this->Title = $title;
    }

    public function Mensaje($mensaje)
    {
        $this->messaje = $mensaje;
    }

    public function Header($name, $header)
    {
        $this->header[$name] = $header;
    }

    public function Send()
    {
        return mail($this->getDestinos(), $this->Title, $this->getMessaje(), $this->getHeaders());
    }

    private function getMessaje()
    {
        return wordwrap($this->messaje, 70, "\r\n");
    }

    private function getHeaders()
    {
        $h = '';
        foreach ($this->header as $name => $header)
        {
            $h .= $name . ': ' . $header . "\r\n";
        }
        return $h;
    }

    private function getDestinos()
    {
        $b = '';
        foreach ($this->Destinatarios as $destino)
        {
            $b.='<' . $destino . '> ,';
        }
        return substr($b, 0, -1);
    }

}
