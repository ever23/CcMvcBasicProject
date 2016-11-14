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

namespace Cc\Mvc;

use Cc\PasswordHash;
use Cc\Cache;
use Cc\Mvc;

/**
 * Description of AuteticateUserDB
 * Provee una interface abstracta para clases Autenticadoras de usuarios usando la base de datos
 * incluye seguridad de contraseñas 
 * @author Enyerber Franco
 * @package CcMvc
 * @subpackage Session
 * @example ../protected/model/AutenticateCcMvc.php EJEMPLO DE UNA CLASE AUTENTICADORA
 * @method void OnFailed() OnFailed(mixes ...$_)  ESTE METODO SE EJECUTARA CUANDO LA AUTENTICACION FALLE
 * @method void OnSuccess() OnSuccess(mixes ...$_)  ESTE METODO SE EJECUTARA CUANDO LA AUTENTICACION TENGA EXITO.
 * @uses Autenticate 
 * 
 */
abstract class AuteticateUserDB extends Autenticate
{

    /**
     * 
     * @var \Cc\PasswordHash 
     */
    protected $PasswordHash;

    /**
     * cache que sera almacenado 
     * @var array
     */
    private $cache;

    /**
     * opciones para {@see \Cc\PasswordHash::SetOptions()}
     * @var array
     */
    private $OptionHash = [];

    /**
     * nombre de la columna que almacena el nombre de usuario en la tabla de usuarios
     * @var string
     */
    private $ColUserName;

    /**
     * nombre de la columna que almacena la contraseña de usuario en la tabla de usuarios
     * @var string
     */
    private $ColPassword;

    /**
     * nombre de la columna que almacena el nombre de  la tabla de usuarios
     * @var string
     */
    private $TabUsers;
    private $ColUserType = NULL;
    public $ErrorDB;
    public $ErrnoDB;

    /**
     *
     * @var DBtabla 
     */
    protected $DBtabla;

    const CollUser = 'CollUser';
    const CollPassword = 'CollPassword';
    const TablaUsers = 'Tabla';
    const CollUserType = 'CollUserType';
    const DenyAccessForUser = 0x10;
    const FailedDataBase = 0x11;

    /**
     * 
     * @param array $exeptions
     */
    public function __construct($exeptions = ['*/*/*'])
    {
        $this->PasswordHash = new PasswordHash();
        $this->cache = Cache::IsSave(static::class) ? Cache::Get(static::class) : [];
        $aut = $this->InfoUserDB();
        $this->ColPassword = $aut[self::CollPassword];
        $this->ColUserName = $aut[self::CollUser];
        $this->TabUsers = $aut[self::TablaUsers];
        $coll = [$this->ColUserName, $this->ColPassword, 'CreadorHashClass'];
        if (isset($aut[self::CollUserType]))
        {
            $this->ColUserType = $aut[self::CollUserType];
            $coll[] = $this->ColUserType;
        }

        parent::__construct(is_null($exeptions) ? [] : $exeptions, $coll);
    }

    /**
     * 
     * indica si la session actual fue iniciada por u tipo de usuario especifico
     * si no se pasa el parametro $type indicara solamente si hay algun usuario registrado en la session
     * @param string $type tipo de usuario 
     * @return boolean
     */
    public function IsUser($type = '*')
    {
        if (is_null($this->ColUserType))
        {
            return (bool) $this->verifica();
        }
        if ($this->offsetExists($this->ColUserType))
        {
            if ($type == '*')
            {
                return true;
            } elseif ($this->offsetGet($this->ColUserType) == $type)
            {
                return true;
            }
        }

        return false;
    }

    private function LoadDBTabla()
    {
        if (!($this->DBtabla instanceof \Cc\DBtabla))
        {
            $this->DBtabla = $this->CreateDBTabla();
        }
    }

    public function CreateDBTabla()
    {
        return Mvc::App()->DataBase()->Tab($this->TabUsers);
    }

