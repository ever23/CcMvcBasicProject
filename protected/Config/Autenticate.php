<?php

return [
    'class' => NULL, //'class'=>' tu clase se autenticacion',
    'param' =>
    [
        ['*/*/*']
    ],
    'SessionName' => 'CcMvc_SESS', // nombre de la cookie de session 
    /**
     * PARAMETRO DE LAS COOKIES DE SESSION
     */
    'SessionCookie' =>
    [
        'path' => NULL,
        'cahe' => 'nocache,private',
        'time' => 21600,
        'dominio' => NULL,
        'httponly' => false,
        'ReadAndClose' => false
    ]
];
