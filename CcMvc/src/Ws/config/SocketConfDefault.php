<?php
/**
 * archivo de configuracion por defecto de CcWs
 * En el nuevo archivo de configuración no sera necesario redefinir
 * todos los índices solo los que se necesiten cambiar ya que el nuevo
 * documento de configuración solo reemplazar los índices del documentos por defecto que Haya cambiado 
 * @package CcWs
 * @subpackage configuracion
 * @see Config
 */
use Cc\CcException;
return
        [
            /**
             * este indice contiene los directorios del servidor
             * solo dentro de este índice se podrá usar el alias {App} el 
             * cual será reemplazado por el contenido del índice [App][app] 
             */
            'App' =>
            [
                'app' => realpath(dirname(__FILE__) . '/../../') . '/',
                'extern' => "{App}extern/",
                'procedimientos' => "{App}procedimientos/",
                'model' => "{App}model/",
                'ServerRoot'=>"{App}Wsdocs/"
            ],
            /**
             * HOST EN EL CUAL ESCUCHARA EL SERVIDOR
             */
            'host' => '127.0.0.1',
            /**
             * PUERTO 
             */
            'port' => '1500',
            /**
             * NUMERO MAXIMO DE CLIENTES QUE SE PODRAN CONECTAR A LA VES
             */
            'MaxClients' => 100,
            /**
             * este índice es un array asociativo que contendrá las clases que
             * podrán ser inyectadas en los métodos de eventos por parámetros   
             */
            'Dependencias' =>
            [
                '\\Cc\\Ws\\Messaje' => ['{messaje}', '{messageLength}', '{binary}'],
                '\\Cc\\Ws\\MessajeJson' => ['{messaje}', '{messageLength}', '{binary}'],
                '\\Cc\\DBtabla' => [ '{DB}', '{name_param}'],
                
            ],
           
           
            /**
             * configuracion de la base de datos 
             */
            'DB' => []
            /*
              'DB' =>
              [
              'class' => 'DB_MySQLi',
              'param' => [ HOST , USER, PASS , DB   ]
              ]
             */,
            'debung' =>
            [
                'error_reporting' => E_ALL,
                'ModoExeption' => CcException::DEBUNG_DATABASE,
                'file' => 'php://stderr',
                
            ],
];

