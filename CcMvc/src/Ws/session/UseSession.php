<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cc\Ws;

/**
 * interface para ser implementada en un clase de evento 
 * esta interface permite usar las sessiones del usuario
 * @autor ENYREBER FRANCO       <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                    
 * @package CcWs
 * @subpackage Session 
 */
interface UseSession
{

    /**
     * @return array 
     */
    public function SessionParams();
}
