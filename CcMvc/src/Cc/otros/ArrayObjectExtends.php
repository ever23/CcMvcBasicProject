<?php

namespace Cc;

/**
 * @deprecated 
 * @package Cc
 * @subpackage otros
 */
class ArrayObjectExtends extends \ArrayObject
{

    public function getArrayCopyString()
    {

        return $this->CreateArray($this->getArrayCopy());
    }

    protected function CreateArray($array)
    {
        $var = '';
        foreach($array as $i => $v)
        {
            if(is_array($v) || is_object($v))
            {
                $var.="\n\"" . $i . '"=>' . $this->CreateArray($v) . ',';
                ;
            } else
            {
                $var.="\n\"" . $i . '"=>"' . $v . '",';
            }
        }
        return 'array(' . substr($var, 0, -1) . "\n)";
    }

}
