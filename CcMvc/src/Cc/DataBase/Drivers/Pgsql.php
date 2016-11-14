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

namespace Cc\DB\Drivers;

use Cc\DB\Drivers;
use Cc\DB\Exception;

include_once dirname(__FILE__) . '/../TypeMetaData/pgMetaData.php';

/**
 * Description of Pgsql
 * DRIVER PARA POSGRESQL experimental
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>
 * @package Cc
 * @subpackage DataBase  
 * @category Drivers
 */
class pgsql extends Drivers
{

    protected $information_schema = 'SELECT * FROM information_schema.columns WHERE table_name=';
    protected $keys_activ = false;

    public function __construct(\Cc\iDataBase $db, $tabla)
    {
        parent::__construct($db, $tabla);
        $this->_escape_char = '"';
    }

    public function keys($tab)
    {
        $this->Ttipe = self::table;

        if ($RESUT = $this->db->query($this->information_schema . "'" . $tab . "' and table_schema='" . $this->db->dbName() . "'"))
        {
            if ($this->num_rows($RESUT) == 0)
            {
                throw new Exception("LA TABLA " . $this->tabla . " NO EXISTE EN LA BASE DE DATOS");
            }
            while ($campo = $this->fecth_result($RESUT))
            {
                $this->OrderColum[$campo['ordinal_position'] - 1] = $campo['column_name'];
                $this->colum+=[$campo['column_name'] => [
                        'Type' => $campo['data_type'],
                        'TypeName' => $campo['udt_name'],
                        'KEY' => '',
                        'Extra' => '',
                        'Default' => '',
                        'Position' => $campo['ordinal_position']
                ]];
            }
        }
        if ($RESUT = $this->db->query("SELECT * FROM information_schema.key_column_usage 
 WHERE table_name='" . $tab . "'"))
        {
            while ($campo = $this->fecth_result($RESUT))
            {
                if ($campo['constraint_name'] == $tab . " pkey")
                {
                    array_push($this->primarykey, $campo['column_name']);
                }
            }
        }
    }

    public function CreateKeys()
    {
        parent::CreateKeys();
    }

    public function ProtectIdentifiers($item, $prefix_single = FALSE, $protect_identifiers = NULL, $field_exists = TRUE)
    {
        $item2 = $item;
        $posItem = '';
        if (preg_match('/\[\d\]/', $item))
        {

            $item = preg_replace('/\[\d\]/', '', $item);
            $posItem = substr($item2, strlen($item));
        }
        return parent::ProtectIdentifiers($item, $prefix_single, $protect_identifiers, $field_exists) . $posItem;
    }

    /**
     * 
     * @param string $ColumName
     * @param mixes $str
     * @return mixes
     * 
     */
    public function SerializeType($ColumName, $str)
    {

        $ColumName = preg_replace('/\[\d\]/', '', $ColumName);

        if (!key_exists($ColumName, $this->colum))
            return $str;

        $class_name = "\\Cc\\DB\\MetaData\\pg" . $this->colum[$ColumName]['Type'];

        if (!is_null($str) && class_exists($class_name, false))
        {
            $obj = new $class_name($str, $this->colum[$ColumName]['TypeName']);
            return $obj->__toString();
        } else
        {
            return parent::SerializeType($ColumName, $str);
        }
    }

    /**
     * 
     * @param type $ColumName
     * @param type $str
     * @return type
     */
    public function UnserizaliseType($ColumName, $str)
    {
        $ColumName = preg_replace('/\[\d\]/', '', $ColumName);

        if (!key_exists($ColumName, $this->colum))
            return $str;
        $class_name = "\\Cc\\DB\\MetaData\\pg" . $this->colum[$ColumName]['Type'];
        if (!is_null($str) && class_exists($class_name, false))
        {
            $obj = new $class_name($str, $ColumName);
            return $obj;
        } else
        {
            return parent::UnserizaliseType($ColumName, $str);
        }
    }

}
