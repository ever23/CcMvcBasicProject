<?php

return [
    /**
     * indica si se optimizaran las imagenes
     */
    'OptimizeImages' => true,
    /**
     * INDICA SI EL CONTENIDO DEL RESULTADO SERA COMPRIMIDO CON ZLIB O NO 
     */
    'CompresZlib' => false,
    /**
     * 
     * Este índice contendrá los nombres de las clases de respuesta que se ejecutaran según el Content-Type que requiera el navegador
     * en caso de que el navegador requiera uno que no está en la lista estas clases procesaran el contenido antes de enviarlo al cliente
     */
    'Accept' =>
    [
    /**
     * Clases de respuesta segun el parametro Accept de el cliente 
     * 'application/json' =>
     * [
     *    'class' => '\\Cc\\Mvc\\Json', //CLASE
     *    'param' => [], //PARAMETROS DEL CONSTRUCTOR
     *    'layaut' => 'error', // LAYAUT POR DEFECTO
     *    'staticFile' => false // INDICA SI SE EJECUTARA EN PETICIONES QUE SE DIRIJAN A ARCHIVOS ESTATICOS
     * ]
     * 
     */
    ]
];
