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
 * Description of TraitDataBase
 *
 * @author usuario
 */
trait TraitDataBase
{

    /**
     * 	ESTE METODO DEVE RETORNAR UNA INSTANCIA DE LA CLASE {@see DBtabla}
     * 	@param string $tab TABLA 
     * 	@return DBtabla
     */
    public function Tab($tab)
    {
        return new DBtabla($this, $tab);
    }

    /**
     * deve firltrar ataques de inyeccion sql 
     * @param string $sq
     */
    public function real_escape_string($sq)
    {
        return addcslashes($sq, "',\\");
    }

    /**
     * @return string el ultimo error ocurrido 
     */
    public function error();

    /**
     * @return string el ultimo numero de  error ocurrido 
     */
    public function errno();
}
