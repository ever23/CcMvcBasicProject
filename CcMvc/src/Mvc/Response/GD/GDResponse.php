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
use Cc\ImageGD;
use Cc\Cache;

/**
 * GDResponse Procesa respuestas para imagenes gif, jpg y png 
 * con la capacidad de redimencionar dinamicamente segun las variables _GET 
 * GDw ancho de la imagen 
 * GDh alto de la imagen 
 * GDc calidad de la imagen si jpg y si es png es la cantidad de compresion de la imagen
 * @author Enyerber Franco <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc
 * @subpackage Response
 */
class GDResponse implements ResponseConten
{

    /**
     * @var array Imagenes soportadas
     */
    const ImgSoported = [
        'image/gif',
        'image/jpeg',
        'image/png',
    ];

    /**
     *
     * @var Request
     */
    protected $request;

    /**
     *
     * @var array 
     */
    protected $RpImage = ['GDw', 'GDh', 'GDc', 'GDNoCookie', ''];

    /**
     *
     * @var \SplFileInfo archivo en cache 
     */
    protected $fileCache;

    /**
     *
     * @var bool indica si se almacena cache de imagenes dinamicas
     */
    protected $DinamicCache = false;

    /**
     *
     * @var int tiempo de expiaracion del cache del servidor
     */
    protected $CacheLifeTime = 3600;

    /**
     *
     * @var int ultima modificacion de la imagen dinamica 
     */
    protected $MTime = false;

    /**
     *
     * @var string directorio de cache  
     */
    protected $CacheDirectory = '';

    /**
     *
     * @var  ImageGD 
     */
    public $BufferGD = NULL;

    /**
     * 
     * @global string $ParamName nombre de paramentro
     * @return array
     * @throws Exception
     */
    public static function CtorParam()
    {
        global $ParamName;
        if (isset(Mvc::App()->Config()->Response['ExtencionContenType'][$ParamName]) && in_array(Mvc::App()->Config()->Response['ExtencionContenType'][$ParamName], self::ImgSoported))
        {
            Mvc::App()->ChangeResponseConten(Mvc::App()->Config()->Response['ExtencionContenType'][$ParamName]);

            return Mvc::App()->Response;
        } else
        {
//Mvc::App()->Response = new static(true);
//return Mvc::App()->Response;
            throw new Exception("LA extencion .$ParamName no esta soportada por " . static::class);
        }
        return [true, '{name_param}'];
    }

    /**
     * 
     * @param bool $compress
     * @param bool $param
     * @throws Exception
     */
    public function __construct($compress = true, $param = NULL)
    {

        /* if (!is_null($param))
          {
          if (isset(Mvc::App()->Config()->Response['ExtencionContenType'][$param]) && in_array(Mvc::App()->Config()->Response['ExtencionContenType'][$param], $this->imageSoported))
          {
          Mvc::App()->ChangeResponseConten(Mvc::App()->Config()->Response['ExtencionContenType'][$param]);
          Mvc::App()->Response = $this;
          } else
          {
          throw new Exception("LA extencion .$param no esta soportada por " . static::class);
          }
          } */
        $this->CacheDirectory = Mvc::App()->Config()->App['Cache'] . 'ImageGD' . DIRECTORY_SEPARATOR;
        Cache::AutoClearCacheFile($this->CacheDirectory);


        $this->request = &Mvc::App()->Request;

        Mvc::App()->Buffer->SetCompres($compress);
    }

    /**
     * Crea una imagen en el buffer 
     * @param  string|int $w si es int es el ancho de la imagen que se creara, si es un binario de imagen se cargara la misma, si es el nombre de el archivo de una imagen se cargaraint $w
     * @param int $h alto de la imagen
     * @param string $ContenType tipo de imagen
     */
    public function &CreateImageGd($w = NULL, $h = NULL, $ContenType = NULL)
    {
        $this->BufferGD = new ImageGD($w, $h, $ContenType);
        return $this->BufferGD;
    }

