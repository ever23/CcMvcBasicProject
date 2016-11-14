<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cc\Mvc;

use MatthiasMullie\Minify\JS;

/**
 * Description of MinScript
 *
 * @author usuario
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
