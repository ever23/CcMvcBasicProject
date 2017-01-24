<?php

return [
    /**
     * indica si se enrutaran los controladores automaticamente
     */
    'AutomaticRoute' => true,
    /**
     * Enrutamientos manuales
     */
    'Routing' => [
    /**
     *  [
     *      'uri' => '/{method}',
     *      'controller' => 'index/{method}',
     *      'where' => []
     *  ],
     *  [
     *      'uri' => '/catalogo/{id_producto}',
     *      'controller' => 'catalogo/producto',
     *      'where' => ['id_prod'=>'^(\d{1,4})$']
     *  ],
     */
    ],
    /**
     * PROTOCOLO EN EL QUE TRABAJARA LA APLICACION
     */
    'protocol' => 'http',
    /**
     * ARCHIVOS QUE ABRIRA POR DEFECTO AL ENRUTAR ARCHIVOS ESTATICOS
     */
    'DefaultOpenFile' =>
    [
        'index.php', 'index.html', 'index.htm'
    ],
    /**
     * TIEMPO DE EXPIRACION DEL CACHE DE ARCHIVOS ESTATICOS 
     */
    'CacheExpiresTime' => NULL
];