    /**
     * evia las cabeceras de cache para el navegador
     * @param int $time ultima modificacion
     * @param string $mime mime-type
     * @param string|int $lifetime tiempo de expiracion
     */
    public function CacheClient($time, $mime, $lifetime = '+1 day')
    {
        Router::HeadersReponseFiles($time, $mime, $lifetime);
    }

    /**
     * activa el cache para imagenes dinamicas 
     * @param bool $is true activado y false desactivado
     * @param string|int $lifetime tiempo de expiracion
     * @param int $Modifytime ultima modificacion
     */
    public function ActiveCache($is, $lifetime, $Modifytime)
    {
        $this->DinamicCache = $is && !Mvc::App()->IsDebung();
        if (is_string($Modifytime))
        {
            $this->CacheLifeTime = (new \DateTime($lifetime))->getTimestamp();
        } else
        {
            $this->CacheLifeTime = $lifetime;
        }

        $this->MTime = $Modifytime;
    }

    /**
     * @access private
     * @return array
     */
    public function GetLayaut()
    {

        return ['Layaut' => NULL, 'Dir' => NULL];
    }

    /**
     * ultimo procesado de la imagen
     * @param bynary $str
     * @return binary
     */
    public function ProccessConten($str)
    {
        if ($str == '' && $this->BufferGD instanceof \Cc\ImageGD)
        {
            $str = $this->BufferGD->Output('', 'S');
        }
        if (in_array(Mvc::App()->Content_type, self::ImgSoported) && (isset($_GET['GDw']) || isset($_GET['GDh']) || isset($_GET['GDc']) || isset($_COOKIE['GDmaxW']) || isset($_GET['GDtp'])))
        {

            return $this->ResampledImage($str, isset($_GET['GDw']) ? $_GET['GDw'] : NULL, isset($_GET['GDh']) ? $_GET['GDh'] : NULL, isset($_GET['GDc']) ? $_GET['GDc'] : NULL, isset($_GET['GDtp']) ? $_GET['GDtp'] : [255, 254, 255]);
        } elseif ($this->DinamicCache)
        {
            list($ancho, $alto) = getimagesizefromstring($str);
            return $this->CacheImgDinamic($ancho, $alto, NULL, $str);
        }
        return $str;
    }

