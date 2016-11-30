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

namespace Cc\Mvc\Twig;

use Cc\Mvc;
use Twig_Environment;
use Cc\Mvc\TemplateLoader;

/**
 *  Adaptador TemplateLoader para cargar templetes Twig 
 *
 * @author Enyerber Franco
 * @package CcMvc
 * @subpackage Adapters
 */
class TemplateLoader implements TemplateLoader
{

    /**
     * instancia de Twig_Environment
     * @var \Twig_Environment 
     */
    private static $TwigEnvironment;

    /**
     *
     * @var \Twig_Environment 
     */
    private $Twig;

    /**
     *
     * @var array 
     */
    private $config;

    /**
     * 
     * @throws Exception si la clase \Twig_Environment no existe 
     */
    public function __construct()
    {
        if (!class_exists("\\Twig_Environment"))
        {
            throw new Exception("Se requieren de la libreria Twig para cargar archivos .twig");
        }
        $this->config = isset(Mvc::App()->Config()->TwigConfig) ? Mvc::App()->Config()->TwigConfig : [];
        if (!(self::$TwigEnvironment instanceof Twig_Environment))
        {
            $twigConf = [
                'cache' => Mvc::App()->Config()->App['Cache'] . 'twig',
                'auto_reload' => Mvc::App()->IsDebung(),
                'strict_variables' => false,
                'autoescape' => false,
            ];
            $loader = new Loader();

            self::$TwigEnvironment = new \Twig_Environment($loader, $twigConf);
            self::$TwigEnvironment->addExtension(new Extencion\CcMvc());

            $this->LoadExtensions();
            if (isset($this->config['Lexer']))
            {
                $lexer = new \Twig_Lexer(self::$TwigEnvironment, $this->config['Lexer']);
                self::$TwigEnvironment->setLexer($lexer);
            }



            $this->Twig = &self::$TwigEnvironment;
        } else
        {
            $this->Twig = &self::$TwigEnvironment;
        }
    }

    /**
     * carga las extenciones del archivo de configuracion CcMvc
     */
    private function LoadExtensions()
    {
        if (isset($this->config['Extensiones']) && is_array($this->config['Extensiones']))
        {
            foreach ($this->config['Extensiones'] as $extClass)
            {
                if (!class_exists($extClass))
                {
                    throw new Exception("La extencion " . $extClass . " no se encontro ");
                }
                self::$TwigEnvironment->addExtension(new $extClass());
            }
        }
    }

    /**
     * 
     * @param array $agrs
     * @param object $context
     * @return bool
     */
    protected function &ProcessAgrs(&$agrs, &$context)
    {
        $agrs['this'] = &$context;
        return $agrs;
    }

    /**
     * 
     * @param object $context
     * @param string $file
     * @param array $agrs
     * @return string
     * @throws \Twig_Error_Loader
     * @see TemplateLoader::Fetch()
     */
    public function Fetch(&$context, $file, array $agrs)
    {
        try
        {
            return $this->Twig->render($file, $agrs);
        } catch (\Twig_Error_Loader $ex)
        {
            throw $ex;
        } catch (\Twig_Error_Syntax $ex)
        {
            throw $ex;
        }
    }

    /**
     * 
     * @param object $context
     * @param string $file
     * @param array $agrs
     * @return mixes
     * @throws \Twig_Error_Loader
     * @see TemplateLoader::Load()
     */
    public function Load(&$context, $file, array $agrs)
    {
        try
        {
            return $this->Twig->display($file, $agrs);
        } catch (\Twig_Error_Loader $ex)
        {
            throw $ex;
        } catch (\Twig_Error_Syntax $ex)
        {
            throw $ex;
        }
    }

//put your code here
}
