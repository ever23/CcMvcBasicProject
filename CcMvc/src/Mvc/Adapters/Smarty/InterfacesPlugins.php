<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cc\Mvc\Smarty;

interface PluginSource
{

    public function source($rsrc_name, &$source, \Smarty $smarty);

    public function timestamp($rsrc_name, &$timestamp, \Smarty $smarty);

    public function secure($rsrc_name, \Smarty $smarty);

    public function trusted($rsrc_name, \Smarty $smarty);
}

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
