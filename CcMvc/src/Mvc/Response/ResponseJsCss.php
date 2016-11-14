<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cc\Mvc;

use Cc\Mvc;
use Cc\Cache;

/**
 * Description of ResponseCSS
 *
 * @author usuario
 */
class ResponseJsCss extends Response
{

    protected $filecache = '';
    protected $type = '';

    public function __construct($compres = false, $min = false)
    {
        if (Mvc::App()->Content_type == 'text/css')
        {
            $this->type = 'css';
            Cache::AutoClearCacheFile(Mvc::App()->Config()->App['Cache'] . 'Min' . $this->type . DIRECTORY_SEPARATOR);
            parent::__construct($compres, $min, 'css');
        } elseif (Mvc::App()->Content_type == 'text/javascript' || Mvc::App()->Content_type == 'application/javascript')
        {
            $this->type = 'js';
            Cache::AutoClearCacheFile(Mvc::App()->Config()->App['Cache'] . 'Min' . $this->type . DIRECTORY_SEPARATOR);
            parent::__construct($compres, $min, 'js');
        }
    }

    public function ProccessConten($conten)
    {

        if ($this->min && !Mvc::App()->IsDebung())
        {

            if (Mvc::App()->Router->InfoFile instanceof \SplFileInfo)
            {
                $name = Mvc::App()->Router->InfoFile->getBasename('.' . $this->type);

                if (substr($name, -4, 4) != '.min')
                {
                    try
                    {
                        Mvc::App()->Buffer->SetAutoMin(false);
                        return $this->CacheMin(Mvc::App()->Router->InfoFile, $conten);
                    } catch (Exception $ex)
                    {
                        return $conten;
                    }
                }
            } else
            {
                Mvc::App()->Buffer->SetAutoMin(true);
                Mvc::App()->Buffer->SetTypeMin($this->typeMin);
            }
        }

        return $conten;
    }

    public function CacheMin(\SplFileInfo $file, $conten)
    {
        $cache = Mvc::App()->Config()->App['Cache'] . 'Min' . $this->type . DIRECTORY_SEPARATOR;
        $f = dirname(Mvc::App()->GetExecutedFile()) . DIRECTORY_SEPARATOR;
        if (!is_dir($cache))
            mkdir($cache);
        $name = preg_replace("/^(" . preg_quote($f) . ")/i", "", $file->__toString());

        $name = str_replace(DIRECTORY_SEPARATOR, '.', $name);


        $this->fileCache = new \SplFileInfo($cache . $name);
        $cache = [];

        $cache['type'] = 'file';
        $cache['Controller'] = $file->__toString();
        $cache['RealFile'] = $this->fileCache->__toString();
        $min = new MinScript();
        $min->file = $file;
        $conten2 = $min->Min($conten, $this->type);
        $f = fopen($this->fileCache, 'w');
        fwrite($f, $conten2);
        fclose($f);

        Cache::Set(Mvc::App()->GetNameStaticCacheRouter(), $cache);
        Router::HeadersReponseFiles($this->fileCache, Mvc::App()->Content_type, Mvc::App()->Config()->Router['CacheExpiresTime']);
        return "/*create cache " . $name . "*/\n" . $conten2;
    }

}
