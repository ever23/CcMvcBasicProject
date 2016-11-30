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
 *
 *  
 */

namespace Cc\Mvc\Twig\Extencion;

use Cc\Mvc;

/**
 * extencion CcMvc para Twig
 *
 * @author Enyerber Franco
 * @package CcMvc
 * @subpackage Adapters
 */
class CcMvc implements \Twig_ExtensionInterface, \Twig_Extension_GlobalsInterface
{

    public function getGlobals()
    {

        return [
            'CcMvc' => Mvc::App(),
            'ResponseConten' => Mvc::App()->Response,
            'Request' => Mvc::App()->Request,
            'Controller' => Mvc::App()->SelectorController->GetController()
        ];
    }

    public function getFilters()
    {
        return [];
    }

    public function getFunctions()
    {
        return [
                // new \Twig_SimpleFunction('isset', 'isset')
        ];
    }

    public function getName()
    {
        return 'CcMvc';
    }

    public function getNodeVisitors()
    {
        return [];
    }

    public function getOperators()
    {
        return [];
    }

    public function getTests()
    {
        return [];
    }

    public function getTokenParsers()
    {
        return [];
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        
    }

}
