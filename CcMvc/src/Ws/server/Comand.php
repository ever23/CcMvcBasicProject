<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cc\Ws;

/**
 * Description of Console
 *
 * @author usuario
 * @package CcWs
 */
class Comand
{

    private $in;
    private $out;
    private $buffer;
    protected $comandos;

    public function __construct()
    {
        $this->in = STDIN;
        $this->out = STDOUT;
        $this->comandos = [];
    }

    public function AddComand($name, callable $call)
    {
        $this->comandos[strtolower($name)] = $call;
    }

    public function Read()
    {

        $buff = fgets($this->in);
        //var_export($buff);

        $this->ProcessComand($buff);


        $this->buffer.=$buff;
        return $buff;
        // fputs($this->out, $buff); 
    }

    public function Write($string)
    {
        fputs($this->out, $string);
    }

    public function getBuffer()
    {
        return $this->buffer;
    }

    protected function ClearBuffer()
    {
        $this->buffer = '';
    }

    public function ProcessComand($comand)
    {

        if(isset($this->comandos[strtolower($comand)]))
        {

            $c = $this->comandos[strtolower($comand)];
            $c($this);
        }
    }

}
