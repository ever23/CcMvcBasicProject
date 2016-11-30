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
 * Maneja los layauts 
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc  
 * @subpackage Controladores 
 */
class LayautManager extends Model
{

    protected $DirLayaut = NULL;
    protected $layaut = NULL;
    protected $Buffer = [];
    private $CallBack;
    protected static $instance;
    public $Obj;

    /**
     * @access private
     */
    public function __construct()
    {


        $LayautController = &$this;

        $this->CallBack = function($contexLayaut = true)use (&$LayautController)
        {
            Mvc::App()->Log("Verificando layaut  ...");
            try
            {
                /* @var $LayautController LayautManager */
                if ($LayautController->GetNivel() == 1 && $LayautController->Obj instanceof iLayaut)
                {
                    $layaut = $this->GetLayaut();
                    MvcEvents::TingerAndDependence("LayautController");
                } else
                {
                    $layaut = $LayautController->GetLayaut();
                }
            } catch (Exception $ex)
            {
                throw $ex;
            } catch (\Error $ex)
            {
                throw $ex;
            }


            if (is_null($layaut['Layaut']) || $layaut['Layaut'] == '')
                return;

            $__name = ($layaut['Dir'] . $layaut['Layaut']);
            if (!file_exists($__name))
            {
                $__name.='.php';
            }
            if ((strpos($layaut['Layaut'], ':') !== false))
            {
                $__name.=$layaut['Layaut'];
            }

            try
            {
                $param = ['content' => DocumentBuffer::Conten()] + $LayautController->jsonSerialize();
                if (isset($layaut['params']))
                {
                    $param+=$layaut['params'];
                }


                DocumentBuffer::Clear();
                $loader = new TemplateLoad(Mvc::App()->Config());
                $loader->Load($this, $__name, $param);
                Mvc::App()->Log("LAYAUT " . $__name . " CARGADO  ...");
            } catch (LayautException $ex)
            {
                throw $ex;
            } catch (TemplateException $ex)
            {
                throw new LayautException("EL LAYAUT " . $__name . " NO EXISTE ");
            } catch (Exception $ex)
            {
                throw $ex;
            }
        };
    }

    public function GetNivel()
    {
        return count($this->Buffer);
    }

    /**
     * 
     * @param type $str
     * @return type
     * @internal
     */
    public function HandleBuffer($str)
    {
        return $str;
    }

    /**
     * @internal 
     */
    public function LoadLayaut()
    {
        $function = \Closure::bind($this->CallBack, $this->Obj, get_class($this->Obj));
        $function();
    }

    /**
     * 
     * @internal 
     */
    public function GetLayaut()
    {
        if (is_null($this->DirLayaut))
        {
            $this->DirLayaut = Mvc::App()->Config()->App['layauts'];
        }
        return ['Layaut' => $this->layaut, 'Dir' => $this->DirLayaut];
    }

    /**
     * carga un archivo layaut e inicia la buferizacion para capturar el contenido
     * que se imprima y enbolverlo eb el layaut
     * <code><?php
     * use Cc\Mvc\LayautManager;
     * $Layaut = LayautManager::BeginConten('sitie');?>
     * texto html
     * <?php echo $content ?>
     * texto html
     * <?php $Layaut->EndConten();
     * </code>
     * @param string $name
     * @param string $dir
     * @return this
     * @see EndConten
     */
    public static function &BeginConten($name, $dir = NULL)
    {
        if (!self::$instance)
        {
            self::$instance = new self();
        }
        self::$instance->Buffer[] = new DocumentBuffer([self::$instance, 'HandleBuffer'], false, false);
        if ($name instanceof iLayaut && count(self::$instance->Buffer) == 1)
        {
            self::$instance->Obj = &$name;
        } else
        {
            self::$instance->SetLayaut($name, $dir);
        }

        return self::$instance;
    }

    /**
     * FINALIZA LA BUFERIZACION E IMPRIME EL TEXTO PROCESADO
     */
    public function EndConten()
    {
        $this->LoadLayaut();
        $buffer = array_pop($this->Buffer);
        if ($buffer instanceof DocumentBuffer)
            $buffer->EndConten();
    }

    /**
     * @internal se usa en Mvc para cargar el layaut
     */
    public static function ClousureLayaut(&$obj = NULL)
    {
        if (!self::$instance)
        {
            self::$instance = new self();
        }

        self::$instance->Obj = &$obj;
        $function = \Closure::bind(self::$instance->CallBack, $obj, get_class($obj));
        $function(false);
    }

    /**
     * 
     * @param string $file
     * @param string $dirLayaut
     */
    private function SetLayaut($file, $dirLayaut = NULL)
    {
        $this->layaut = ValidFilename::ValidName($file, true);
        if ($dirLayaut)
        {
            $this->DirLayaut = $dirLayaut;
        }
    }

    protected function Campos()
    {
        return [];
    }

}

interface iLayaut
{

    /**
     * ESTE METODO ESTABLECERA EL LAYAUT (ENVOLTURA DE DISEÑO) EN LA RESPUESTA DONDE SE IMPRIMIRA EL CONTENIDO DEL BUFFER
     * 
     * @param string|NULL $layaut SI ES NULL EL LAYAUT DEVE SER DESACTIVADO LO QUE QUIERE DECIR QUE EL CONTENIDO DEL BUFFER SERA IMPRIMIDO SIN EL LAYAUT 
     * @param string $dirLayaut DIRECTORIO DONDE SE ENCUENTRAN LOS LAYAUTS SE DEVE ALMACENAR EL ANTERIOR PARA CUANDO SEA NULL SE UTILISE EL ANTERIOR
     * <code>
     *  public function SetLayaut($file,$dirLayaut=NULL)
     * {
     *      $this->layaut = $file;
     *      if($dirLayaut)
     *      {
     *          $this->DirLayaut=$dirLayaut;
     *      }
     * }
     * </code>
     */
    public function SetLayaut($layaut, $dirLayaut = NULL);

    /**
     * DEBERIA RETORNAR UN ARRAY CON DOS INDICES Layaut QUE CONTENDRA EL LAYAUT QUE SE USARA  Y Dir DIRECTORIO DEL LAYAUT
     * <code>
     *  public function GetLayaut()
     * {
     *   return ['Layaut' => $this->layaut, 'Dir' => $this->DirLayaut];
     * }
     * </code>
     * @return array 
     */
    public function GetLayaut();
}

class LayautException extends \Exception
{
    
}
