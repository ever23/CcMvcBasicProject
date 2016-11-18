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

/**
 * Description of AccessUserController
 *
 * @author usuario
 */
interface AccessUserController extends iProtected
{

    /**
     * DEBE RETORNAR UN ARRAY EN EL CUAL SE ESPECIFICARAN LOS METODOS A LOS
     * QUE LOS DIFERENTES TIPOS DE USUARIO TIENE ACCESO
     * EN CASO  DE QUE EL USUARIO EJECUTE EL UN METODO AL QUE NO POSEE ACCESSO
     * SE MOSTRARA EL MENSAJE 403 O SE EJECURAR EL EVENTO OnFailed DE AuteticateUserDB
     * <code>
     * public static function AccessUser();
     * {
     *      return array(
     *      'NoAth'=>array(lista de metodos que no seran afectador por la autentificacion),
     *      'tipo de usuario'=>array( 'Access'=>array(metodos accesibles),'NoAccess'=>array(metodos inaccsibles));
     *       .  
     *       .
     *       .
     *      );
     *  
     * }
     * </code>
     * <pre>
     * 'NoAuth' en este indice sera un array con los metodos que no seran afectador por la autenticacion es decir
     * cualquier usuario o visitante tendra acceso 
     * El resto de los indices podran ser los direrentes tipos de usuarios que se puedan encontrar en la clumna 
     * idicada en Cc\Mvc\AuteticateUserDB::InfoUserDB() en el indice Cc\Mvc\AuteticateUserDB::CollUserType del array que 
     * se retorna, 
     * se puede usar un * para indica que afectara a todos lo usuarios
     * en en la lista de metodos para indica que afectara a todos los metodos 
     * @return array 
     */
    public static function AccessUser();
}
