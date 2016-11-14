<?php

/**
 *  
 * La clase MinScript es software libre: usted puede redistribuirlo y / o modificarlo
 * bajo los términos de la General Public License, de GNU según lo publicado por
 * la Free Software Foundation, bien de la versión 3 de la Licencia, o
 * cualquier versión posterior.
 * 
 * La redistribucion y uso de el codigo fuente, con o sin modificacion,
 * se permiten con el cimplimiento de las siguientes 
 * condiciones:
 * * Las redistribuciones del codigo fuente deven tener el aviso de copyright anterior,
 *   en la lista de condiciones y los siguientes avisos legales en la documentacion y/o 
 *   en otros materiales provistos en la distribucion
 * * Los Scripts generados por un objeto instanceado por esta clase o de un  sub clase de esta deveran llevar
 *   el nombre MinScript, la version y  aviso de CopyRight en la primera linea como comentario
 * 
 * ESTE SOFTWARE ES PROPORCIONADO POR EL PROPIETARIO DEL COPYRIGHT Y COLABORADORES 
 * "TAL COMO ESTÁ" SIN GARANTIA EXPRESA O IMPLÍCITAS DE CUALQUIER TIPO, INCLUYENDO, PERO NO LIMITADO A,
 * LAS GARANTÍAS IMPLÍCITAS DE COMERCIABILIDAD E IDONEIDAD PARA UN PROPÓSITO. 
 * EN NINGÚN CASO LOS DERECHOS DE AUTOR DEL PROPIETARIO O COLABORADORES SERÁN RESPONSABLES DE DAÑOS DIRECTOS,
 * INDIRECTOS, INCIDENTALES, ESPECIAL, EJEMPLARES O CONSECUENTES( INCLUYENDO,
 * PERO NO LIMITADO A,ADQUISISION Y SUSTITUCIÓN DE BIENES Y SERVICIOS; 
 * PÉRDIDA DE USO, DE DATOS, O BENEFICIOS O INTERRUPCIÓN DEL NEGOCIO) CAUSADOS COMO FUERE EN CUALQUIER TEORÍA DE RESPONSABILIDAD
 * CONTRACTUAL, RESPONSABILIDAD ESTRICTA O RESPONSABILIDAD CIVIL( INCLUYENDO NEGLIGENCIA O CUALQUIER OTRA FORMA) 
 * QUE SURJAN DE NINGUNA MANERA DEL USO DE ESTE SOFTWARE, AUNQUE INFORMADOS DE LA POSIBILIDAD DE TALES DAÑOS.
 */

namespace Cc;

/**
 * MinScript                                                             
 * ELIMINA LOS COMENTARIOS DE UNA LINEA,ESPACIOS, TABULACIONES                      
 *  Y SALTOS DE LINEAS  SOBRANTES DE SCRIPTS HTML,CSS,JS,JSON
 *                     
 *                                                                              
 * @version: 1.0.1.0                                                           
 * @fecha:  2015-08-16                                                         
 * @autor:  ENYREBER FRANCO    <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                    
 * @copyright © 2015-2016, Enyerber Franco
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License  
 * @package Cc
 * @subpackage Utilidades
 */
class MinScript
{
    /* <b>1.0.1.0</b> */

    public $version = '7.0.1.0'; //version 
    protected $HTML = array('script' => 0, 'style' => 0, 'head' => 0, 'pre' => 0, 'textarea' => 0, 'coment' => 0); //etiquetas html 
    protected $HTML_HEAD = '';
    protected $is_info = true;
    protected $script_acet = array('html', 'css', 'js', 'json');
    protected $scripthtml = "";
    protected $scriptjs = "";
    protected $scriptcss = "";
    protected $conten = '';
    protected $type = '';

    /*     * METODOS PUBLICOS */

    public function __construct($str = NULL, $type = 'html')
    {
        $this->IniTag();
        if (!is_null($str))
        {
            $this->conten = $this->Min($str, $type);
        }
    }

    public final function GetScriptAcet($type)
    {
        if (in_array($type, $this->script_acet))
        {
            return true;
        } else
        {
            return false;
        }
    }

