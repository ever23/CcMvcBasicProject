<?php

namespace Cc;

/**
 * interfas que deven usar todos los manejadores de bases de datos del framework
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package Cc
 * @subpackage DataBase     
 *                                                            
 */
interface iDataBase
{

    /**
     * 	ESTE METODO DEVE RETORNAR UNA INSTANCIA DE LA CLASE {@see DBtabla}
     * 	@param string $tab TABLA 
     * 	@return DBtabla
     */
    public function Tab($tab);

    /**
     * deve firltrar ataques de inyeccion sql 
     * @param string $sq
     */
    public function real_escape_string($sq);

    /**
     * @return string el ultimo error ocurrido 
     */
    public function error();

    /**
     * @return string el ultimo numero de  error ocurrido 
     */
    public function errno();

    public function dbName();
}

/**
 * interfas que deven usar los manejadores de bases creados por el usuario
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package Cc
 * @subpackage DataBase     
 *                                                            
 */
interface iDataBaseResult extends iDataBase
{

    public function GetTypeDB();

    /**
     * DEBERIA  EJECUTAR UNA SENTENCIA SQL (prepare,bindvalue,execute)
     * SI OCURRE UN ERROR ESTE DEBERIA PODERSE OBTENER MEDIANTE {@link iDataBase::error()} y {@link iDataBase::errno()}
     * @param string $sql SQL A EJECUTAR
     * @param array $BindValue los valores 
     * @return bool false si ocurrio un error 
     */
    public function ExecuteSQL($sql, $BindValue);

    /**
     * DEBERIA  EJECUTAR UNA SENTENCIA SQL  (prepare,bindvalue,execute) Y RETORNAR UN ARRAY ASOCIATIVO CON EL RESULTADO
     * SI OCURRE UN ERROR ESTE DEBERIA PODERSE OBTENER MEDIANTE {@link iDataBase::error()} y {@link iDataBase::errno()}
     * @param string $sql SQL A EJECUTAR
     * @param array $BindValue los valores 
     * @return array|false si ocurre un error debe retornar false 
     */
    public function QuerySQL($sql, $BindValue);
}
