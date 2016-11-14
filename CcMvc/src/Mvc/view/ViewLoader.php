<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cc\Mvc;

/**
 * Description of FileEvaluator
 *
 * @author usuario
 */
class ViewLoader
{

    protected $Config;
    protected $evaluadores = [];
    protected $DefaultLoader = [];

    public function __construct(Config $c)
    {
        $this->Config = $c;
        if (isset($c['ViewLoaders']))
        {
            $this->evaluadores = $c['ViewLoaders']['Loaders'];
            $this->DefaultLoader = $c['ViewLoaders']['Default'];
        }
    }

    public function Load(&$context, $file, array $agrs)
    {
        $splfile = new \SplFileInfo($file);
        if ((strpos($file, ':') !== false))
        {
            return $this->Evaluate($context, $splfile, $agrs);
        }
        if (!$splfile->isFile())
        {
            $splfile = new \SplFileInfo($file . '.' . $this->DefaultLoader['ext']);
            if (!$splfile->isFile())
                throw new Exception("El archivo " . $file . " no existe");
        }
        return $this->Evaluate($context, $splfile, $agrs);
    }

    public function Fetch(&$context, $file, array $agrs)
    {
        $splfile = new \SplFileInfo($file);
        if ((strpos($file, ':') !== false))
        {
            return $this->LoadFetch($context, $splfile, $agrs);
        }
        if (!$splfile->isFile())
        {
            $splfile = new \SplFileInfo($file . '.' . $this->DefaultLoader['ext']);
            if (!$splfile->isFile())
                throw new ViewLoaderException("El archivo " . $file . " no existe");
        }
        return $this->LoadFetch($context, $splfile, $agrs);
    }

    protected function LoadFetch(&$context, \SplFileInfo $file, array $agrs)
    {
        $ext = $file->getExtension();

        if (isset($this->evaluadores[$ext]))
        {

            $eval = $this->FactoryLoaders($ext);
            return $eval->Fetch($context, $file->__toString(), $agrs);
        } else
        {
            $eval = $this->FactoryLoaders();
            return $this->Fetch($context, $file, $agrs);
        }
    }

    protected function Evaluate(&$context, \SplFileInfo $file, array $agrs)
    {
        $ext = $file->getExtension();

        if (isset($this->evaluadores[$ext]))
        {
            $eval = $this->FactoryLoaders($ext);
            return $eval->Load($context, $file->__toString(), $agrs);
        } else
        {
            $eval = $this->FactoryLoaders();
            return $eval->Load($context, $file->__toString(), $agrs);
        }
    }

    private function FactoryLoaders($ext = NULL)
    {
        if (!is_null($ext))
        {

            $class = $this->evaluadores[$ext]['class'];
            $param = isset($this->evaluadores[$ext]['param']) && is_array($this->evaluadores[$ext]['param']) ? $this->evaluadores[$ext]['param'] : [];
        } else
        {
            $class = $this->DefaultLoader['class'];
            $param = isset($this->DefaultLoader['param']) && is_array($this->DefaultLoader['param']) ? $this->DefaultLoader['param'] : [];
        }
        return new $class(...$param);
    }

}

class ViewLoaderException extends Exception
{
    
}

interface ViewLoaderExt
{

    /**
     * 
     * @param object $context
     * @param string $file
     * @param file $agrs
     */
    public function Load(&$context, $file, array $agrs);

    public function Fetch(&$context, $file, array $agrs);
}
