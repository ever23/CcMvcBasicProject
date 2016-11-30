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
use Cc\CcException;
use Cc\UrlManager;
use Cc\Cache;

/**
 * CLASE DE RESPUESTA PROCESA EL CONTENIDO HTML ANTES DE ENVIARLO 
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc
 * @subpackage Response
 * @uses MinScript 
 * @uses DocumentBuffer 
 * @example ../examples/CERQU/protected/layauts/main.php EJEMPLO DE UN ARCHIVO LAYAUT QUE SERA EJECUTADO AL FINALIZAR LA EJECUCION DE CcMvc
 * 
 * 
 */
class Html extends Response
{

    /**
     * refrencias a recursos javascript
     * @var array 
     */
    protected $js = array();

    /**
     * refrencias a recursos javascript
     * @var array 
     */
    protected $css = array();

    /**
     * titulo de la pagina 
     * @var string 
     */
    public $titulo = "Default Document CcMvc";

    /**
     * descripcion de la pagina para la etiqueta <meta name='descrption' content=''>
     * @var string 
     */
    public $Description = '';

    /**
     * icono del sitio
     * @var string
     */
    protected $ico = 'favicon.ico';

    /**
     * root 
     * @var string 
     */
    public $ROOT_HTML;

    /**
     * contenido adicional de la etiqueta head
     * @var string 
     */
    public $Head = '';

    /**
     * indica si ya se imprimio el contenido 
     * @var bool 
     */
    protected $foother = false;

    /**
     * script que sera impreso cuando ocurran errores 
     * @var string 
     */
    protected $script_error;

    /**
     * codigo javascript
     * @var string 
     */
    protected $jsscript;

    /**
     * estilos css
     * @var string 
     */
    protected $cssscript;

    /**
     * errores 
     * @var string 
     */
    public $errores;

    /**
     * alisa de directorios  
     * @var array 
     */
    protected $src = array();

    /**
     * configuracion de {@link Mvc}
     * @var Config 
     */
    protected $AppConfig = [];

    /**
     * etiquetas meta 
     * @var array 
     */
    private $MetaTang = [];
    protected $http_equiv = [];

    /**
     *
     * @var string 
     */
    private $conten = '';

    /**
     *
     * @var string 
     */
    protected $BasePath = '';

    /**
     * palabras clave 
     * @var array 
     */
    protected $KeyWords = [];

    /**
     *
     * @var JsonLD 
     */
    public $JsonLD = NULL;
    protected $saveCache = false;
    protected $CacheLifeTime = 60;
    protected $cacheDir = '';

    public static function CtorParam()
    {
        Mvc::App()->ChangeResponseConten('text/html');
        return Mvc::App()->Response;
    }

    /**
     * 
     * @param bool $compress indica si se comprimira el resultado
     * @param bool $min indica si se procesara con MinScript
     */
    public function __construct($compress = true, $min = false)
    {

        $this->foother = false;
        $this->errores = '';
        $this->AppConfig = Mvc::App()->Config();
        $this->ROOT_HTML = $this->AppConfig['Router']['DocumentRoot'];
        if ($this->ROOT_HTML[0] != '/')
        {
            $this->ROOT_HTML = '/' . $this->ROOT_HTML;
        }
        $this->titulo = &Mvc::App()->Name;
        $this->SetSrc("{root}", $this->ROOT_HTML);
        $this->SetSrc("{src}", 'src/');
        $this->script_error = "";
        $this->BasePath = UrlManager::BuildUrl($this->AppConfig['Router']['protocol'], $_SERVER['HTTP_HOST'], $this->ROOT_HTML);
        $this->MetaTang = $this->AppConfig->SEO['MetaTang'];
        $this->KeyWords = $this->AppConfig->SEO['keywords'];
        $this->http_equiv = $this->AppConfig->SEO['HttpEquiv'] + ['Content-Type' => 'text/html; charset=UTF-8'];
        $this->cacheDir = Mvc::App()->Config()->App['Cache'] . 'Html' . DIRECTORY_SEPARATOR;
        Cache::AutoClearCacheFile($this->cacheDir);
        parent::__construct($compress, $min, 'html');
    }