    /**
     * redimenciona la imagen 
     * @param binary $image
     * @param int $nuevo_ancho
     * @param int $nuevo_alto
     * @param int $calidad
     *
     * @return bynary
     */
    protected function ResampledImage($image, $nuevo_ancho, $nuevo_alto, $calidad = NULL)
    {

// return $image;

        list($ancho, $alto) = getimagesizefromstring($image);


        if (substr($nuevo_ancho, -1, 1) == '%' && isset($_COOKIE['GDmaxW']))
        {
            $porcent = (int) substr($nuevo_ancho, 0, -1);

            $nuevo_ancho = ( $_COOKIE['GDmaxW'] * ($porcent * 0.01));
        }


        if (is_null($nuevo_ancho) && !is_null($nuevo_alto))
        {
            $proc = ($nuevo_alto * 100) / ( $alto);
            $nuevo_ancho = ( $ancho * ($proc * 0.01));
        } elseif (is_null($nuevo_alto) && !is_null($nuevo_ancho))
        {
            $proc = ($nuevo_ancho * 100) / ($ancho);
            $nuevo_alto = ($alto * ($proc * 0.01));
        }
        if (isset($_GET['GDNoCookie']) || !$this->AppConfig->Response['OptimizeImages'])
        {
            unset($_GET['GDNoCookie']);
//if (is_null($nuevo_alto) && is_null($nuevo_ancho) && is_null($calidad))
// return $image;
        }
        if (is_null($nuevo_alto) && is_null($nuevo_ancho))
        {
            $nuevo_alto = $alto;
            $nuevo_ancho = $ancho;
        }
        Mvc::App()->Log("GDResponse: w=" . $nuevo_ancho . " h=" . $nuevo_alto);
        if (!isset($_GET['GDNoCookie']) && (isset($_COOKIE['GDmaxW']) && $nuevo_ancho > $_COOKIE['GDmaxW']))
        {
            $alto = $nuevo_alto;
            $ancho = $nuevo_ancho;
            $nuevo_ancho = $_COOKIE['GDmaxW'];
            $proc = ($nuevo_ancho * 100) / ($ancho);
            $nuevo_alto = ($alto * ($proc * 0.01));
            Mvc::App()->Log("GDResponse Cookie: w=" . $nuevo_ancho . " h=" . $nuevo_alto);
///return $this->ResampledImage($image, $_COOKIE['GDmaxW'], $nuevo_alto)
        }
        $nuevo_ancho = (int) ($nuevo_ancho > 2000 ? 2000 : $nuevo_ancho);
        $nuevo_alto = (int) ($nuevo_alto > 2000 ? 2000 : $nuevo_alto);
        if (!is_numeric($calidad))
        {
            $calidad = NULL;
        } else
        {
            $calidad = (int) $calidad;
            $calidad = (int) ($calidad > 100 ? 100 : $calidad);
            $calidad = (int) ($calidad < 0 ? 0 : $calidad);
        }

        if ($alto == $nuevo_alto && $ancho == $nuevo_ancho)
        {
            if ($this->DinamicCache)
                return $this->CacheImgDinamic($nuevo_ancho, $nuevo_alto, $calidad, $image);
            return $image;
        }

//  return var_export(Mvc::App()->Router->InfoFile, true);
// $fondo = (int) $fondo;
        if (Mvc::App()->Router->InfoFile instanceof \SplFileInfo)
        {
            $c = $this->CacheImg($nuevo_ancho, $nuevo_alto, $calidad);
        }

// return 1;
//  imagecropauto($image, $mode, $threshold, $color)



        $IMG = new ImageGD($image);


//return $IMG->LoadString($image);
        $IMG->Resize($nuevo_ancho, $nuevo_alto);
//$IMG->ImportImgFormString('img', $image);
//   $IMG->PrintImg('img', 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto);
        switch (trim(Mvc::App()->Content_type))
        {
//case 'image/x-xbitmap':
            case 'image/gif':
                $out = $IMG->Output(NULL, "S");
                break;
            case 'image/jpeg':
                $out = $IMG->Output(NULL, "S", is_null($calidad) ? 100 : $calidad);
                break;
            case 'image/png':
                Mvc::App()->Buffer->SetCompres(false);
                $out = $IMG->Output(NULL, "S", is_null($calidad) ? 9 : $calidad);
                break;
            default :
                $IMG->destroy();
                $out = $image;
                break;
        }

        if (!Mvc::App()->IsDebung() && Mvc::App()->Router->InfoFile instanceof \SplFileInfo)
        {

            $cache = [];

            $cache['type'] = 'file';
            $cache['Controller'] = Mvc::App()->Router->InfoFile->__toString();
            $cache['RealFile'] = $this->fileCache->__toString();
            $cookie = '';
            if (isset($_COOKIE['GDmaxW']))
            {
                $cookie = 'COOKIE';
                $cookie.= $cache['GDmaxW'] = $_COOKIE['GDmaxW'];
            }
            Cache::Delete(Mvc::App()->GetNameStaticCacheRouter());
            Cache::Set(Mvc::App()->GetNameStaticCacheRouter() . $cookie, $cache);


            $out = $this->SaveFileCache($out);
            Router::HeadersReponseFiles($this->fileCache, Mvc::App()->Content_type, Mvc::App()->Config()->Router['CacheExpiresTime']);
        } elseif ($this->DinamicCache)
        {
            return $this->CacheImgDinamic($nuevo_ancho, $nuevo_alto, $calidad, $out);
        }
//  header('content-type: ' . Mvc::App()->Content_type);

        return $out;
    }

