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
use Cc\Inyectable;

/**
 * interface que deven implementar todas las clases manejaoras de respuesta
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                                    
 * @package CcMvc
 * @subpackage Response
 */
interface ResponseConten extends iLayaut, Inyectable
{

    /**
     * PROCESARA EL TEXTO QUE RECIBA Y RETORNARA EL RESULTADO 
     * 
     * @param string $str
     * @return string 
     */
    public function ProccessConten($str);
}

/**
 * TRAIT PARA USAR EN CLASES QUE SERAN DESTINADA PARA RESPUESTA DE CcMvc
 * DEFINE TODOS LOS METODS QUE NESESITA {@link ResponseConten}
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                                    
 * @package CcMvc
 * @subpackage Response
 */
trait TResponseConten
{

    protected $DirLayaut = NULL;
    protected $layaut = NULL;

    public function SetLayaut($file, $dirLayaut = NULL)
    {
        if (is_null($file))
        {
            $this->layaut = NULL;
        } else
        {
            $this->layaut = ValidFilename::ValidName($file, true);
        }

        if ($dirLayaut)
        {
            $this->DirLayaut = $dirLayaut;
        }
    }

    public function ProccessConten($conten)
    {

        return $conten;
    }

    public function GetLayaut()
    {
        return ['Layaut' => $this->layaut, 'Dir' => $this->DirLayaut];
    }

}

/**
 * CLASE DE RESPUESTA POR DEFECTO
 *
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                                    
 * @package CcMvc
 * @subpackage Response
 */
class Response implements ResponseConten
{

    protected $DirLayaut = NULL;
    protected $layaut = NULL;
    protected $min;
    protected $typeMin;

    public static function CtorParam()
    {
        throw new Exception("No Deberia usar esta clase " . self::class . " para referenciar al objeto de respuesta "
        . "use la clase \\Cc\\Mvc\\ResponseConten");
    }

    public function __construct($compres = false, $min = false, $type = 'html')
    {
        $this->min = $min;
        $this->typeMin = $type;

        Mvc::App()->Buffer->SetCompres($compres);
        $this->layaut = NULL;
        $this->DirLayaut = NULL;
    }

    public function SetMin($min)
    {
        $this->min = $min;
    }

    /**
     * 
     * @param string $conten
     * @return string
     */
    public function ProccessConten($conten)
    {
        if ($this->min && !Mvc::App()->IsDebung())
        {
            Mvc::App()->Buffer->SetAutoMin(true);
            Mvc::App()->Buffer->SetTypeMin($this->typeMin);
        }

        return $conten;
    }

    /**
     * 
     * @return array
     */
    public function GetLayaut()
    {

        return ['Layaut' => $this->layaut, 'Dir' => $this->DirLayaut];
    }

    /**
     * 
     * @param string $file
     * @param string $dirLayaut
     */
    public function SetLayaut($file, $dirLayaut = NULL)
    {

        if (is_null($file))
        {
            $this->layaut = NULL;
        }
        $this->layaut = ValidFilename::ValidName($file, true);
        if ($dirLayaut)
        {
            $this->DirLayaut = $dirLayaut;
        }
    }

}
