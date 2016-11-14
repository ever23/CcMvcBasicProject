<?php

/*
 * Copyright (C) 2016 Enyerber Franco
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Cc\Mvc;

/**
 * Description of TestController
 *
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc  
 * @subpackage Controladores
 * @todo SE NESESITA MEJORAR LOS TEST 
 */
class TestController extends Controllers
{

    /**
     *
     * @var MapingControllers 
     */
    protected $controladores;

    public function __construct(Config $c)
    {
        $this->controladores = new MapingControllers($c);
        $this->view->controladores = $this->controladores->GetControllers();
        $this->view->config = $c;
    }

    public function index()
    {
        $this->view->LoadInternalView('TestController/');
    }

}
