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
 */

namespace Cc\DB\MetaData;

/**
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>
 * @package Cc
 * @subpackage DataBase  
 * @category MetaData
 *
 */
class pgARRAY extends \ArrayObject implements iMetaData
{

    /**
     *
     * @var \Cc\DB\Drivers\pgsql 
     */
    protected $key;

    public function __construct($input = [], &$key)
    {
        $this->key = &$key;
        if (is_array($input) || is_object($input))
        {
            parent::__construct($input);
        } elseif (is_string($input))
        {
            $array = $this->Decode($input);
            parent::__construct($array);
        }
    }

    public function __toString()
    {
        return self::Encode($this->getArrayCopy());
    }

    private static function Format($var, $a = NULL)
    {
        if (is_null($var) || (is_string($var) && strtolower($var) == 'null'))
        {
            return 'NULL';
        } elseif (is_int($var) || is_float($var) || is_double($var))
        {
            return $var;
        } elseif (is_bool($var))
        {
            return $var ? 'true' : 'false';
        } elseif ((is_array($var) || $var instanceof \Traversable) && !$var instanceof \Cc\Json)
        {
            $t = '';
            foreach ($var as $v)
            {
                $t.=self::Format($v, 'array') . ',';
            }
            $t = '{' . substr($t, 0, -1) . '}';
            if ($a != 'array')
            {
                return "'" . $t . "'";
            } else
            {
                return $t;
            }
        } else
        {
            if ($a == 'array')
            {
                return '"' . $var . '"';
            }
            return "'" . $var . "'";
        }
    }

    public static function Encode(array $array)
    {
        return self::Format($array, 'array');
    }

    public static function Decode($arraystring, $reset = true)
    {
        static $i = 0;
        if ($reset)
            $i = 0;

        $matches = array();
        $indexer = 1;   // by default sql arrays start at 1
        // handle [0,2]= cases
        if (preg_match('/^\[(?P<index_start>\d+):(?P<index_end>\d+)]=/', substr($arraystring, $i), $matches))
        {
            $indexer = (int) $matches['index_start'];
            $i = strpos($arraystring, '{');
        }

        if ($arraystring[$i] != '{')
        {
            return NULL;
        }

        if (is_array($arraystring))
            return $arraystring;

        // handles btyea and blob binary streams
        if (is_resource($arraystring))
            return fread($arraystring, 4096);

        $i++;
        $work = array();
        $curr = '';
        $length = strlen($arraystring);
        $count = 0;
        $quoted = false;

        while ($i < $length)
        {
            // echo "\n [ $i ] ..... $arraystring[$i] .... $curr";

            switch ($arraystring[$i])
            {
                case '{':
                    $sub = self::Decode($arraystring, false);
                    if (!empty($sub))
                    {
                        $work[$indexer++] = $sub;
                    }
                    break;
                case '}':
                    $i++;
                    if (strlen($curr) > 0)
                        $work[$indexer++] = $curr;
                    return $work;
                    break;
                case '\\':
                    $i++;
                    $curr .= $arraystring[$i];
                    $i++;
                    break;
                case '"':
                    $quoted = true;
                    $openq = $i;
                    do
                    {
                        $closeq = strpos($arraystring, '"', $i + 1);
                        $escaped = $closeq > $openq &&
                                preg_match('/(\\\\+)$/', substr($arraystring, $openq + 1, $closeq - ($openq + 1)), $matches) &&
                                (strlen($matches[1]) % 2);
                        if ($escaped)
                        {
                            $i = $closeq;
                        } else
                        {
                            break;
                        }
                    } while (true);

                    if ($closeq <= $openq)
                    {
                        throw new Exception('Unexpected condition');
                    }

                    $curr .= substr($arraystring, $openq + 1, $closeq - ($openq + 1));

                    $i = $closeq + 1;
                    break;
                case ',':
                    if (strlen($curr) > 0)
                    {
                        if (!$quoted && (strtoupper($curr) == 'NULL'))
                            $curr = null;
                        $work[$indexer++] = $curr;
                    }
                    $curr = '';
                    $quoted = false;
                    $i++;
                    break;
                default:
                    $curr .= $arraystring[$i];
                    $i++;
            }
        }

        throw new Exception('Unexpected line end');
    }

    public function jsonSerialize()
    {
        return $this->getArrayCopy();
    }

}

/**
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>
 * @package Cc
 * @subpackage DataBase  
 * @category MetaData
 */
class json extends \Cc\Json implements iMetaData
{

    protected $key;

    public function __construct($json, $key)
    {
        $this->key = $key;
        if ($json instanceof \Cc\Json)
        {
            $this->Copy($json);
        }
        parent::__construct($json);
    }

}

/**
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>
 * @package Cc
 * @subpackage DataBase  
 * @category MetaData
 */
class xml extends \DOMDocument implements iMetaData
{

    protected $key;

    public function __construct($doc, $key)
    {
        $this->key = $key;
        parent::__construct();
        if ($doc instanceof \DOMDocument)
        {
            $this->loadXML($doc->saveXML());
        } else
        {
            $this->loadXML($doc);
        }
    }

    public function __toString()
    {
        return $this->saveXML();
    }

    public function jsonSerialize()
    {
        return $this->saveXML();
    }

}
