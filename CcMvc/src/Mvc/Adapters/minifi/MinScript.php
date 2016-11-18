<?php

/**
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

use MatthiasMullie\Minify\JS;

/**
 *  MinScript implementando la libreria \\MatthiasMullie\\Minify
 *
 * @author Enyerber Franco
 */
class MinScript extends \Cc\MinScript
{

    public function __construct($str = NULL, $type = 'html')
    {
        parent::__construct($str, $type);
    }

    protected function GetInfo()
    {
        return "\nPowered by CcMvc Framework <http://ccmvc.com.ve> \nCreate: " . date('Y-m-d H:i:s') . "\n";
    }

    protected function JsMin($js_script)
    {
        //  return '';
        if (class_exists("\\MatthiasMullie\\Minify\\JS"))
        {
            $min = new JS();
            $min->add($js_script);
            try
            {
                return $min->minify();
            } catch (\Exception $ex)
            {
                return $js_script;
            }
        } else
        {
            return parent::JsMin($js_script);
        }
    }

    public $file = NULL;

    protected function CssMin($css_script)
    {
        if (class_exists("\\MatthiasMullie\\Minify\\CSS"))
        {
            $min = new \Cc\Mvc\Minify\CSS();

            if ($this->file)
            {
                $min->addContext($css_script, $this->file);
            } else
            {
                $min->add($css_script);
            }

            try
            {
                return $min->minify();
            } catch (\Exception $ex)
            {

                return parent::CssMin($css_script);
            }
        } else
        {
            return parent::CssMin($css_script);
        }
    }

}
