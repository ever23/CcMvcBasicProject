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
 *
 *  
 */

namespace Cc\Mvc;

use Cc\Mvc;
use Cc\Autoload;
use Cc\Cache;

/**
 * Adaptador TemplateLoad para cargar templetes smarty 
 *
 * @author Enyerber Franco
 * @package CcMvc
 * @subpackage Adapters
 */
class TemplateSmarty implements TemplateLoader
{

    /**
     * instancia de smarty
     * @var \Smarty
     */
    protected $smarty;

    /**
     * configuracion de smary
     * @var array 
     */
    protected $config;

    /**
     *
     * @var \Smarty 
     */
    public static $SmartyRef = NULL;

    /**
     * directorio de Plugins
     * @var string 
     */
    protected $PluginsDir = NULL;

    /**
     * directorio de configuracion 
     * @var string 
     */
    protected $ConfigDir = NULL;

    /**
     * 
     * @throws Exception si la clase \smarty no existe
     */
    public function __construct()
    {
        if (!class_exists("\\Smarty"))
        {
            throw new Exception("Se requieren de la libreria Smarty para cargar archivos .tpl");
        }
        $this->config = isset(Mvc::App()->Config()->SmartyConfig) ? Mvc::App()->Config()->SmartyConfig : [];
        if (!(self::$SmartyRef instanceof \Smarty))
        {
            self::$SmartyRef = new \Smarty();
            $this->smarty = &self::$SmartyRef;
            $this->ConfigSmarty();
        } else
        {
            $this->smarty = &self::$SmartyRef;
        }
    }

