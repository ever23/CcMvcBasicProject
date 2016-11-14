<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cc\Mvc;

/**
 * Description of ViewPHP
 *
 * @author usuario
 */
class ViewPHP implements ViewLoaderExt
{

    public function Fetch(&$context, $file, array $agrs)
    {
        ob_start();
        $this->Load($context, $file, $agrs);
        $conten = ob_get_contents();
        ob_end_clean();
        return $conten;
    }

    public function Load(&$context, $file, array $agrs)
    {
        if (!file_exists($file) && ($t = strpos($file, ':')) !== false)
        {
            $file = new \SplFileInfo(substr($file, $t + 1));
            if (!$file->isFile())
                throw new ViewLoaderException("El archivo " . $file->__toString() . " no existe");
        }

        $function = \Closure::bind(function($__agrs, $__file)
                {
                    extract($__agrs);
                    include ($__file);
                }, $context, get_class($context));
        $function($agrs, $file);
    }

//put your code here
}
