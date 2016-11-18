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
 * interface para lugins smarty Function 
 */
interface PluginFunction
{

    public function PluginFunction($param, \Smarty_Internal_Template $smarty);
}

interface PluginFunctionStaticAll
{
    
}

interface Pluginblock
{

    public function Pluginblock($params, $content, \Smarty_Internal_Template $smarty, &$repeat);
}

interface PluginblockStaticAll
{
    
}

interface PluginModifier
{

    public function PluginModifier($value, $params = NULL);
}

interface PluginModifierStaticAll
{
    
}

interface PluginCompiler
{

    public function PluginCompiler($tang, \Smarty_Internal_Template $smarty);
}

interface PluginPretfilter
{

    public function PluginPrefilter($source, \Smarty_Internal_Template $smarty);
}

interface PluginPostfilter
{

    public function PluginPosfilter($source, \Smarty_Internal_Template $smarty);
}

interface PluginOutputfilter
{

    public function PluginOutputfilter($out, \Smarty_Internal_Template $smarty);
}

interface PluginInsert
{

    public function PluginInsert($params, \Smarty_Internal_Template $smarty);
}

interface PluginInsertStaticAll
{
    
}
