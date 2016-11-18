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

use Cc\Mvc;

/**
 * CLASE DE RESPUESTA PARA TEXTO EN FORMATO JSON 
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                                    
 * @package CcMvc
 * @subpackage Response
 * @uses DocumentBuffer
 * @uses \Cc\Json             
 */
class Json extends \Cc\Json implements ResponseConten
{

    private $textJsonBuffer = 'TextBufer';

    public static function CtorParam()
    {
        Mvc::App()->ChangeResponseConten('application/json');
        return Mvc::App()->Response;
    }

    public function __construct($compress = [])
    {

        if (is_bool($compress))
        {
            Mvc::App()->Buffer->SetCompres($compress);
            parent::__construct();
        } else
        {
            parent::__construct($compress);
        }
    }

    /**
     * @access private
     * @param type $string
     * @return type
     */
    public function ProccessConten($string)
    {

        if ($string != "")
            $this->Set($this->textJsonBuffer, $string);

        return $this;
    }

    /**
     * @ignore
     */
    public function DefineVarBuffer($tex)
    {
        $this->textJsonBuffer = $tex;
    }

    /**
     * @ignore
     */
    public function SetLayaut($layaut, $dirLayaut = NULL)
    {
        $this->textJsonBuffer = $layaut;
    }

    /**
     * @ignore
     */
    public function GetLayaut()
    {
        $this->Header();
        return ['Layaut' => NULL, 'Dir' => ''];
    }

}
