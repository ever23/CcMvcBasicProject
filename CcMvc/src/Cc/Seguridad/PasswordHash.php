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

namespace Cc;

/**
 * CREA Y ADMINISTRA HASH DE CONTRASEÑAS DE USUARIOS 
 * @package Cc
 * @subpackage Seguridad
 */
class PasswordHash
{

    private $Algorim;
    private $Options;

    public function __construct($algo = PASSWORD_BCRYPT, $options = [])
    {
        $this->Algorim = $algo;
        $this->Options = $options;


        // echo $this->Options['cost'];
    }

    public function SetOptions(array $options)
    {
        $this->Options = $options;
    }

    public function CreateHash($password)
    {
        return password_hash($password, $this->Algorim, $this->Options);
    }

    public function VerifyPassword($password, $Hash)
    {
        return password_verify($password, $Hash);
    }

    public function VerifyUpdateAlgorim($password, $hash)
    {
        if(self::PasswordVerify($password, $hash))
        {
            if(password_needs_rehash($hash, $this->Algorim, $this->Options))
            {
                return password_hash($password, $this->Algorim, $this->Options);
            }
        }
        return NULL;
    }

    public function GenerateCost()
    {
        // return 10;
        $timeTarget = 0.05;
        $coste = 8;
        do
        {
            $coste++;
            $inicio = microtime(true);
            password_hash("test", $this->Algorim, ["cost" => $coste]);
            $fin = microtime(true);
        } while(($fin - $inicio) < $timeTarget);

        return $coste;
    }

}
