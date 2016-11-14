<?php

return [
    'debung' => false, // indica si se encuentra en modo debung o produccion 
    'App' =>
    [
        'app' => realpath(dirname(__FILE__)) . '/', // directorio de protected 
    ],
    'DB' => include ('sqlite.php'), // configuracion de base de datos 
    'Response' => [
        'Accept' => [
            'text/html, application/xhtml+xml,  application/xaml+xml, application/pdf' =>
            [
                'layaut' => 'main.tpl', // por defecto el layaut es main.php en este proyecto se usara smarty por lo que se indica que se main.tpl
            ],
        ]
    ],
];


