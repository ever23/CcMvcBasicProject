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

/**
 * Description of mysql
 * DRIVER PARA BASES DE DATOS DE MYSQL 
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>
 * @package Cc
 * @subpackage DataBase  
 * @category Drivers
 */
class mysql extends Drivers
{

    protected $ShowView = "show create view ";
    protected $ExplainSelect = "explain select * from ";
    protected $DescribeTable = "DESCRIBE ";
    protected $information_schema = 'SELECT * FROM information_schema.columns WHERE table_name=';
    public $Ttipe;

    public function __construct(\Cc\iDataBase $db, $tabla)
    {
        parent::__construct($db, $tabla);
        $this->_escape_char = '`';
    }

    public function keys($tab)
    {
        if ($this->keys_activ && $tab == $this->Tabla())
            return;
        $this->keys_activ = true;

        /* if($this->db->query($this->ShowView . $tab))
          {
          if($tab == $this->Tabla())
          {
          $this->Ttipe = self::view;
          }
          if($RESUT = $this->db->query($this->ExplainSelect . $tab))
          while($campo = $this->fecth_result($RESUT))
          {
          $this->keys($campo['table']);
          }
          } */

        if ($RESUT = $this->db->query($this->information_schema . "'" . $tab . "' and TABLE_SCHEMA='" . $this->db->dbName() . "'"))
        {
            if ($tab == $this->Tabla())
            {
                $this->Ttipe = self::table;
            }
            if ($this->num_rows($RESUT) == 0)
            {
                throw new Exception("LA TABLA " . $this->tabla . " NO EXISTE EN LA BASE DE DATOS");
            }
            while ($campo = $this->fecth_result($RESUT))
            {
                $this->OrderColum[$campo['ORDINAL_POSITION']] = $campo['COLUMN_NAME'];
                $this->colum+=[$campo['COLUMN_NAME'] => [
                        'Type' => $campo['COLUMN_TYPE'],
                        'TypeName' => '',
                        'KEY' => $campo['COLUMN_KEY'],
                        'Extra' => $campo['EXTRA'],
                        'Default' => $campo['COLUMN_DEFAULT'],
                        'Position' => $campo['ORDINAL_POSITION']
                ]];

                //array_push($this->colum,$campo['Field']);
                if ($campo['COLUMN_KEY'] == 'PRI' && !in_array($campo['COLUMN_NAME'], $this->primarykey))
                {
                    array_push($this->primarykey, $campo['COLUMN_NAME']);
                }

                if ($campo['EXTRA'] == 'auto_increment')
                {
                    $this->autoincrement = $campo['COLUMN_NAME'];
                }
            }
        } else
        {
            throw new Exception("LA TABLA " . $this->tabla . " NO EXISTE EN LA BASE DE DATOS");
        }

        if ($tab == $this->Tabla() && $this->Ttipe != self::view && $this->Ttipe != self::table)
        {
            $this->Ttipe = self::none;
        }
    }

//put your code here
}