    public function ClearCache()
    {
        if (is_dir($this->cacheDir))
        {
            $dir = dir($this->cacheDir);
            while ($file = $dir->read())
            {
                unlink($dir . $file);
            }
        }
    }

    public function SetCache($isCache, $lifeTime = 60)
    {
        $this->saveCache = (bool) $isCache;
        $this->CacheLifeTime = $lifeTime;
    }

    /**
     * 
     * @param string $conten
     * @return string
     */
    public function ProccessConten($conten)
    {
        if ($this->saveCache && !Mvc::App()->IsDebung() && !$_POST)
        {
            return $this->CacheMin($conten, $this->min);
        }
        return parent::ProccessConten($conten);
    }

    public function CacheMin($conten, $minify = false)
    {
        $cache = $this->cacheDir;
        $f = dirname(Mvc::App()->GetExecutedFile()) . DIRECTORY_SEPARATOR;
        if (!is_dir($cache))
            mkdir($cache);

        $controller = Mvc::App()->GetController();
        $name = "paquete" . $controller['paquete'] . '.controller' . $controller['controller'] . '.controller' . $controller['method'];
        if ($_GET)
        {
            $name .='.GET-';
            foreach ($_GET as $i => $v)
            {
                $name .=$i . '_' . $v;
            }
        }
        $name.='.html';

        $this->fileCache = new \SplFileInfo($cache . $name);
        $cache = [];
        $cache['type'] = 'Controllers';
        $cache['Controller'] = Mvc::App()->GetController();
        $cache['RealFile'] = $this->fileCache->__toString();
        $cache['LifeTime'] = $this->CacheLifeTime;

        if ($minify)
        {
            $min = new MinScript($conten, 'html');
            $conten2 = $min->Min();
        } else
        {
            $conten2 = "<!--Response by cache " . date("Y-m-d H:m:i") . " -->\n" . $conten;
        }

        $f = fopen($this->fileCache, 'w');
        fwrite($f, $conten2);
        fclose($f);

        \Cc\Cache::Set(Mvc::App()->GetNameStaticCacheRouter(), $cache);
        //   Router::HeadersReponseFiles($this->fileCache, Mvc::App()->Content_type, Mvc::App()->Config()->Router['CacheExpiresTime']);

        return "<!--create cache " . $name . " -->\n" . $conten2;
    }

    /**
     * 
     * @param type $type
     * @return \Cc\Mvc\JsonLD
     */
    public function &CreateEstructureData($type = NULL)
    {
        $j = new JsonLD($type);
        $j["@context"] = "http://schema.org";
        $this->JsonLD[] = $j;
        return $j;
    }

    /**
     * 
     * @return string
     */
    public function GetBasePath()
    {
        return $this->BasePath;
    }

    /**
     *  INGRESA ALIAS PARA LAS DIRECCIONES DE LOS LINK DE SCRIPTS
     *  @param string $seudo EL TEXTO QUE SE BUSCARA EN LOS LINKS DEVE CUPLIR CON LA SIGUIENTE SINTAXIS {namedir}
     *  @param string $src  LA DIRECCION QUE SERA COLOCADA DONDE SE CONSIGA EL $seudo
     */
    public function SetSrc($seudo, $src)
    {
        $this->src[$seudo] = $this->ReplaceSrc($src);
    }

    /**
     * AGREGA UNA ETIQUETA <META> A EL <HEAD> DE LA PAGINA 
     * @param string $name nombre 
     * @param string $value
     */
    public function AddMetaTang($name, $value)
    {

        $this->MetaTang[$name] = $value;
    }

    /**
     * Agrega una palabra clave a la etiqueta meta[keyword]
     * @param ...$word 
     */
    public function AddKeyWords(...$word)
    {
        if (count($word) > 1)
        {
            foreach ($word as $w)
                $this->AddKeyWords($w);
        } else
        {
            $word = $word[0];
        }
        if (is_array($word))
        {
            foreach ($word as $w)
                $this->AddKeyWords($w);
        } else
        {

            $this->KeyWords[] = $word;
        }
    }

    /**
     * agrega una etiqueta meta[http-quiv] a head
     * @param string $name valor del atributo http-quiv
     * @param string $value valor del atributo content
     */
    public function AddHttpEquiv($name, $value)
    {
        $this->http_equiv[$name] = $value;
    }