    public function GetHtml()
    {
        if (trim($this->scripthtml) != "")
        {
            return "<!--" . $this->GetInfo() . "-->\n" . $this->scripthtml;
        }
        return "";
    }

    public function GetJs()
    {
        if (trim($this->scriptjs) != "")
            return "/*" . $this->GetInfo() . "*/\n" . $this->scriptjs;
        return "";
    }

    public function GetCss()
    {
        if (trim($this->scriptjs) != "")
            return "/*" . $this->GetInfo() . "*/\n" . $this->scriptcss;
        return "";
    }

    /**
     *  QUITA LOS COMENTARIOS DE UNA LINEA,
     *  ESPACIOS, TABULACIONES Y SALTOS DE LINEAS  SOBRANTES DE UNA CADENA HTML,CSS,JS,JSON
     *  @param $cadena cadena de caracteres HTML,CSS,JS,JSON
     *  @param $type tipo de texto HTML,CSS,JS,JSON
     *  @return string CADENA DE TEXTO
     */
    public function Min($cadena = NULL, $type = 'html')
    {
        $this->type = $type;
        if (is_null($cadena))
        {
            return $this->conten;
        }
        $buff = '';
        $info = '';
        //return $cadena;
        switch (strtolower($type))
        {
            case 'css':
                if ($this->is_info == true)
                    $info = "/*" . $this->GetInfo() . "*/\n";
                $buff.=$this->CssMin($cadena);
                break;
            case 'json':
                if ($this->is_info == true)
                    $buff = $this->JsMin($cadena);
                break;
            case 'js':
                if ($this->is_info == true)
                    $info = "/*" . $this->GetInfo() . "*/\n";
                $buff.=$this->JsMin($cadena);
                break;
            default:
                if ($this->is_info == true)
                    $info = "<!--" . $this->GetInfo() . " -->\n";
                $buff.=$this->HtmlMin($cadena);
                break;
        }
        $this->conten = ($buff == '' ? $buff : $info . $buff);
        //return implode('',$palabras);
        return $this->conten;
    }

    /**
     *  QUITA LOS COMENTARIOS DE UNA LINEA,
     *  ESPACIOS, TABULACIONES Y SALTOS DE LINEAS  SOBRANTES DE UN ARCHIVO HTML,CSS,JS,JSON 
     *  @param string $filename ruta del archivo HTML,CSS,JS,JSON
     *  @param string $sifij sufijo para el nombre del archivo de salida HTML,CSS,JS,JSON
     *  @param string mixes $ouput si es booleano true indica si se guardara en un archivo con el sufijo($sifij) y false para retornar el contenido si es un string indica el nombre de archivo de salida
     *  @param string $typo indica el tipo de archivo  HTML,CSS,JS,JSON
     *  @return string array el indice es el nombre del archivo el valor es el nombre del archivo de salida
     */
    public function FileMin($filename, $sifij = 'min', $ouput = true, $typo = '')
    {
        $new_dir = '';
        if (!is_array($filename))
        {
            $archivos = array(0 => $filename);
        } else
        {
            $archivos = $filename;
            // throw new Exception("NO PUEDE CONCATENAR ARCHIVOS HTML");
        }
        $f = '';
        if (is_string($ouput))
        {
            $new_dir = $ouput;
        } elseif (is_bool($ouput) && $ouput)
        {
            if ($sifij != '')
                $new_dir = $this->SetSifij($new_fil, $sifij);
        }
        $files = implode(",\n", $archivos);
        $coment = "\n" . $new_dir . " " . date('Y-m-d H:i:s') . "\nCONTEN FILES\n" . implode("\n", $archivos) . "\n";
        switch (strtolower($typo))
        {
            case 'css':
                if ($this->is_info)
                    $info = "/*" . $this->GetInfo() . $coment . "*/";
                break;
            case 'js':
                if ($this->is_info)
                    $info = "/*" . $this->GetInfo() . $coment . "*/";
                break;
            case 'json':
                $info = '';
                break;
            default:
                if ($this->is_info)
                    $info = "<!--" . $this->GetInfo() . "-->";
                break;
        }
        $new = '';
        $new_fil = $archivos[count($archivos) - 1];
        if ($typo == '')
        {
            $typo = $this->GetType($archivos[count($archivos) - 1]);
        }
        $this->is_info = false;
        foreach ($archivos as $file)
        {
            if (!($conten = $this->FileRead($file)))
            {

                throw new \Exception("EL ARCHIVO " . $file . " NO EXISTE");
                // return array($file => "!file_exists(" . $file . ")");
            }
            if ($this->IsSifij($file, 'min'))
            {
                $new.=$conten;
            } else
            {
                $new.=$this->Min($conten, $typo);
            }
        }
        $new_content = $info . $new;
        $this->is_info = true;
        if (is_bool($ouput))
        {
            if ($ouput)
            {
                $this->FileWrite($new_dir, $new_content);
                return array($files => $new_dir);
            } else
            {
                return $new_content;
            }
        } elseif (is_string($ouput))
        {
            $this->FileWrite($ouput, $new_content);
            return array($files => $ouput);
        } else
        {
            return $new_content;
        }
    }