    /**
     * debe retornar un array asociativo con el nombre de la tabla en la base de datos que almacena los usuarios, el nombre de la columna de nombre se usuario , el nombre de la columna de contraseña 
     * <code><?php
     * protected function InfoUserDB()
     * {
     *      return [
     *                 self::TablaUsers=>'TablaDeUsuarios',
     *                 self::CollUser=>'ColumnaDeNombreDeUsuario',
     *                 self::CollPassword=>'ColumnaDeContraseña',
     *                 self::CollUserType=>''  //opcional 
     *              ];
     * }
     * </code>
     * @return array 
     * @uses TablaUsers SE USARA CON INDICE PARA EL NOMBRE DE LA TABLA DE USUARIOS EN LA BASE DE DATOS 
     * @uses CollPassword SE USARA COMO INDICE PARA EL NOMBRE DE LA COLUMNA EN LA TABLA DE USUARIOS QUE ALMACENARA EL HASH DE CONTRASEÑA 
     * @uses CollUser SE USARA COMO INDICE PARA EL NOMBRE DE LA COLUMNA EN LA TABLA DE USUARIOS QUE ALMACENARA EL NOMBRE DE USUARIO O IDENTIFICADOR UNICO 
     * @uses CollUserType SE USARA COMO INDICE PARA EL TIPO DE USUARIO DE LA COLUMNA DE LA TABLA DE USUARIOS EN LA BASE DE DATOS
     */
    abstract protected function InfoUserDB();

    /**
     * INSERTA UN USUARIO EN LA BASE DE DATOS CREANDO UN HASH SEGURO PARA LA CONTRASEÑA 
     * @param string|array|\ArrayAccess $password si es un string sera tomada como la contraseña en la tabla de usuarios
     * si es un array sera tomado como el parametro $columsUser
     * @param array|\ArrayAccess $columsUser la columna a insetar en la tabla de usuarion en la base de datos debe ser un array de tipo [columna=>valor] 
     * @return bool si no ocurrio ningun error retornara true de lo contrario false
     */
    public function CreteUser($password, $columsUser = [])
    {
        $this->LoadDBTabla();
        if (is_array($password))
        {
            $columsUser = $password;
            $password = $columsUser[$this->ColPassword];
        }

        /* @var $row DBRow */
        $row = $this->DBtabla->NewRow();
        foreach ($this->DBtabla->GetCol() as $i => $v)
        {
            if (isset($columsUser[$i]))
                $row[$i] = $columsUser[$i];
        }
        $this->MejorCosto();
        $this->PasswordHash->SetOptions($this->OptionHash);
        $row[$this->ColPassword] = $this->PasswordHash->CreateHash($password);
        if ($row->Insert())
        {
            return true;
        } else
        {
            $this->ErrnoDB = $this->DBtabla->errno;
            $this->ErrorDB = $this->DBtabla->error;
            return false;
        }
    }

    /**
     * EDITA LA INFORMACION DE UN USUARIO EN LA BASE DE DATOS CREANDO UN HASH SEGURO PARA LA CONTRASEÑA
     * @param string|array|\ArrayAccess $password si es un string sera tomada como la contraseña en la tabla de usuarios
     * si es un array sera tomado como el parametro $columsUser
     * @param array|\ArrayAccess $columsUser la columna a insetar en la tabla de usuarion en la base de datos deve ser un array de tipo [columna=>valor] 
     * @return bool si no ocurrio ningun error retornara true de lo contrario false
     */
    public function UpdateUser($password, $columsUser = [])
    {
        $this->LoadDBTabla();
        if (is_array($password))
        {
            $columsUser = $password;
            $password = $columsUser[$this->ColPassword];
        }
        $row = $this->DBtabla->NewRow();
        foreach ($this->DBtabla->GetCol() as $i => $v)
        {
            $row[$i] = $columsUser[$i];
        }
        $this->MejorCosto();
        $this->PasswordHash->SetOptions($this->OptionHash);
        $row[$this->ColPassword] = $this->PasswordHash->CreateHash($password);
        if ($row->Update())
        {
            return true;
        } else
        {
            $this->ErrnoDB = $this->DBtabla->errno;
            $this->ErrorDB = $this->DBtabla->error;
            return false;
        }
    }