    /**
     * evia las headers de cache 
     * @param int $w
     * @param int $h
     * @param int $c
     * 
     * @return boolean
     */
    protected function CacheImg($w, $h, $c)
    {
        $name = 'w' . ((int) $w) . 'h' . ((int) $h) . 'c' . $c;
//$name .= str_replace(, "", );
        $name .= preg_replace("/^(" . preg_quote(dirname(Mvc::App()->GetExecutedFile()) . DIRECTORY_SEPARATOR, '/') . ")/i", "", Mvc::App()->Router->InfoFile->__toString());
        $name = str_replace(DIRECTORY_SEPARATOR, '.', $name);

//$name = Mvc::App()->Router->InfoFile->getBasename(Mvc::App()->Router->InfoFile->getExtension());

        $cache = $this->CacheDirectory;
        if (!is_dir($cache))
            mkdir($cache);
        $file = $cache . $name;

        $this->fileCache = new \SplFileInfo($file);
        if (!Mvc::App()->IsDebung() && file_exists($this->fileCache) && $this->fileCache->getMTime() >= Mvc::App()->Router->InfoFile->getMTime())
        {
            if (Router::HeadersReponseFiles($this->fileCache, Mvc::App()->Content_type, Mvc::App()->Config()->Router['CacheExpiresTime'], true))
            {
                return true;
            } else
            {
                return NULL;
            }
        }
        return FALSE;
    }

    /**
     * almacena el cache de imagenes dinamicas
     * @param int $w
     * @param int $h
     * @param int $c
     * @param binary $out
     */
    public function CacheImgDinamic($w, $h, $c, $out)
    {
        $name = 'w' . ((int) $w) . 'h' . ((int) $h) . 'c' . $c;
//$name .= str_replace(, "", );
        $name .= '.dinamic.';

        $name = $name . str_replace('/', '.', Mvc::App()->Request->BasePath());
        switch (trim(Mvc::App()->Content_type))
        {

            case 'image/gif':
                $name .='.gif';
                break;
            case 'image/jpeg':
                $name .='.jpg';
                break;
            case 'image/png':
                $name .='.png';
                break;
        }
//$name = Mvc::App()->Router->InfoFile->getBasename(Mvc::App()->Router->InfoFile->getExtension());

        $cache = $this->CacheDirectory;
        if (!is_dir($cache))
            mkdir($cache);
        $file = $cache . $name;

        $this->fileCache = new \SplFileInfo($file);




        $cache = [];
        $cache['type'] = 'Controllers';
        $cache['Controller'] = Mvc::App()->GetController();
        $cache['RealFile'] = $this->fileCache->__toString();
        $cache['LifeTime'] = $this->CacheLifeTime;
        $cache['Content-Type'] = Mvc::App()->Content_type;
        $cache['CacheClient'] = Mvc::App()->Config()->Router['CacheExpiresTime'];
        $cache['MTime'] = $this->MTime;
        $out = $this->SaveFileCache($out);
        Router::HeadersReponseFiles($this->MTime, Mvc::App()->Content_type, Mvc::App()->Config()->Router['CacheExpiresTime'], true);
// Router::HeadersReponseFiles($this->fileCache, Mvc::App()->Content_type, $this->CacheLifeTime);
        \Cc\Cache::Set(Mvc::App()->GetNameStaticCacheRouter(), $cache);
        return $out;
    }

    private function SaveFileCache($out)
    {
        //$compres= new \smushit();
        //$compres->compress($image);
        $f = fopen($this->fileCache, 'w');
        fwrite($f, $out);
        fclose($f);
        return $out;
    }

    /**
     * @access private
     * @param string $layaut
     * @param string $dirLayaut
     */
    public function SetLayaut($layaut, $dirLayaut = NULL)
    {
        
    }

}

/**
 * exepciones 
 */
class GDexception extends Exception
{
    
}