    /**
     * QUITA LOS COMENTARIOS DE UNA LINEA,
     *  ESPACIOS, TABULACIONES Y SALTOS DE LINEAS  SOBRANTES DE TODOS LOS ARCHIVOS  HTML,CSS,JS,JSON  DE UN DIRECTORIO  </b>
     *  @param string $dir directorio
     *  @param string $sifij sufijo para el nombre del archivo de salida HTML,CSS,JS,JSON
     *  @param mixes $tipos tipos de archivos que se buscaran  HTML,CSS,JS,JSON
     *  @param mixes $filesave si se le pasa un directorio de archivo almacena todo el contenido de los archivos encontrados en $dir
     *  @return array el indice es el nombre de los archivo el valor es el nombre del archivo de salida
     */
    public function DirMin($dir, $sifij = 'min', $tipos = NULL, $filesave = NULL)
    {
        $files = $this->DirSearch($dir, $tipos);
        ;
        $ret_files = array();
        //return $files;
        if (!is_null($filesave))
        {
            $ret_files = $this->FileMin($files, '', $filesave, is_array($tipos) ? $tipos[0] : $tipos);
        } else
        {
            foreach ($files as $file)
            {
                $ret_files = array_merge($ret_files, $this->FileMin($file, $sifij, true, $this->GetType($file)));
            }
        }
        return $ret_files;
    }

    private function DirSearch($dir, $tipo = NULL)
    {
        $piladir = array();
        $tpos = array();
        if (!is_null($tipo) && !is_array($tipo))
        {
            $tpos = array($tipo);
        }
        $direct = dir($dir);
        while ($fichero = $direct->read())
        {
            if ($fichero != '.' && $fichero != '..')
            {
                $ext = '';
                $fic = explode('.', $fichero);
                if (count($fic) > 1)
                    $ext = $this->GetType($fichero);
                if (is_null($tipo))
                {
                    $a = $this->GetScriptAcet($ext);
                } else
                {
                    $a = in_array($ext, $tpos);
                }
                if (count($fic) > 1)
                {
                    if ($a)
                        array_push($piladir, $dir . $fichero);
                    //$this->InsetDir($dir.$fichero);
                }elseif (count($fic) == 1)
                {
                    $piladir = array_merge($piladir, $this->DirSearch($dir . $fichero . "/", $tipo));
                }
            }
        }
        $direct->close();
        return $piladir;
    }

    private function GetType($file)
    {
        $fic = explode('.', $file);
        return strtolower(array_pop($fic));
    }

    private function SetSifij($file, $sufij)
    {
        $fic = explode('.', $file);
        $ext = strtolower(array_pop($fic));
        return implode('.', $fic) . "." . $sufij . "." . $ext;
    }

    private function IsSifij($file, $sifij)
    {
        $fic = explode('.', $file);
        $ext = $fic[count($fic) - 2];
        return $ext === $sifij;
    }