    /**
     * EDITA LA CONTRASEÑA DEL USUARIO ACTIVO EN LA SESSION
     * nota: una ves cambiada la contraseña la session se destruye
     * @param string $LastPass contraseña anterior 
     * @param string $newPass nueva contraseña 
     * @return bool|NULL si ocurre un error en la base de datos retornara null, en caso que la autenticacion falle retornara false y si se cambia la contraseña con exito retorna true
     * 
     */
    public function UpdatePassword($LastPass, $newPass)
    {
        $this->LoadDBTabla();
        $ColUserName = $this->ColUserName;
        $ColHash = $this->ColPassword;
        if (!$this->DBtabla->Select($ColUserName . "='" . $this[$ColUserName] . "'", [], NULL, NULL, NULL, 1))
        {
            $this->ErrnoDB = $this->DBtabla->errno;
            $this->ErrorDB = $this->DBtabla->error;
            return NULL;
        }
        if ($this->DBtabla->num_rows != 1)
        {
            return false;
        }
        $user = $this->DBtabla->fetch();
        $this->DBtabla->FreeResult();
        if ($this->PasswordHash->VerifyPassword($LastPass, $user[$ColHash]))
        {
            $this->MejorCosto();
            $this->PasswordHash->SetOptions($this->OptionHash);
            $user[$this->ColPassword] = $this->PasswordHash->CreateHash($newPass);
            if ($user->Update())
            {
                $this->Destroy();
                return true;
            } else
            {
                $this->ErrnoDB = $this->DBtabla->errno;
                $this->ErrorDB = $this->DBtabla->error;
                return NULL;
            }
        }
        return false;
    }

    /**
     * VERIFICA E INICIA LA SESSION DE UN USUARIO Y CONTRASEÑA DADO 
     * @param string $username nombre de usuario
     * @param string $password contraseña de usuario
     * @return boolean TRUE SI LA CONTRASEÑA COINCIDE CON EL NOMBRE DE LO CONTRARIO FALSE, SI OCURRE UN ERROR EN LA BASE DE DATOS RETORNA NULL
     */
    public function Login($username, $password)
    {

        $this->LoadDBTabla();
        $ColUserName = $this->ColUserName;
        $ColHash = $this->ColPassword;
        if (!$this->DBtabla->Select($ColUserName . "='" . $username . "'", [], NULL, NULL, NULL, 1))
        {
            $this->ErrnoDB = $this->DBtabla->errno;
            $this->ErrorDB = $this->DBtabla->error;
            return NULL;
        }
        if ($this->DBtabla->num_rows != 1)
        {
            /* echo '<br><br><pre>';
              var_dump($username, $password);
              echo $this->DBtabla->num_rows, $this->DBtabla->sql, '</pre>'; */
            return false;
        }
        $row = $this->DBtabla->fetch();
        $this->DBtabla->FreeResult();

        if ($this->PasswordHash->VerifyPassword($password, $row[$ColHash]))
        {
            $this->Del();
            $this->TingerEvent('OnSessionRegister');
            foreach ($row as $i => $v)
            {
                $this->offsetSet($i, $v);
            }
            $this->offsetSet($ColUserName, $row[$ColUserName]);
            $this->offsetSet('CreadorHashClass', static::class);
            $this->offsetSet($ColHash, $row[$ColHash]);
            if (!is_null($this->ColUserType))
            {
                $this->offsetSet($this->ColUserType, $row[$this->ColUserType]);
            }
            if ($this->TingerEvent('OnSuccess') === false)
            {
                return false;
            }
            return true;
        } else
        {

            return false;
        }
    }

    /**
     * elimina los datos del usuario de la session 
     */
    public function CloseSessionUser($destroy = false)
    {
        $this->offsetUnset($this->ColUserName);
        $this->offsetUnset('CreadorHashClass');
        $this->offsetUnset($this->ColPassword);
        $this->offsetUnset($this->ColUserType);
        if ($destroy)
            $this->Destroy();
    }

