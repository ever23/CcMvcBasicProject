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
 * Description of DriverSqlite
 * DRIVER PARA SQLITER
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>
 * @package Cc
 * @subpackage DataBase  
 * @category Drivers
 * @todo se nesesita mejorar la obtencion de los atributos de las tablas de sqlite 
 */
class sqlite extends Drivers
{

    protected $keys_activ = false;
    protected $SqliteMaster = "select * from sqlite_master where  name=";

    /**
     * 
     * @param string $tab
     * @return boolean
     * @throws \Exception
     * 
     */
    public function keys($tab)
    {
        if ($this->keys_activ && $tab == $this->Tabla())
            return TRUE;
        $this->keys_activ = TRUE;
        if ($r = $this->db->query($this->SqliteMaster . "'" . $tab . "'"))
        {

            $result = $this->fecth_result($r);
            if (!$result)
            {
                throw new Exception("LA TABLA " . $this->tabla . " NO EXISTE EN LA BASE DE DATOS");
            }

            if ($result['type'] == 'table')
            {
                $this->Ttipe = self::table;
            } else
            {
                return true;
            }

            $sql = substr(substr($result['sql'], strlen('create table ' . $tab) + 2), 0, -1);
            $sql = preg_replace('/create table.*\(/i', '', $result['sql']);

            $rows = preg_split("/(\(.*[,]+.*\))?[,]/", trim($sql));
            $i = 0;
            $order = 1;
            foreach ($rows as $v)
            {

                if (!preg_match('/FOREIGN KEY/', $v))
                {
                    $fil = explode(" ", trim($v));
                    if (strtolower($fil[0]) != 'primary' && (isset($fil[1]) && strtolower($fil[1]) != 'key'))
                    {
                        //return $v;
                        $NULL = '';
                        $KEY = '';
                        $fil[0] = preg_replace("/[\"|\'|`]/", '', $fil[0]);
                        foreach ($fil as $j => $v)
                        {
                            if ($j = 0)
                                continue;;
                            if (strtolower($v) == 'not' && strtolower($fil[$j + 1]) == 'null')
                            {
                                $fil[$j] = '';
                                $fil[$j + 1] = '';
                                $NULL = 'NOT NULL';
                            }
                            if (strtolower($v) == 'primary')
                            {
                                $KEY = 'PRI';
                                if (!in_array($fil[0], $this->primarykey))
                                    array_push($this->primarykey, $fil[0]);
                            }
                        }
                        $this->OrderColum[$order++] = $fil[0];
                        $this->colum+=[$fil[0] => [
                                'Type' => $fil[1],
                                'KEY' => $KEY,
                                'Null' => $NULL,
                                'TypeName' => '',
                                'Extra' => NULL,
                                'Position' => $i
                        ]];
                        $i++;
                    }
                }
            }
            if (preg_match('/primary key \(.*\)/i', $sql, $m))
            {
                $primary = preg_replace('/(primary key \()|(\))/i', '', $m[0]);
                $keis = explode(',', $primary);
                foreach ($keis as $v)
                {
                    if (isset($this->colum[trim($v)]))
                    {
                        $this->colum[trim($v)]['KEY'] = 'PRI';
                        if (!in_array(trim($v), $this->primarykey))
                            array_push($this->primarykey, trim($v));
                    }
                }
            }
            return true;
        }else
        {

            return false;
        }
    }

//put your code here
}