    /**
     * RETORNA EL CONTENIDO PARA SER INSERTADO DETRO DE HEAD GENERALMENTE SE USA EN EL LAYAUT
     * ESTO INCLUYE LAS ETIQUETAS <TITLE><SCRITS><LINK> Y EL ICONO
     * @return string  
     */
    public function GetContenHead()
    {
        $jsOptimize = "(function(d,w){"
                . "var bp='" . str_replace(' ', '%20', $this->ROOT_HTML) . "';";
        if ($this->AppConfig->Response['OptimizeImages'])
        {
            $jsOptimize.= "d.cookie='GDmaxW='+w.innerWidth+'; path='+bp;";
        }


        $jsOptimize.= "})(document,window);";
        $js = $this->GetJsScript();
        $css = $this->GetCssScript();

        $head = "\n" . $this->Head . "\n";
        $keywords = '';
        foreach ($this->KeyWords as $word)
        {
            $word = trim($word);
            if ($word != '')
                $keywords.=$word . ', ';
        }
        if (isset($this->MetaTang['keywords']))
        {
            $keywords.=$this->MetaTang['keywords'];
        }
        $this->MetaTang['keywords'] = $keywords;

        if (isset($this->MetaTang['description']))
        {
            $this->Description = $this->MetaTang['description'] . $this->Description;
            unset($this->MetaTang['description']);
        }

        foreach ($this->http_equiv as $i => $v)
        {
            //Content-Language
            $head.=self::meta([ "http-equiv" => $i, "content" => $v]) . "\n";
        }
        foreach ($this->MetaTang as $i => $v)
        {

            $head.=self::meta(['name' => $i, 'content' => $v]) . "\n";
        }
        $head.=self::meta(['name' => 'description', 'content' => $this->Description]) . "\n";
        $head .= self::base(['href' => $this->BasePath]) . "\n";
        if ($this->ico)
            $head .= self::link(['rel' => 'shortcut icon', 'href' => $this->ico, 'media' => 'monochrome']) . "\n";
        if ($this->titulo)
            $head.=self::title($this->titulo) . "\n";
        $head.=self::script($jsOptimize, ['type' => 'text/javascript']);
        $head.=$this->link_cssjs();
        if ($js != "")
            $head.=self::script($js, ['type' => 'text/javascript']);
        if ($css != "")
            $head.=self::style($css, ['type' => 'text/css']);
        if (count($this->JsonLD) > 0)
        {
            if (count($this->JsonLD) == 1)
            {
                $head.=self::script($this->JsonLD[0]->Encode(), ['type' => 'application/ld+json'], false);
            } else
            {
                $head.=self::script(json_encode($this->JsonLD), ['type' => 'application/ld+json'], false);
            }
        }
        return $head;
    }

    /**
     * 
     * @param string $error
     * @deprecated 
     */
    public function add_error($error)
    {
        $this->AddError($error);
    }

    /**
     * 
     * @param string $error
     */
    public function AddError($error)
    {
        $this->errores.=$error;
    }

    /**
     *  INSERTA UN UNA ETIQUETA SCRIPT TIPÓ LINK EN EL HEAD DEL DOCUMENTO
     *  @param string $name EL NOMBRE DEL SCRIPT
     *  @param string $fn P PARA QUE EL SCRITP SEA INSERTADO AL PRINCIPIO Y U AL FINAL
     * @return this 
     */
    public function &addlink_js($name, $fn = 'P')
    {

        if (!is_array($name))
        {

            $new_name = $this->ReplaceSrc($name, 'js/');
            if (empty($this->js) || !in_array($new_name, $this->js))
            {
                if ($fn == 'P')
                {
                    array_push($this->js, $new_name);
                } elseif ($fn == 'U')
                {
                    array_unshift($this->js, $new_name);
                }
            }
        } else
        {
            foreach (array_reverse($name) as $na)
            {
                $this->addlink_js($na, $fn);
            }
        }
        return $this;
    }

    /**
     *  INSERTA UN SCRIPT JS EN EN EL DOCUMENTO
     *  @param string $js SCRIPT JS
     *  @return this
     */
    public function &AddJsScript($js)
    {
        $this->jsscript.=$js;
        return $this;
    }