    /**
     * realiza las operaciones de autenticacion 
     * verificando si el usuario en session existen en la base de datos  
     * @return array 
     * @internal 
     */
    protected function Autentica()
    {
        $this->LoadDBTabla();
        if ($this->offsetExists($this->ColUserName) && $this->offsetGet($this->ColPassword) && $this->offsetGet('CreadorHashClass'))
        {

            if ($this->DBtabla->Select($this->ColUserName . "='" . $this->offsetGet($this->ColUserName) . "' and " . $this->ColPassword . "='" . $this->offsetGet($this->ColPassword) . "'"))
            {
                if ($this->DBtabla->num_rows === 1)
                {

                    $usuario = $this->DBtabla->fetch();
                    $this->DBtabla->FreeResult();
                    $ath = [$this->ColUserName => $usuario[$this->ColUserName], $this->ColPassword => $usuario[$this->ColPassword], 'CreadorHashClass' => static::class] + $usuario->GetRow();

                    if (!is_null($this->ColUserType))
                    {

                        if (!$this->UserTypeAccess($usuario[$this->ColUserType]))
                        {
                            $this->TingerFailed(self::DenyAccessForUser, "SE HA DENEGADO EL ACCESO A LOS USUARIOS TIPO " . $usuario[$this->ColUserType]);
                        } else
                        {
                            $ath[$this->ColUserType] = $usuario[$this->ColUserType];
                        }
                    }

                    //var_dump($ath);
                    return $ath;
                } elseif ($this->DBtabla->num_rows !== 0)
                {
                    $this->TingerFailed(self::FailedDataBase, "SE HA OBTENIDO MAS DE UN RESULTADO DESDE LA BASE DE DATOS");
                } else
                {
                    $this->TingerFailed(self::FailedAuth, "No se encontro el usuario");
                }
            } else
            {
                $this->TingerFailed(self::FailedDataBase, "Ocurrio un error al accesar a la base de datos Error: " . $this->DBtabla->error);
                $this->ErrnoDB = $this->DBtabla->errno;
                $this->ErrorDB = $this->DBtabla->error;
            }
        }

        return [];
    }

    /**
     * VERIFICA LOS PERMISOS DEL USUARIO
     * @param string $Type
     * @return boolean
     */
    private function UserTypeAccess($Type, $rec = true)
    {
        // $Super = isset($this->AccessUser['*']) && $rec ? $this->UserTypeAccess('*', false) : true;
        $all = false;
        $controller = Mvc::App()->GetController();
        if (isset($this->AccessUser['*']))
        {
            if (isset($this->AccessUser['*']['Access']) && is_array($this->AccessUser['*']['Access']))
            {

                if (in_array('*', $this->AccessUser['*']['Access']))
                {

                    $all = true;
                }
                if (in_array($controller['method'], $this->AccessUser['*']['Access']))
                {

                    $all = true;
                }
            } else
            {
                $all = 1;
            }
            if (isset($this->AccessUser['*']['NoAccess']))
            {

                if (in_array('*', $this->AccessUser['*']['NoAccess']) && ($all === 1 || $all == false))
                {

                    $all = false;
                }
                if (in_array($controller['method'], $this->AccessUser['*']['NoAccess']) && $all === false)
                {
                    $all = false;
                }
            }
        } else
        {
            $all = true;
        }

        $users = $all;

        if (isset($this->AccessUser[$Type]))
        {

            if (isset($this->AccessUser[$Type]['Access']) && is_array($this->AccessUser[$Type]['Access']))
            {

                if (in_array('*', $this->AccessUser[$Type]['Access']))
                {
                    $users = true;
                }
                if (in_array($controller['method'], $this->AccessUser[$Type]['Access']))
                {
                    $users = true;
                }
            }
            {
                $users = 1;
            }
            if (isset($this->AccessUser[$Type]['NoAccess']))
            {
                if (in_array('*', $this->AccessUser[$Type]['NoAccess']))
                {
                    $users = false;
                }
                if (in_array($controller['method'], $this->AccessUser[$Type]['NoAccess']))
                {
                    $users = false;
                }
            }
        }
        return $users;
    }

    /**
     * EJECUTA TODA LA AUTENTICACION NO PUEDE SER REDEFINIDO
     * @return bool
     * @internal 
     */
    public final function Auth()
    {
        $r = parent::Auth();

        return $r;
    }

    /**
     * calcula y almacena en cache el costo que se usara para el hash de contraseñas 
     */
    private function MejorCosto()
    {
        if (isset($this->cache['cost']))
        {
            $this->OptionHash['cost'] = $this->cache['cost'];
        } else
        {
            $this->cache['cost'] = $this->OptionHash['cost'] = $this->PasswordHash->GenerateCost();
            Cache::Set(static::class, $this->cache);
        }
    }

}
