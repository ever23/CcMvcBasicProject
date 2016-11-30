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

namespace Cc\Mvc\Smarty;

/**
 * interface para plugins smarty source 
 * @package CcMvc
 * @subpackage Adapters
 */
interface PluginSource
{

    /**
     * 
     * @param string $rsrc_name nombre del templete 
     * @param mixes &$source
     * @param \Smarty $smarty
     */
    public function source($rsrc_name, &$source, \Smarty $smarty);

    public function timestamp($rsrc_name, &$timestamp, \Smarty $smarty);

    public function secure($rsrc_name, \Smarty $smarty);

    public function trusted($rsrc_name, \Smarty $smarty);
}

/**
 * interface para plugins smarty Function 
 * @package CcMvc
 * @subpackage Adapters
 */
interface PluginFunction
{

    public function PluginFunction($param, \Smarty_Internal_Template $smarty);
}

/**
 * si una clase implementa esta interface y es registrada como plugins todos sus metodos static seran registrados 
 * como funciones smarty
 * @package CcMvc
 * @subpackage Adapters
 */
interface PluginFunctionStaticAll
{
    
}

/**
 * interface para funciones de bloque smarty
 * @package CcMvc
 * @subpackage Adapters
 */
interface Pluginblock
{

    public function Pluginblock($params, $content, \Smarty_Internal_Template $smarty, &$repeat);
}

/**
 * si una clase implementa esta interface y es registrada como plugins todos sus metodos static seran registrados 
 * como funciones de bloque smarty
 * @package CcMvc
 * @subpackage Adapters
 */
interface PluginblockStaticAll
{
    
}

/**
 * interface para modificadores smarty
 * @package CcMvc
 * @subpackage Adapters
 */
interface PluginModifier
{

    public function PluginModifier($value, $params = NULL);
}

/**
 * si una clase implementa esta interface y es registrada como plugins todos sus metodos static seran registrados 
 * como modificadores smarty
 * @package CcMvc
 * @subpackage Adapters
 */
interface PluginModifierStaticAll
{
    
}

/**
 * interface para compiladores smarty
 * @package CcMvc
 * @subpackage Adapters
 */
interface PluginCompiler
{

    public function PluginCompiler($tang, \Smarty_Internal_Template $smarty);
}

/**
 * interface para prefiltros smarty
 * @package CcMvc
 * @subpackage Adapters
 */
interface PluginPretfilter
{

    public function PluginPrefilter($source, \Smarty_Internal_Template $smarty);
}

/**
 * interface para postfiltros smarty
 * @package CcMvc
 * @subpackage Adapters
 */
interface PluginPostfilter
{

    public function PluginPosfilter($source, \Smarty_Internal_Template $smarty);
}

/**
 * interface para Outputfilter smarty
 * @package CcMvc
 * @subpackage Adapters
 */
interface PluginOutputfilter
{

    public function PluginOutputfilter($out, \Smarty_Internal_Template $smarty);
}

/**
 * interface para Insert smarty
 * @package CcMvc
 * @subpackage Adapters
 */
interface PluginInsert
{

    public function PluginInsert($params, \Smarty_Internal_Template $smarty);
}

/**
 * si una clase implementa esta interface y es registrada como plugins todos sus metodos static seran registrados 
 * como inserts smarty
 * @package CcMvc
 * @subpackage Adapters
 */
interface PluginInsertStaticAll
{
    
}