    /**
     * RETORNA LOS SCRIPTS JS CONTENIDOS
     * @return string 
     */
    public function GetJsScript()
    {
        return $this->jsscript;
    }

    /**
     * INSERTA UN UNA ETIQUETA LINK EN EL HEAD DEL DOCUMENTO
     * @param string $name EL NOMBRE DEL SCRIPT
     * @param string $fn P PARA QUE EL SCRITP SEA INSERTADO AL PRINCIPIO Y U AL FINAL
     * @return this 
     */
    public function &addlink_css($name, $fn = 'P')
    {
        if (!is_array($name))
        {
            $new_name = $this->ReplaceSrc($name, 'css/');
            if (empty($this->css) || !in_array($new_name, $this->css))
            {
                if ($fn == 'P')
                {
                    array_push($this->css, $new_name);
                } elseif ($fn == 'U')
                {
                    array_unshift($this->css, $new_name);
                }
            }
        } else
        {
            foreach (array_reverse($name) as $na)
            {
                $this->addlink_css($na, $fn);
            }
        }
        return $this;
    }

    /**
     * INSERTA UN SCRIPT CSS EN EN EL DOCUMENTO
     * @param string $css SCRIPT CSS
     * @return this AUTOREFERENCIA
     */
    public function &AddCssScript($css)
    {
        $this->cssscript.=$css;
        return $this;
    }

    /**
     * RETORNA LOS SCRIPTS JS CONTENIDOS
     * @return string 
     */
    public function GetCssScript()
    {
        return $this->cssscript;
    }

    /**
     *  ESTABLECE EL TITULO DEL DOCUMENTO
     *  @param string $title TITULO DE LA PAGINA
     *  @return this AUTOREFERENCIA
     */
    public function &set_title($title)
    {
        $this->titulo = $title;
        return $this;
    }

    /**
     *  ESTABLECE EL ICONO DEL DOCUMENTO
     *  @param string $ico DIRECCION DEL ICONO
     *  @return this AUTOREFERENCIA
     */
    public function &set_ico($ico)
    {
        $this->ico = $this->ReplaceSrc($ico);
        return $this;
    }

    /**
     *  RETORA LOS LINK CSS JS COMPLETOS EN UNA CADENA DE TEXTO¨
     * 
     *  @return string LINKS HTML
     */
    public function link_cssjs()
    {
        $link = '';

        foreach ($this->js as $js)
        {
            if (!Mvc::App()->IsDebung() && isset($this->AppConfig->SEO['CDNs'][$js]))
            {
                $link.=self::script("", ['src' => $this->AppConfig->SEO['CDNs'][$js], 'type' => 'text/javascript']);
            } else
            {
                $link.=self::script("", ['src' => $js, 'type' => 'text/javascript']);
            }
        }
        foreach ($this->css as $css)
        {
            if (!Mvc::App()->IsDebung() && isset($this->AppConfig->SEO['CDNs'][$css]))
            {

                $link.=self::link(['rel' => 'stylesheet', 'href' => $this->AppConfig->SEO['CDNs'][$css], 'media' => 'all']);
            } else
            {
                $link.=self::link(['rel' => 'stylesheet', 'href' => $css, 'media' => 'screen']);
            }
        }
        return $link;
    }

    /**
     * @internal 
     * @return string
     */
    public function __toString()
    {
        return $this->conten;
    }

    /**
     * 
     * @param string $text
     * @param string $type
     * @return string
     */
    protected function ReplaceSrc($text, $type = '')
    {
        if (count($this->src) == 0)
        {
            return $text;
        }
        foreach ($this->src as $i => $v)
        {
            $text = str_replace($i, $v . $type, $text);
        }
        return $text;
    }

    /**
     * 
     */
    protected function ShowError()
    {
        $this->add_error(CcException::GetExeptionS());
        if ($this->errores != '')
        {
            $this->AddJsScript($this->script_error);
        }
    }

    /**
     * 
     * @return array
     * @internal 
     */
    public function GetLayaut()
    {
        $this->ShowError();
        return parent::GetLayaut();
    }

