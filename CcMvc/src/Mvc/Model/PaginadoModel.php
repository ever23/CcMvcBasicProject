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
use Cc\ResultManager;
use Cc\UrlManager;

/**
 * Description of PaginadoModel
 * Modelo de paginado puede ser recorrido con un foreach
 * @author Enyerber Franco
 * @package CcMvc
 * @subpackage Modelo
 */
class PaginadoModel extends Model implements \JsonSerializable
{

    /**
     * INICIO DEL PAGINADO
     * @var int  
     */
    protected $Ini = 0;

    /**
     * FIN DEL PAGINADO 
     * @var int 
     */
    protected $End = NULL;

    /**
     * INTERVALO DEL PAGINADO
     * @var int 
     */
    protected $interval = 1;

    /**
     * 
     * @var int 
     */
    protected $count = 0;

    /**
     *
     * @var string 
     */
    protected $str = NULL;

    /**
     *
     * @var string 
     */
    protected $nameIni = 'PMIni';
    protected $nameEnd = 'PMEnd';
    protected $GetVar = 'PM';
    protected static $num = 0;
    protected $PageActual = 0;

    /**
     * VALOR POR DEFECTO DEL ATRIBUTO href EN {@link NextLink} y {@link LastLink}
     * @var string 
     */
    public $href = NULL;
    protected $Pages = [];

    /**
     * 
     * @param \Traversable|array $val
     * @param int $interval
     * @param string|array $strForEach si es un string este sera tomado para imprimir en el el valor de cada fila en lugar de {{indice}}
     * si es un array este deve temer dos strings el idice 0 deve terner el nombre de la variable request que recivira el inicio de paginado y el segundo 
     * el fin del paginado
     */
    public function __construct($val, $interval = 1, $strForEach = NULL, $actual = NULL)
    {
        static::$num++;
        $this->GetVar.= static::$num;
        //Mvc::App()->Request[''];
        if (is_array($strForEach))
        {
            list($this->nameIni, $this->nameEnd) = $strForEach;
            $strForEach = NULL;
        }
        if (is_null($actual))
        {
            if (isset(Mvc::App()->Request[$this->GetVar]))
            {
                $actual = Mvc::App()->Request[$this->GetVar];
            } else
            {
                $actual = 0;
            }
        }
        $this->interval = $interval;
        $this->PageActual = $actual;


        /* if ($val instanceof ResultManager && !is_string($strForEach))
          {
          $this->_ValuesModel = clone $val;
          $this->count = $this->_ValuesModel->num_rows;
          $this->Pages = $this->ListPages();
          list($this->Ini, $this->End) = $this->Pages[$actual];
          var_dump($this->End + 1);
          $this->_ValuesModel->DataSeek($this->Ini);
          $this->_ValuesModel->SetEndResult($this->End + 1);
          } else
          { */
        if (is_array($val) || ($val instanceof \Traversable && $val instanceof \Countable))
        {
            $this->count = count($val);
            $this->Pages = $this->ListPages();
            list($this->Ini, $this->End) = isset($this->Pages[$actual]) ? $this->Pages[$actual] : [0, 0];

            $i = 0;
            foreach ($val as $v)
            {
                if ($i >= $this->Ini)
                {
                    $this->_ValuesModel[$i] = $v;
                    if (!is_null($strForEach))
                    {
                        $this->str.=$this->ParseStrForEach($v, $strForEach);
                    }
                }

                $i++;
                if ($i > $this->End)
                    break;
            }
        }
        /* } */
    }

    public function GetVarRequest()
    {
        return $this->GetVar;
    }

    public function ActualPage()
    {
        return $this->PageActual;
    }

    public function GetListPages()
    {
        return $this->Pages;
    }

    protected function ListPages()
    {
        $list = [];
        $ant = 0;
        for ($i = 0; $i < $this->count; $i+=$this->interval)
        {

            $list[] = [0 => $i, 1 => $i + $this->interval - 1];
        }
        /*  echo '<pre>';
          echo 'posotion ' . $this->PageActual . '<br>';
          var_dump($list);
          echo '</pre>'; */
        return $list;
    }

    public function jsonSerialize()
    {

        $next = $this->GetNext();
        if (!$next)
            $next = [$this->GetVar => false];
        $last = $this->GetLast();
        if (!$last)
            $last = [$this->GetVar => false];
        return ['ValueModel' => $this->_ValuesModel, 'next' => $next[$this->GetVar], 'last' => $last[$this->GetVar], 'var' => $this->GetVar];
    }

