<?php

namespace Cc;

/**
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package Cc
 * @subpackage Exception
 * @internal   
 */
trait TranceHtml
{

    protected static $App;

    public static function TraceAsString($exeption = NULL, $mjs = NULL, $trace = NULL, $ini = 0, $end = NULL)
    {

        $file = $line = $code = NULL;
        if (is_array($exeption))
        {
            list($exeption, $file, $line, $code) = $exeption;
        } elseif (is_object($exeption))
        {
            /* @var $exeption \Exception */
            $file = $exeption->getFile();
            $line = $exeption->getLine();
            $code = $exeption->getCode();
            $mjs = $exeption->getMessage();
            $trace = $exeption->getTrace();
            $exeption = get_class($exeption);
        }
        if ($end < 0)
        {
            $end = count($trace) + $end;
        }

        $trace = array_slice($trace, $ini, $end);
        $c = count($trace);
        $mjs.="<br> en " . $file . " linea " . $line . " ";
        $a = "<pre><br/><font size='1'><table class='xdebug-error xe-notice'dir='ltr'border='1'cellspacing='0'cellpadding='1'><tr><th align='left' bgcolor='#f57900' colspan='5'>";
        $a.="<span style='background-color: #cc0000; color: #fce94f; font-size: x-large;'>(" . self::$App . ")</span><b style='font-size:18px;'>" . $exeption . " </b><bR>";
        $a.="<i>" . $mjs . "</i></th></tr>";
        if (count($trace) > 0)
        {
            $a.= "<tr><th align='left' bgcolor='#e9b96e' colspan='5'>Pila de llamadas</th></tr><tr><th align='center' bgcolor='#eeeeec' width='10'>#</th>";
            $a.="<th align='left' bgcolor='#eeeeec'>function</th><th align='left' bgcolor='#eeeeec'>archivo</th><th align='left' bgcolor='#eeeeec'>linea</th></tr>";
        }

        foreach ($trace as $i => $trance)
        {
            //  $trance = $trace[$i];
            $type = (!empty($trance['type']) ? $trance['type'] : '');
            $a.="<tr><td bgcolor='#eeeeec' align='center' width='10'>" . $i . "</td>";
            $a.="<td bgcolor='#eeeeec'>" . (!empty($trance['class']) ? $trance['class'] . $type : '') . $trance['function'] . "(" . (isset($trance['args']) ? self::Implode($trance['args']) : '') . ")</td>";
            $a.="<td title='" . (empty($trance['file']) ? '' : $trance['file']) . "' bgcolor='#eeeeec'>" . (empty($trance['file']) ? '' : $trance['file']) . "</td><th align='left' bgcolor='#eeeeec'>" . (empty($trance['line']) ? '' : $trance['line']) . "</th></tr>";
        }
        $a.="</table></font></pre>";
        return $a;
    }

    private static function Implode(array $array)
    {
        $a = '';

        foreach ($array as $i => $v)
        {
            if (is_string($v))
            {
                if (strlen($v) < 100)
                {
                    $a.=$v . ',';
                } else
                {
                    $a.=substr($v, 0, 100) . ',';
                }
            } elseif (is_object($v))
            {
                if (method_exists($v, '__debugInfo'))
                {
                    $a.='Object# ' . (get_class($v)) . ' ' . substr((self::CreateArray($v->__debugInfo())), 0, 100) . ' ...,';
                } else
                {
                    $a.='Object# ' . (get_class($v)) . ',';
                }
            } elseif (is_array($v))
            {
                $a.=substr(self::CreateArray($v), 0, 100) . ' ...,';
            } elseif (is_null($v))
            {
                $a.='NULL,';
            } else
            {
                $a.=$v . ',';
            }
        }
        return substr($a, 0, strlen($a) - 1);
    }

    private static function CreateArray($array)
    {
        $var = '';
        foreach ($array as $i => $v)
        {
            if (is_array($v) || is_object($v))
            {
                $var.="\n\"" . $i . '"=>' . self::CreateArray($v) . ',';
                ;
            } else
            {
                $var.="\n\"" . $i . '"=>"' . $v . '",';
            }
        }
        return 'array(' . substr($var, 0, -1) . "\n)";
    }

}