    /**
     * CODIFICA LOS UNA CADENA HTML
     * @param string $text
     * @return string
     */
    public static function Encode($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'utf-8');
    }

    /**
     * DECODIFICA UNA CADENA HTML
     * @param string $text
     * @return string
     */
    public static function Decode($text)
    {
        return htmlspecialchars_decode($text, ENT_QUOTES, 'utf-8');
    }

    /**
     * 
     * @param array $data
     * @return array
     */
    public static function EncodeArray($data)
    {
        $d = array();
        foreach ($data as $key => $value)
        {
            if (is_string($key))
                $key = htmlspecialchars($key, ENT_QUOTES);
            if (is_string($value))
                $value = htmlspecialchars($value, ENT_QUOTES);
            elseif (is_array($value))
                $value = self::EncodeArray($value);
            $d[$key] = $value;
        }
        return $d;
    }

    /**
     * FUNCION MAGICA SE EJECUTA CADA VEZ QUE ES LLAMADO UN METODO ESTATICO 
     * QUE NO ESTE DEFINIDO 
     * EN ESTA CLASE ESTA DEFINIDO PARA CREAR ETIQUETAS HTML 
     * <code><?php
     * echo Html::h1('hola mundo');// <h1>hola mundo</h1>
     * 
     * echo Html::div('un texto',['class'=>'texto']);// <div class='texto'>un texto</h1> 
     * 
     * </code>
     * @param string $name NOMBRE DE LA ETIQUETA 
     * @param array $arguments ARGUMENTOS $text y $attrs DE {@link Tang} 
     * @return string
     * 
     */
    public static function __callStatic($name, array $arguments)
    {
        $arguments[0] = isset($arguments[0]) ? $arguments[0] : '';
        $arguments[1] = isset($arguments[1]) ? $arguments[1] : [];

        return self::Tang($name, $arguments[0], $arguments[1]);
    }

    /**
     * CREA UNA ETIQUETA HTML 
     * @param string $name NOMBRE DE LA ETIQUETA 
     * @param string|array $text TEXTO DE LA ETIQUETA SI ES UN ARRAY SE TOMA CON EL PARAMETRO $attrs
     * @param array $attrs ATRIBUTOS DE LA ETIQUETA [attr=>value]
     * @return string
     */
    public static function Tang($name, $text = '', $attrs = NULL)
    {
        if (is_array($text))
        {
            return self::OpenTang($name, $text, true);
        } else
        {
            return self::OpenTang($name, $attrs) . $text . self::CloseTang($name);
        }
    }

    /**
     * CREA UNA ETIQUETA DE APERTURA HTML
     * @param string $name NOMBRE DE LA ETIQUETA 
     * @param array $attrs ATRIBUTOS DE LA ETIQUETA [attr=>value]
     * @param bool $close INDICA SI LA ETIQUETA ES DE AUTOCERRADO EJEMPLO <imput />,<img />,<link /> true SI ES UNA ETIQUETA DE AUTOCERRADO Y false SI NO
     * @return string
     */
    public static function OpenTang($name, array $attrs = [], $close = false)
    {
        return "<" . $name . " " . self::ConvertAttrs($attrs) . ($close ? '/' : '') . ">";
    }

    /**
     * CREA UNA ETIQUETA DE CIERRE 
     * @param string $name NOMBRE DE LA ETIQUETA 
     * @return string
     *
     */
    public static function CloseTang($name)
    {
        return "</" . $name . ">";
    }

    /**
     * CONVIERTE UN ARRAY EN ATRIBUTOS HTML
     * @param array $attrs
     * @return string
     *  
     */
    private static function ConvertAttrs(array $attrs)
    {
        $specialAttributes = [
            'autofocus' => 1,
            'autoplay' => 1,
            'async' => 1,
            'checked' => 1,
            'controls' => 1,
            'declare' => 1,
            'default' => 1,
            'defer' => 1,
            'disabled' => 1,
            'formnovalidate' => 1,
            'hidden' => 1,
            'ismap' => 1,
            'itemscope' => 1,
            'loop' => 1,
            'multiple' => 1,
            'muted' => 1,
            'nohref' => 1,
            'noresize' => 1,
            'novalidate' => 1,
            'open' => 1,
            'readonly' => 1,
            'required' => 1,
            'reversed' => 1,
            'scoped' => 1,
            'seamless' => 1,
            'selected' => 1,
            'typemustmatch' => 1,
        ];
        $buffer = '';
        $encode = true;
        if (isset($attrs['encode']))
        {
            $encode = $attrs['encode'];
            unset($attrs['encode']);
        }
        foreach ($attrs as $i => $v)
        {
            if (isset($specialAttributes[$i]))
            {
                if ($v === false && $i === 'async')
                {
                    $buffer .= ' ' . $i . '="false"';
                } elseif ($v)
                {
                    $buffer .= ' ' . $i;
                }
            } elseif ($v !== null)
            {

                if ($encode && $i != 'src' && $i != 'href')
                {
                    $buffer .= " " . $i . "='" . self::Encode($v) . "'";
                } else
                {
                    $buffer .= " " . $i . "='" . $v . "'";
                }
            }
        }
        return $buffer;
    }

    /**
     * ETIQUETAS ESPECIALES 
     */

    /**
     * CREA UN ETIQUETA SELECT 
     * 
     * @param array $attrs ATRIBUTOS DE LA ETIQUETA SELECT [attr=>value]
     * @param array|\Traversable $options ATRIBUTOS DE LAS ETIQUETAS OPTION  
     * <code>
     * [
     *      0=>[attr=>value,..],
     *      1=>[attr=>value,..],
     *      .
     *      .
     *      N=>[attr=>value,..]
     * ]</code>
     * @return string
     */
    public static function select($attrs = [], $options = [])
    {

        $tang = self::OpenTang('select', $attrs);

        foreach ($options as $i => $v)
        {
            $text = '';
            if (is_array($v) || $v instanceof \Traversable)
            {
                $atr = [];
                foreach ($v as $i => $v)
                    $atr[$i] = $v;
                if (isset($atr['text']))
                {
                    $text = $atr['text'];
                    unset($atr['text']);
                }
                if (!isset($atr['value']))
                    $atr['value'] = $i;
                if (isset($attrs['value']) && $attrs['value'] == $atr['value'])
                {
                    $atr['selected'] = true;
                }

                $tang.=self::Tang('option', $text, $atr);
            } elseif (is_numeric($i))
            {
                $atr = ['value' => $v];
                if (isset($attrs['value']) && $attrs['value'] == $atr['value'])
                {
                    $atr['selected'] = true;
                }
                $tang.=self::Tang('option', $v, $atr);
            } else
            {
                $atr = ['value' => $i];
                if (isset($attrs['value']) && $attrs['value'] == $atr['value'])
                {
                    $atr['selected'] = true;
                }
                $tang.=self::Tang('option', $i, $atr);
            }
        }
        $tang.=self::CloseTang('select');
        return $tang;
    }

    /**
     * CREA UNA ETIQUETA SCRIPT
     * @param array $text 
     * @param array $attrs
     * @return string
     *
     */
    public static function script($text = '', $attrs = [], $noCDATA = true)
    {
        $attrs = $attrs + ['type' => 'text/javascript'];
        if (is_string($text) && $text != '')
        {
            if ($noCDATA)
            {
                return self::Tang('script', self::cdata($text, true), $attrs);
            } else
            {
                return self::Tang('script', $text, $attrs);
            }
        } elseif (is_array($text))
        {
            return self::Tang('script', "", $text + ['type' => 'text/javascript']);
        } else
        {
            return self::Tang('script', $text, $attrs);
        }
    }

    /**
     * CREA UNA ETIQUETA STYLE
     * @param array $text 
     * @param array $attrs
     * @return string
     */
    public static function style($text = '', $attrs = [])
    {
        $attrs = $attrs + ['type' => 'text/css'];
        if (isset($attrs['src']))
        {
            $attrs+=[ 'rel' => "stylesheet"];
        }
        if (is_string($text) && $text != '')
        {
            return self::Tang('style', self::cdata($text, true), $attrs);
        } else
        {
            return self::Tang('style', $text, $attrs);
        }
    }

    /**
     * CREA UNA SECCION CDATA
     * @param string $text 
     * @param bool $comment INDICA SI SE COMENTARA 
     * @return string
     */
    public static function cdata($text, $comment = false)
    {
        if ($comment)
        {
            return '/*<![CDATA[*/' . $text . '/*]]>*/';
        } else
        {
            return '<![CDATA[' . $text . ']]>';
        }
    }

    public static function meta($attrs)
    {
        return self::OpenTang('meta', $attrs);
    }

}
