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
 * DocumentBuffer                                                     
 * FACILITA LA BUFERIZACION Y COMPRIMIDO EN GZIP DE LA SALIDA HTML E INCORPORA  
 * LA CLASE MIN_SCRIPT PARA AJUSTAR Y REDUCIR EL TAMANO DEL DOCUMENTO           
 *                                                                              
 * @version 1.0                                                                 
 * Fecha:  2015-04-11                                                           
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>      
 * @package CcMvc
 * @subpackage Response 
 * @uses MinScript          
 */
class DocumentBuffer
{

    protected $is_auto;
    protected $auto_compres;
    protected $nivel_compres = 9;
    protected $modo_compres;
    protected $type_text;
    protected $min;
    protected $minifi;
    public $nivel = NULL;
    protected static $CompresedZlib = false;

    /**
     * 
     * @param boolS $star indica si la buferizacion se iniciara 
     * @param bool $compress indica si se comprimira el contenido antes de enviarlo
     * @param bool $min indica si se procesara el contenido con MinScript antes de enviarlo
     * @param string $type indica el tipo del documento
     */
    public function __construct($star = false, $compress = false, $min = false, $type = 'html')
    {

        $this->min = new MinScript();
        $this->SetAutoMin($min);
        $this->SetTypeMin($type);
        $this->SetCompres($compress);
        $this->Auto_flush((bool) $star);

        if (is_callable($star))
        {
            self::Start($star);
        } elseif ((bool) $star)
        {
            self::Start([$this, 'Handle']);
        } elseif (is_array($star))
        {
            self::Start(...$star);
        }

        $this->nivel = count(ob_list_handlers());
    }

    /**
     * 
     * @ignore
     */
    public function Handle($string)
    {
        if (trim($string) == '')
            return;



        if ($this->auto_compres)
        {

            if ($this->minifi == TRUE)
            {
                $string = $this->min->Min($string, $this->type_text);
                $buffer = gzcompress($string, $this->nivel_compres, $this->modo_compres);
            } else
            {
                $buffer = gzcompress($string, $this->nivel_compres, $this->modo_compres);
            }
            IF ($buffer == false)
            {
                return $string;
            }
            $this->HeaderGzip(strlen($buffer));
            if (!is_int(self::$CompresedZlib))
                self::$CompresedZlib = 1;
            else
                self::$CompresedZlib ++;
            return $buffer; //$buffer;
        } else
        {
            if ($this->minifi)
            {
                $string = $this->min->Min($string, $this->type_text);
                header('Content-Length: ' . strlen($string));
                return $string;
            } else
            {
                header('Content-Length: ' . strlen($string));
                return $string;
            }
        }
    }

    /**
     * envia todos el buffer y todos los anidados dentro de el
     */
    public function EndConten()
    {

        for ($i = ob_get_level(); $i >= $this->nivel; $i--)
        {

            ob_end_flush();
        }
    }

    /**
     * desactiva el procesamiento con MinScript
     */
    public function Destroy()
    {
        $this->SetAutoMin(false);
    }

    /**
     * indica si se procesara el 
     * @param bool $min
     */
    public function SetAutoMin($min)
    {
        $this->minifi = $min;
    }

    /**
     * EVIA LAS CABECERAS DE GZIP
     * @param bool $Length
     */
    protected function HeaderGzip($Length)
    {
        header('Content-Encoding: gzip');
        header('Content-Length: ' . $Length);
        header("Content-Transfer-Encoding: binary");
    }

    static function AddRewriteVars($name, $value)
    {
        output_add_rewrite_var($name, $value);
    }

    /**
     * 
     */
    static function ResetRewriteVars()
    {
        output_reset_rewrite_vars();
    }

    /**
     * inicia la buferizacion
     * @param calback $output_callback
     * @param int $chunk_size
     */
    static function Start($output_callback = NULL, $chunk_size = 0)
    {
        ob_start($output_callback, $chunk_size);
    }

    static function End()
    {

        ob_end_clean();
    }

    public function ContenMin()
    {
        //return self::Conten();
        return $this->min->Min(self::Conten(), $this->type_text);
    }

    static function Conten()
    {
        return ob_get_contents();
    }

    static function Clear()
    {
        ob_clean();
    }

    static function EndFlush()
    {
        ob_end_flush();
    }

    public function Auto_flush($auto)
    {
        $this->is_auto = $auto;
    }

    public function GetTypeMin()
    {
        return $this->type_text;
    }

    /**
     * 
     * @param type $compres
     * @param type $nivel
     * @param type $modo
     * @return type
     */
    public function SetCompres($compres, $nivel = 9, $modo = ZLIB_ENCODING_GZIP)
    {
        $config = \Cc\Mvc::Config();
        $compres = $compres && $config['Response']['CompresZlib'];
        $com = '';
        if (!$compres)
        {
            $this->auto_compres = $compres;
            return;
        }
        if ($modo == ZLIB_ENCODING_GZIP)
        {
            $com = 'gzip';
        } elseif ($modo == ZLIB_ENCODING_DEFLATE)
        {
            $com = 'deflate';
        }
        if (empty($_SERVER['HTTP_ACCEPT_ENCODING']))
        {
            $this->auto_compres = false;
            return;
        }
        $acep = explode(",", $_SERVER['HTTP_ACCEPT_ENCODING']);
        if (in_array($com, $acep))
        {
            $this->auto_compres = $compres;
            $this->nivel_compres = $nivel;
            $this->modo_compres = $modo;
        } else
        {
            $this->auto_compres = false;
        }
    }

    public function ContenGzip($nivel = 9, $modo = ZLIB_ENCODING_GZIP)
    {
        if ($this->minifi)
        {
            return gzcompress($this->ContenMin(), $nivel, $modo);
        } else
        {
            return gzcompress($this->Conten(), $nivel, $modo);
        }
    }

    public function SetTypeMin($type)
    {

        if ($this->min->GetScriptAcet($type))
        {
            $this->type_text = $type;
        } else
        {
            throw new Exception("TIPO DE ARCHIVO NO SOPORTADO POR MINSCRIPT");
        }
    }

    /**
     * @ignore
     * @param type $layaut
     * @param type $dirLayaut
     */
    public function SetLayaut($layaut, $dirLayaut = NULL)
    {
        
    }

}