    /** METODOS PRIVADOS * */
    protected function HtmlMin($html_script)
    {
        $new_html = '';
        $js = $css = $this->HTML_HEAD = "";
        // Intérprete de HTML
        $a = preg_split('/<(.*)>/U', $html_script, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($a as $i => $e)
        {
            if ($i % 2 == 0)
            {
                if ($this->HTML['style'] > 0)
                {

                    $this->scriptcss.=$e;
                } elseif ($this->HTML['script'] > 0)
                {


                    $this->scriptjs.=$e;
                } else
                {
                    if ($this->HTML['pre'] > 0 || $this->HTML['textarea'] > 0)
                    {

                        $new_html.=$e;
                        // $new_html.=$this->JsMin($e);
                    }
                    if (max($this->HTML) == 0)
                    {
                        $e = preg_replace('/(\n{1,} {1,})|(\r{1,}\n{1,})|\n{1,}|\t{1,}| {1,}|\r{1,}/', ' ', $e);
                        $new_html.=$e;
                    }
                }
            } else
            {

                $new_html.=$this->DetectTag($e);
            }
        }

        $this->HTML_HEAD = '';
        //$this->scriptjs = $this->JsMin($js);
        // $this->scriptcss = $this->CssMin($css);

        $a = preg_split('/' . preg_quote('<!--', '/') . '(.*)' . preg_quote('-->', '/') . '/U', $new_html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $new_html = '';
        foreach ($a as $i => $e)
        {
            if ($i % 2 == 0)
            {
                $new_html .= $e;
            } elseif ($e[0] == '[')
            {
                $new_html .= '<!--' . $e . '-->';
            }
        }
        $this->scripthtml = $new_html;
        $this->IniTag();
        return $new_html;
    }

    private function AddHead($html)
    {
        $head = $this->HTML_HEAD;
        $a = explode('</head>', $html);
        $a[0].=$head;
        return implode('</head>', $a);
    }

    private function IniTag()
    {
        foreach ($this->HTML as $i => $tag)
        {
            $this->HTML[$i] = 0;
        }
    }

    private function DetectTag($e)
    {
        $a2 = explode(' ', $e);
        $tag = strtolower(array_shift($a2));
        $attr = implode(' ', $a2);
        $etiqueta = '';
        if ($e[0] == '/')
        {
            $etiqueta.=$this->CloseTag(strtolower(substr($e, 1)));
        } else
        {
            // Extraer atributos
            $etiqueta.=$this->OpenTag($tag, $attr);
        }
        return $etiqueta;
    }

    private function OpenTag($tag, $attr)
    {
        // Etiqueta de apertura
        if ($this->HTML['script'] > 0)
        {
            $this->scriptjs .= '<' . $tag . " " . $attr . ">";

            return '';
        }

        switch ($tag)
        {
            case 'script':
                $this->HTML[$tag] ++;
                $this->scriptjs = '';
                $src = explode('src', strtolower($attr));
                if (count($src) != 1)
                {
                    return '<' . $tag . " " . $this->Attr($attr) . "></" . $tag . ">";
                }
                break;
            case 'style':
                $this->HTML[$tag] ++;
                $this->scriptcss = '';
                break;
            case 'pre':
                $this->HTML[$tag] ++;
                break;
            case 'textarea':
                $this->HTML[$tag] ++;
                break;
        }
        if ($attr != '')
        {
            return '<' . $tag . " " . $this->Attr($attr) . ">";
        }
        return '<' . $tag . ">";
    }

    private function Attr($attributos)
    {
        return $attributos;
        return $this->CssMin($attributos);
    }

    private function CloseTag($tag)
    {
        if ($this->HTML['script'] > 0 && $tag != 'script')
        {
            $this->scriptjs .= '</' . $tag . ">";
            return '';
        }
        switch ($tag)
        {
            case 'script':
                $this->HTML[$tag]-=($this->HTML[$tag] > 0 ? 1 : 0);
                $js = $this->JsMin($this->scriptjs);

                $this->scriptjs = '';
                return $js . '</' . $tag . ">";

                break;
            case 'style':
                $this->HTML[$tag]-=($this->HTML[$tag] > 0 ? 1 : 0);
                $css = $this->CssMin($this->scriptcss);

                $this->scriptcss = '';
                return $css . '</' . $tag . ">";
                break;
            case 'pre':
                $this->HTML[$tag] = 0;
                break;
            case 'textarea':
                $this->HTML[$tag] = 0;
                break;
        }
        return '</' . $tag . ">";
    }

    protected function GetInfo()
    {
        return "! MinScript " . $this->version . " CopyRight 2015-2016, Enyerber Franco <http://enyerberfranco.com.ve>|Copyright (c) 2012, Matthias Mullie";
    }

    protected function CssMin($css_script)
    {
        $chars = array('{', '}', ';', ':', '[', '] ', '=', '"', "'", ',', '<', '>', ' ');
        //preg_match("/\/\*(.*)\*\//", $css_script,$preg);
        //$css_script=implode(preg_split("/\/\*.*\*\//",$css_script,-1));
        $match = '/(\/\*(.*)\*\/)/Us';
        $css = preg_replace($match, "", $css_script);
        //$css = $match . $css;
        $cadena = '';
        $css = preg_replace('/(\n{1,} {1,})|(\r{1,}\n{1,})|\n{1,}|\t{1,}| {1,}|\r{1,}/', ' ', $css);
        foreach ($chars as $char)
        {
            $lineas = explode($char, $css);
            for ($i = 0; $i < count($lineas); $i++)
            {
                $p = trim($lineas[$i]);
                if ($p != '')
                    $lineas[$i] = $p;
            }

            $css = implode($char, $lineas);
        }
        $css = str_replace('﻿', '', (string) $css);
        $this->scriptcss = (string) $css;
        return (string) $css;
    }

    protected function JsMin($js_script)
    {

        return $js_script;
        $chars = array(';', ':', ',', '[', ']', '(', ')', '=', '/', '{', '}', '|', '&', '+', '-', '*', '!', '?', '.', '>', '<', '%', 'else');
        $js = '';
        $cadena = '';


        $js_script = preg_replace("/\/\*(.*)\*\//Us", "", $js_script);
        //$js_script = str_replace("\n", "", $js_script);

        foreach (explode("\\n", $js_script) as $scr)
        {
            $scr = preg_replace("/(\/\/.*\n{1,})/U", "", trim($scr));
            $js.=(substr(trim($scr), 0, 2) == 'if' ? ' ' : NULL) . trim($scr);
        }
        foreach ($chars as $char)
        {
            $cadenas = explode($char, $js);
            for ($i = 0; $i < count($cadenas); $i++)
            {
                $cadenas[$i] = trim($cadenas[$i]);
                if ($i < count($cadenas) - 1)
                {
                    if ($char == 'else')
                    {
                        $cadenas[$i + 1] = ' ' . $cadenas[$i + 1];
                    }
                    $csig = trim($cadenas[$i + 1]);
                    $sig = $this->uc($csig);
                    if ($char === '}' && substr($csig, 0, 4) !== 'else' && substr($csig, 0, 5) !== 'catch' && ((ord($sig) >= 65 && ord($sig) <= 90) || (ord($sig) >= 97 && ord($sig) <= 122) || ord($sig) === 36))
                    {
                        $cadenas[$i + 1] = '; ' . $csig;
                    }
                }
            }
            $js = implode($char, $cadenas);
        }
        //$js=str_replace('﻿','',(string)$js);
        $this->scriptjs = $js;
        return $js;
    }

    private function uc($cadena)
    {
        if (strlen($cadena) > 0)
        {
            $a = substr($cadena, 0, 1);
            if (ord($a) == 0)
            {
                return $this->uc(substr($cadena, 1, strlen($cadena) - 1));
            } else
            {
                return $cadena;
            }
        }
    }

    private function FileRead($filename)
    {
        if (!file_exists($filename))
        {
            return false;
        }

        $fi = fopen($filename, 'r');
        $conten = fread($fi, filesize($filename));
        fclose($fi);
        return $conten;
    }

    private function FileWrite($filename, $text)
    {
        $fi = fopen($filename, 'w+');
        fwrite($fi, $text);
        fclose($fi);
    }

}
