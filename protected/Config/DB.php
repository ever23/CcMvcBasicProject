<?php

return [
    'class' => '\\Cc\\Mvc\\SQLite3',
    'param' => [dirname(__FILE__) . '/../testdb.db']
];

/**
 * return [
 *   'class' => '\\Cc\\Mvc\\MySQLi',
 *   'param' => ['localhost', 'root', '', 'testDB']
 * ];
 */

/**
 * return [
 *   'class' => '\\Cc\\Mvc\\PDO',
 *   'param' => ['mysql:', 'root', '', 'testDB']
 * ];
 */