    public function ListLinkPages($attrs = [], $tang = 'li')
    {
        if (is_object($tang) && $tang instanceof \Smarty_Internal_Template)
        {
            $type = isset($attrs['tang']) ? $attrs['tang'] : 'li';
            unset($attrs['tang']);
        }
        $links = [];

        foreach ($this->Pages as $i => $v)
        {
            $attrs['href'] = isset($attrs['href']) ? $attrs['href'] : $this->href;
            $get = [$this->GetVar => $i];
            $links[] = Html::a($i, ['href' => UrlManager::Href($attrs['href'], $get)] + $attrs);
        }
        switch ($type)
        {
            case 'return':
                return $links;
            case 'json':
                $j = new Json();
                $j->CreateJson($links);
                return $j;
            default :
                $ret = '';
                foreach ($links as $i => $v)
                {
                    $ret.=Html::$type($v);
                }
                echo $ret;
        }
    }

    public function __toString()
    {
        return $this->str;
    }

    private function ParseStrForEach(array $value, $str)
    {
        $string = '';
        foreach ($value as $i => $v)
        {
            $string.=str_replace('{{' . $i . '}}', $v, $str);
        }
        return $string;
    }

    /**
     * retorna un array con el los siguientes valores del paginado  
     * @return array ['PaginadoModelIni' => seguiente, 'PaginadoModelEnd' => siguiente ];
     * 
     */
    public function GetNext()
    {
        $next = $this->PageActual + 1;
        if (isset($this->Pages[$next]))
        {
            return [$this->GetVar => $next];
        }
        return false;
    }

    /**
     * retorna un string con el los siguientes valores del paginado  
     * @return PaginadoModelIni=seguiente&PaginadoModelEnd=siguiente
     * 
     */
    public function GetNextUrlEncode()
    {
        return http_build_query($this->GetNext());
    }

    /**
     * CREA UNA ETIQUETA HTML <A> CON LOS DATOS DEL SIGUIENTE PAGINADO   
     * @param string $text texto dentro de la etiqueta 
     * @param array $attrs atributos de la etiqueta 
     * @return string|boolean si no hay mas link siguientes retorna false
     */
    public function NextLink($text = 'Siguiente', $attrs = [])
    {
        if (is_object($attrs) && $attrs instanceof \Smarty_Internal_Template)
        {

            $attrs = $text;
            $text = $attrs['text'];
        }
        $get = $this->GetNext();
        if ($get === false)
        {
            return false;
        }

        $attrs['href'] = isset($attrs['href']) ? $attrs['href'] : $this->href;
        return Html::a($text, ['href' => UrlManager::Href($attrs['href'], $get)] + $attrs);
    }

    /**
     * retorna un array con  los anteriores valores del paginado  
     * @return array ['PaginadoModelIni' => anterior, 'PaginadoModelEnd' => anterior ];
     * 
     */
    public function GetLast()
    {
        $next = $this->PageActual - 1;

        if (isset($this->Pages[$next]))
        {

            return [$this->GetVar => $next];
        }
        return false;
    }

    /**
     * retorna un string con  los anteriores valores del paginado  
     * @return PaginadoModelIni=anterior&PaginadoModelEnd=anterior
     * 
     */
    public function GetLastUrlEncode()
    {
        return http_build_query($this->GetLast());
    }

    /**
     * CREA UNA ETIQUETA HTML <A> CON LOS DATOS DEL ANTERIOR PAGINADO   
     * @param string $text texto dentro de la etiqueta 
     * @param array $attrs atributos de la etiqueta 
     * @return string|boolean si no hay mas link anteriores retorna false
     */
    public function LastLink($text = 'Anterior', $attrs = [])
    {
        $get = $this->GetLast();
        if (is_object($attrs) && $attrs instanceof \Smarty_Internal_Template)
        {

            $attrs = $text;
            $text = $attrs['text'];
        }
        if ($get === false)
        {

            return false;
        }

        $attrs['href'] = isset($attrs['href']) ? $attrs['href'] : $this->href;
        return Html::a($text, ['href' => UrlManager::Href($attrs['href'], $get)] + $attrs);
    }

    public function ParseSmaryTpl()
    {
        $smary = parent::ParseSmaryTpl();
        $smary['allowed'] = $smary['allowed'] + ['ActualPage', 'ListLinkPages', 'NextLink', 'LastLink'];
        return $smary;
    }

    /**
     * @ignore
     */
    public function Campos()
    {
        
    }

    public function count()
    {
        return count($this->_ValuesModel);
    }

    /**
     * REINICIA EL PUNTERO EN EL INIDCE 0
     * 
     */
    public function rewind()
    {

        return reset($this->_ValuesModel);
    }

    /**
     * @access private
     * @return boolean
     */
    public function current()
    {

        return current($this->_ValuesModel);
    }

    /**
     * @access private
     * @return type
     */
    public function key()
    {
        return key($this->_ValuesModel);
    }

    /**
     * @access private
     * @return boolean
     */
    public function next()
    {

        return next($this->_ValuesModel);
    }

    /**
     * @access private
     * @return type
     */
    public function valid()
    {
        return $this->current() !== false;
    }

}