    /**
     * 
     * @param object $context
     * @param string $file
     * @param array $agrs
     * @return string
     * @throws \SmartyCompilerException
     * @throws Exception
     * @see TemplateLoader::Fetch()
     */
    public function Fetch(&$context, $file, array $agrs)
    {
        $this->smarty->clearAllAssign();
        $this->asingVars($agrs, $context);
        if (($t = strpos($file, ':')) === false)
        {
            $file = 'file:' . $file;
        }
        try
        {
            return $this->smarty->fetch($file, $this->smarty->cache_id, $this->smarty->compile_id);
        } catch (\SmartyCompilerException $ex)
        {
            throw $ex;
        } catch (\SmartyException $ex)
        {
            throw new Exception("Ocurrio un error al evaluar el archivo " . $file . " " . $ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * 
     * @param object $context
     * @param string $file
     * @param array $agrs
     * @return mixes
     * @throws \SmartyCompilerException
     * @see TemplateLoader::Load()
     */
    public function Load(&$context, $file, array $agrs)
    {
        $this->smarty->clearAllAssign();
        $this->asingVars($agrs, $context);
        if (($t = strpos($file, ':')) === false)
        {
            $file = 'file:' . $file;
        }
        try
        {
            return $this->smarty->display($file, $this->smarty->cache_id, $this->smarty->compile_id, $this->smarty);
        } catch (\SmartyCompilerException $ex)
        {
            throw $ex;
        }/* catch (\SmartyException $ex)
          {
          throw new Exception("Ocurrio un error al evaluar el archivo " . $file . " " . $ex->getMessage(), $ex->getCode(), $ex);
          } */
    }

    private function asingVars(array &$agrs, &$context)
    {
        foreach ($agrs as $i => $var)
        {
            if (is_object($var) && $var instanceof ParseObjectSmartyTpl)
            {
                $ag = $var->ParseSmaryTpl();
                $ag['allowed'] = isset($ag['allowed']) ? $ag['allowed'] : [];
                $ag['format'] = isset($ag['format']) ? $ag['format'] : true;
                $ag['block_methods'] = isset($ag['block_methods']) ? $ag['block_methods'] : [];
                $ag['object'] = (isset($ag['object']) ? $ag['object'] : $var);
                $this->smarty->registerObject($i, $ag['object'], $ag['allowed'], $ag['format'], $ag['block_methods']);
            }

            $this->smarty->assign($i, $var);
        }
        if (is_object($context) && $context instanceof ParseObjectSmartyTpl)
        {
            $ag = $context->ParseSmaryTpl();
            $ag['allowed'] = isset($ag['allowed']) ? $ag['allowed'] : [];
            $ag['format'] = isset($ag['format']) ? $ag['format'] : true;
            $ag['block_methods'] = isset($ag['block_methods']) ? $ag['block_methods'] : [];
            $ag['object'] = (isset($ag['object']) ? $ag['object'] : $context);
            $this->smarty->registerObject("this", $ag['object'], $ag['allowed'], $ag['format'], $ag['block_methods']);
        }


        $this->smarty->assign("this", $context);
    }

    private function ConfigSmarty()
    {
        $this->smarty->debugging = Mvc::App()->IsDebung() && $this->config['DebungConsole'];
        $this->smarty->setLeftDelimiter($this->config['LeftDelimiter']);
        $this->smarty->setRightDelimiter($this->config['RightDelimiter']);
        $cache = Mvc::App()->Config()->App['Cache'] . 'Smarty';
        $this->smarty->setCacheDir($cache . '/Cache');
        $this->smarty->setCompileDir($cache . '/Compile');
        $this->PluginsDir = Mvc::App()->Config()->App['app'] . $this->config['PluginsDir'];
        if (!is_dir($this->PluginsDir))
        {
            mkdir($this->PluginsDir);
        }
        $this->ConfigDir = Mvc::App()->Config()->App['app'] . $this->config['ConfigDir'];
        if (!is_dir($this->ConfigDir))
        {
            mkdir($this->ConfigDir);
        }
        $this->smarty->addPluginsDir($this->PluginsDir);
        $this->smarty->setConfigDir($this->ConfigDir);
        if (isset($this->config['Plugins']))
            foreach ($this->config['Plugins'] as $i => $plugin)
            {
                $this->RegisterPlugin($i, $plugin);
            }
        $this->LoadPlugins();
        if (is_object(Mvc::App()) && Mvc::App() instanceof ParseObjectSmartyTpl)
        {
            $ag = Mvc::App()->ParseSmaryTpl();
            $ag['allowed'] = isset($ag['allowed']) ? $ag['allowed'] : [];
            $ag['format'] = isset($ag['format']) ? $ag['format'] : true;
            $ag['block_methods'] = isset($ag['block_methods']) ? $ag['block_methods'] : [];
            $ag['object'] = (isset($ag['object']) ? $ag['object'] : Mvc::App());
            $this->smarty->registerObject("CcMvc", $ag['object'], $ag['allowed'], $ag['format'], $ag['block_methods']);
        }
        $this->smarty->assignGlobal('CcMvc', Mvc::App());

        if (Mvc::App()->SelectorController->GetController())
        {
            if (is_object(Mvc::App()->SelectorController->GetController()) && Mvc::App()->SelectorController->GetController() instanceof ParseObjectSmartyTpl)
            {
                $ag = Mvc::App()->SelectorController->GetController()->ParseSmaryTpl();
                $ag['allowed'] = isset($ag['allowed']) ? $ag['allowed'] : [];
                $ag['format'] = isset($ag['format']) ? $ag['format'] : true;
                $ag['block_methods'] = isset($ag['block_methods']) ? $ag['block_methods'] : [];
                $ag['object'] = (isset($ag['object']) ? $ag['object'] : Mvc::App()->SelectorController->GetController());
                $this->smarty->registerObject("Controller", $ag['object'], $ag['allowed'], $ag['format'], $ag['block_methods']);
            }
            $this->smarty->assignGlobal('Controller', Mvc::App()->SelectorController->GetController());
        }

        if (is_object(Mvc::App()->Request) && Mvc::App()->Request instanceof ParseObjectSmartyTpl)
        {
            $ag = Mvc::App()->Request->ParseSmaryTpl();
            $ag['allowed'] = isset($ag['allowed']) ? $ag['allowed'] : [];
            $ag['format'] = isset($ag['format']) ? $ag['format'] : true;
            $ag['block_methods'] = isset($ag['block_methods']) ? $ag['block_methods'] : [];
            $ag['object'] = (isset($ag['object']) ? $ag['object'] : Mvc::App()->Request);
            $this->smarty->registerObject("Request", $ag['object'], $ag['allowed'], $ag['format'], $ag['block_methods']);
        }
        $this->smarty->assignGlobal('Request', Mvc::App()->Request);
    }

    protected function RegisterPlugin($type, $plugin)
    {
        if (!in_array($type, ['function', 'modifier', 'block', 'compiler', 'prefilter', 'postfilter', 'outputfilter', 'resource', 'insert']))
        {
            return;
        }
        foreach ($plugin as $fn)
        {
            $this->smarty->registerPlugin($type, $fn['name'], $fn['implement'], isset($fn['cacheable']) ? $fn['cacheable'] : true, isset($fn['cache_attr']) ? $fn['cache_attr'] : NULL );
        }
    }

    private function LoadPlugins()
    {
        if (Mvc::App()->IsDebung() && file_exists($this->PluginsDir . \Cc\Autoload\FileCore))
        {
            @unlink($this->PluginsDir . \Cc\Autoload\FileCore);
        }
        $load = Autoload::Start($this->PluginsDir, false);
        $file = $load->GetFileCoreClass();

        $clases = include($file);

        foreach ($clases['class'] as $clas => $f)
        {

            $namespaces = explode('\\', $clas);

            if ((isset($namespaces[2]) && $namespaces[2] == 'Smarty'))
            {

                $name = $namespaces[3];
                $ref = new \ReflectionClass($clas);
                $this->SmartyPlugins($name, $clas, $ref);
            }
        }
        $load->Stop();
    }

    protected function SmartyPlugins($name, $clas, \ReflectionClass $ref)
    {
        if ($ref->implementsInterface(Smarty\PluginFunctionStaticAll::class))
        {
            $methods = $ref->getMethods(\ReflectionMethod::IS_STATIC);
            /* @var $m \ReflectionMethod */
            foreach ($methods as $m)
            {
                if ($m->isPublic())
                    $this->smarty->registerPlugin('function', $name . '_' . $m->getName(), [$clas, $m->getName()]);
            }
        }
        elseif ($ref->implementsInterface(Smarty\PluginblockStaticAll::class))
        {
            $methods = $ref->getMethods(\ReflectionMethod::IS_STATIC);
            /* @var $m \ReflectionMethod */
            foreach ($methods as $m)
            {
                if ($m->isPublic())
                    $this->smarty->registerPlugin('block', $name . '_' . $m->getName(), [$clas, $m->getName()]);
            }
        } elseif ($ref->implementsInterface(Smarty\PluginModifierStaticAll::class))
        {
            $methods = $ref->getMethods(\ReflectionMethod::IS_STATIC);
            /* @var $m \ReflectionMethod */
            foreach ($methods as $m)
            {
                if ($m->isPublic())
                    $this->smarty->registerPlugin('modifer', $name . '_' . $m->getName(), [$clas, $m->getName()]);
            }
        } elseif ($ref->implementsInterface(Smarty\PluginInsertStaticAll::class))
        {
            $methods = $ref->getMethods(\ReflectionMethod::IS_STATIC);
            /* @var $m \ReflectionMethod */
            foreach ($methods as $m)
            {
                if ($m->isPublic())
                    $this->smarty->registerPlugin('insert', $name . '_' . $m->getName(), [$clas, $m->getName()]);
            }
        }elseif ($ref->implementsInterface(Smarty\PluginSource::class))
        {
            $obj = new $clas ();
            $call = [
                [$obj, 'source'],
                [$obj, 'timestamp'],
                [$obj, 'secure'],
                [$obj, 'trusted']
            ];

            $this->smarty->registerResource($name, $call);
        } elseif ($ref->implementsInterface(Smarty\PluginFunction::class))
        {
            $obj = new $clas ();
            $call = [$obj, 'PluginFunction'];
            $this->smarty->registerPlugin('function', $name, $call);
        } elseif ($ref->implementsInterface(Smarty\Pluginblock::class))
        {
            $obj = new $clas ();
            $call = [$obj, 'Pluginblock'];
            $this->smarty->registerPlugin('block', $name, $call);
        } elseif ($ref->implementsInterface(Smarty\PluginModifier::class))
        {
            $obj = new $clas ();
            $call = [$obj, 'PluginModifier'];
            $this->smarty->registerPlugin('modifer', $name, $call);
        } elseif ($ref->implementsInterface(Smarty\PluginCompiler::class))
        {
            $obj = new $clas ();
            $call = [$obj, 'function'];
            $this->smarty->registerPlugin('function', $name, $call);
        } elseif ($ref->implementsInterface(Smarty\PluginPretfilter::class))
        {
            $obj = new $clas ();
            $call = [$obj, 'PluginPretfilter'];
            $this->smarty->registerPlugin('prefilter', $name, $call);
        } elseif ($ref->implementsInterface(Smarty\PluginPostfilter::class))
        {
            $obj = new $clas ();
            $call = [$obj, 'PluginPostfilter'];
            $this->smarty->registerPlugin('postfilter', $name, $call);
        } elseif ($ref->implementsInterface(Smarty\PluginOutputfilter::class))
        {
            $obj = new $clas ();
            $call = [$obj, 'PluginOutputfilter'];
            $this->smarty->registerPlugin('ouputfilter', $name, $call);
        } elseif ($ref->implementsInterface(Smarty\PluginInsert::class))
        {
            $obj = new $clas ();
            $call = [$obj, 'PluginInsert'];
            $this->smarty->registerPlugin('insert', $name, $call);
        }
    }

}

/**
 * interface para definir como smarty debe tratar el objeto que la implemente  
 * @author Enyerber Franco
 * @package CcMvc
 * @subpackage Adapters
 */
interface ParseObjectSmartyTpl extends iProtected
{

    /**
     * <code>
     * <?php
     * public function ParseSmaryTpl()
     * {
     *      return [
     *      'allowed'=>array,//lista de metodo que estaran disponibles en los templetes 
     *      'format'=>bool,// indica si el formato es tradicional o de etiquetas 
     *      'block_methods'=>array,// lista de funcion de bloques 
     * 
     *      ];
     * }
     * 
     * </code>
     * @return array 
     */
    public function ParseSmaryTpl();
}
