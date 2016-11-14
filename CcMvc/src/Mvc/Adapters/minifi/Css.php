<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cc\Mvc\Minify;

/**
 * Description of Css
 *
 * @author usuario
 */
class CSS extends \MatthiasMullie\Minify\CSS
{

    protected $importExtensions = array(
    );

    public function addContext($data, $contex)
    {
        // redefine var
        $data = (string) $data;
        $contex = (string) $contex;
        // load data
        $value = $this->load($data);
        $key = ($data != $value) ? $data : count($this->data);

        // replace CR linefeeds etc.
        // @see https://github.com/matthiasmullie/minify/pull/139
        $value = str_replace(array("\r\n", "\r"), "\n", $value);

        // store data

        $this->data[$contex] = $value;
    }

}
