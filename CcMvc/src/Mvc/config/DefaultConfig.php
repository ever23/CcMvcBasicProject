<?php

/**
 * archivo de configuracion por defecto de CcMvc
 * En el nuevo archivo de configuración no será necesario redefinir todos los índices solo los
 * que se necesiten cambiar ya que el nuevo documento de configuración solo reemplazar los índices
 * del documentos por defecto que haya cambiado 
 * @package CcMvc
 * @subpackage Configuracion
 * @see Config
 */

namespace Cc\Mvc;

use Cc\Mvc;

return
        [
            /**
             * este índice debe contener un array asociativo con los directorios donde se alojaran
             * los controladores, layauts, model entre otros, solo dentro de este índice se podrá usar el 
             * alias {App} el cual será reemplazado por el contenido del índice [App][app] 
             */
            'App' =>
            [
                'app' => realpath(dirname(Mvc::App()->GetFileCoreClass()) . '/../protected') . DIRECTORY_SEPARATOR,
                'controllers' => "{App}controllers" . DIRECTORY_SEPARATOR,
                'extern' => "{App}extern" . DIRECTORY_SEPARATOR,
                'procedimientos' => "{App}procedimientos" . DIRECTORY_SEPARATOR,
                'model' => "{App}model" . DIRECTORY_SEPARATOR,
                'view' => "{App}view" . DIRECTORY_SEPARATOR,
                'layauts' => "{App}layauts" . DIRECTORY_SEPARATOR,
                'Cache' => "{App}Cache" . DIRECTORY_SEPARATOR,
            ],
            /**
             * CACHE INTERNO DE CcMvc
             */
            'Cache' =>
            [
                'debung' => false,
                'class' => '\\Cc\\CacheFilePHP',
                'File' => 'CcMvcCache' . \CcMvc::Version,
                'ExpireTime' => '+1 month'
            ],
            /**
             * en este índice se configurara los controladores 
             */
            'Controllers' =>
            [
                /**
                 * esten indice indica el prefijo de las clases controladoras 
                 */
                'Prefijo' => 'C',
                /**
                 * este es el controlador por defecto
                 */
                'DefaultControllers' => 'index',
                /**
                 * este índice es un array asociativo que contendrá las clases que podrán ser inyectadas en los métodos de controladores por parámetros  
                 * También se pueden pasar por parámetros referencias de objetos que el sistema ya a creado como el de bases de datos el manejador de contenido y el de autenticación esto se realiza utilizando los seudónimos
                 * 
                 * {DB} si un parámetro contiene este seudónimo será reemplazado por el objeto creado para manejar bases de datos
                 * {Response} este parámetro será reemplazado con el objeto de respuesta
                 * {Autenticate} este parámetro será reemplazado por el objeto que se utilizo para autenticar
                 * {param_name} este parámetro será reemplazado por el nombre que se le aya asignado al parámetro en la definición del controlador
                 * {config} este parametro sera reemplazado por el array de configuracion
                 * {Router} será reemplazado por una referencia al objeto Router que se utilizo para enrutar la petición
                 * {Request} se reemplazara por un objeto de Request
                 * {SelectorControllers} se reemplazara por una referencia al objeto SelectorControllers que se usó para ejecutar el controlador
                 * Por defecto en las dependencias estarán definidas varias clases un es DBtabla que creara un objeto DBtabla tomando como primer parámetro  {DB} y  el segundo  {param_name} esto le permitirá asociar el objeto con un tabla de la base de datos con el mismo nombre del parámetro
                 * La segunda clase es Cookie que tomara como parámetro la configuración {config} esto permitirá que use la configuración para obtener el dominio y el path para setcookie
                 * La tercera es PostFiles la cual tomara como parámetro {name_param} y lo utilizara para elegir el array le correspondiente al nombre del parámetro esta clase maneja los archivos enviados mediante post
                 */
                'Dependencias' =>
                [
                    '\\Cc\\Mvc\\DBtabla' => ['{DB}', '{name_param}'],
                    '\\Cc\\Mvc\\DBtablaModel' => ['{DB}', '{name_param}'],
                    '\\Cc\\Mvc\\Cookie' => ['{config}'],
                    '\\Cc\\Mvc\\PostFiles' => ['{name_param}'],
                    '\\Cc\\Mvc\\Server' => [],
                    '\\Cc\\Mvc\\MapingControllers' => ['{config}']
                ],
            ],
            /**
             * este índice es un array que contendrá las clases manejadoras de contenido las cuales se 
             * ejecutaran según los requiera el navegador en Content-Type por defecto está definido de la siguiente manera 
             */
            'Response' =>
            [
                /**
                 * indica si se optimizaran las imagenes
                 */
                'OptimizeImages' => true,
                /**
                 * INDICA SI EL CONTENIDO DEL RESULTADO SERA COMPRIMIDO CON ZLIB O NO 
                 */
                'CompresZlib' => true,
                /**
                 * EXTENCIONES DE ARCHIVOS CON SUS RESPECTIVOS MIMES
                 */
                'ExtencionContenType' => include ('ExtencionContenType.php'),
                /**
                 * 
                 * Este índice contendrá los nombres de las clases de respuesta que se ejecutaran según el Content-Type que requiera el navegador
                 * en caso de que el navegador requiera uno que no está en la lista estas clases procesaran el contenido antes de enviarlo al cliente
                 */
                'Accept' =>
                [
                    'application/json' =>
                    [
                        'class' => '\\Cc\\Mvc\\Json', //CLASE 
                        'param' => [], //PARAMETROS DEL CONSTRUCTOR
                        'layaut' => 'error', // LAYAUT POR DEFECTO
                        'staticFile' => false // INDICA SI SE EJECUTARA EN PETICIONES QUE SE DIRIJAN A ARCHIVOS ESTATICOS 
                    ],
                    'application/javascript, text/javascript, text/css' =>
                    [
                        'class' => '\\Cc\\Mvc\\ResponseJsCss',
                        'param' => [true, true],
                        'staticFile' => true
                    ],
                    'text/html, application/xhtml+xml, application/xaml+xml, application/xaml+xml' =>
                    [
                        'class' => '\\Cc\\Mvc\\Html',
                        'param' => [true, true],
                        'layaut' => 'main',
                        'staticFile' => false
                    ],
                    'application/pdf' =>
                    [
                        'class' => '\\Cc\\Mvc\\HtmlPDF',
                        'param' => [],
                        'layaut' => 'main', 'staticFile' => false
                    ],
                    'image/gif, image/jpeg, image/png' =>
                    [
                        'class' => '\\Cc\\Mvc\\GDResponse',
                        'param' => [],
                        'staticFile' => true
                    ],
                    '*/*, text/plain' =>
                    [
                        'class' => '\\Cc\\Mvc\\Response',
                        'param' => [true, false],
                        'staticFile' => true
                    ]
                ]
            ],
            /**
             * este índice indicara las configuraciones que la aplicación usara para enrutar las peticiones del navegador
             */
            'Router' =>
            [
                'AutomaticRoute' => true,
                /**
                 * PROTOCOLO EN EL QUE TRABAJARA LA APLICACION
                 */
                'protocol' => 'http',
                /**
                 * SI ES {@link Router::Get} SERA ENRUTADO TOMANDO ENCUENTA EL VALOR DE LA VARIABLE GET ESTABLECIDA EN EL INDICE 'GetControllers' 
                 * SI ES {@link Router::Path} EL ENRUTAMIENTO SERA MEDIANTE EL PATH O RUTA  REQUERIDA POR EL NAVEGADOR
                 */
                'GetControllerFormat' => Router::Path,
                /**
                 * NOMBRE DE LA VARIABLE GET QUE SE USARA PARA EL ENRUTAMIENTO 
                 */
                'GetControllers' => 'page',
                /**
                 * OPERADOR DE ALCANCE SEPARARA LOS PAQUETES, CONTROLADORES Y METODOS EN LA VARIABLE GET QUE SE USARA PARA EL ENRUTAMIENTO 
                 */
                'OperadorAlcance' => '::',
                /**
                 * INDICA EL MODO EN QUE SE TRATARAN LAS EXTENCIONES DE LOS CONTROLADORES PARA FORZAR EL USO DE UNA
                 * DETERMINADA CLASE DE RESPUESTA 
                 * EJEMPLO CUANDO ESTA ACTIVA ESTA FUNCION 
                 * SE PUEDE FORZAR QUE CON CONTROLADO RESPONDA CON CONTENIDO DE TIPO JSON LLAMADOLO DE LA SIGUIENTE MANERA 
                 * http://host/controlador/metodo.json
                 * O FORZAR LA RESPUESTA EN HTML
                 * http://host/controlador/metodo.html
                 * HAY TRES POSIBLES VALORES 
                 * 
                 * 
                 *  Router::UseExtContr PARA USAR EXTENCIONES (NO OBLIGA EL USO )
                 *  Router::NoExtContr PARA QUE NO SE PERMITA EXTENCIONES EN CONTROLADORES 
                 *  Router::RequireExtContr PARA QUE EL USO DE EXTENCIONES EN CONTROLADORES SEA OBLIGATORIO 
                 */
                'ExtencionController' => Router::NoExtContr,
                /**
                 * DIRECCION EN EL SERVIDOR DE LA APLICACION 
                 */
                'DocumentRoot' => NULL,
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
            ],
            /**
             * este índice establece la clase autenticadora que debe ser extendida de Autentícate y los parámetros 
             * del constructor también contiene el nombre que se usara para las sesiones y los parámetros de las
             *  cookies de sesión  por defecto este índice es un array vacío
             */
            'Autenticate' =>
            [
                'class' => '\Cc\Mvc\SESSION',
                'param' =>
                [
                    ['*/*/*']
// [ LISTA DE CONTROLADORES QUE NO SERAN AFECTADOS POR AUTENTICACION],
// [ LISTA DE VARIABLES QUE SE USARAN PARA AUTENTICAR ]
                ],
                'SessionName' => 'CcMvc_SESS',
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
            ],
            'Events' => [
                'class' => '\\Cc\\Mvc\\MvcEvents'
            ],
            'debung' =>
            [
                'error_reporting' => E_ALL,
                'ModoExeption' => \Cc\CcException::DEBUNG_DATABASE,
                'file' => 'php://stderr',
                'NoReenviarFiles' => true,
                'UseErrorResponseCode' => false
            ],
            'DB' => [
                'UseStmt' => true
            ]
            /*
              'DB' =>
              [
              'class' => 'DB_MySQLi',
              'param' => [ HOST , USER, PASS , DB   ]
              ]
             */,
            'VarAceptXss' =>
            [
            /*
              '_GET' => [],
              '_POST' => [],
              '_COOKIE' => []
             */
            ],
            'VarAceptSqlI' => [
            /* '_GET' => [],
              '_POST' => [],
              '_COOKIE' => []
             */
            ],
            'AutoloadLibs' =>
            [
                'UseStandarAutoloader' => false,
                'AutoloadersFiles' => [
//'namelib'=>'pathlib'
                ],
                'NamespacesForDir' => [
//'namespace'=>'path' o 'namespace'=>['path1','path2']
                ],
            ],
            /**
             * Configuraciones para los views y layauts en general 
             * estableciendo la clase que se encargara de evalualos segun la extencion de archivo
             */
            'ViewLoaders' => [
                'Default' =>
                [
                    'class' => '\\Cc\\Mvc\\ViewPHP',
                    'param' => [],
                    'ext' => 'php'
                ],
                "Loaders" =>
                [
                    'tpl' => [
                        'class' => '\\Cc\\Mvc\\ViewSmartyTpl',
                        'param' => [],
                    ],
                    'php' => [
                        'class' => '\\Cc\\Mvc\\ViewPHP',
                        'param' => [],
                    ]
                ]
            ],
            /**
             * Configuraciones specificas de la libreria smarty 
             * solo son usada cuando se esta usando la libreria 
             */
            'SmartyConfig' => [
                'LeftDelimiter' => '{',
                'RightDelimiter' => '}',
                'PluginsDir' => 'SmartyPlugins/',
                'ConfigDir' => 'SmartyConfig/',
                'DebungConsole' => false,
                'Plugins' => [
                ],
            ],
            'WebMaster' =>
            [
                'name' => 'webmaster',
                'email' => 'webmaster@localhost.com'
            ],
            /**
             * Configuracion para seo
             */
            'SEO' =>
            [
                'MetaTang' =>
                [
                    'language' => 'es',
                    'author' => ' ',
                    'designer' => ' ',
                    'programmer' => ' ',
                    'robots' => "index,follow,all",
                    'viewport' => "width=device-width, initial-scale=1.0, maximum-scale=1.0"
                ],
                'keywords' => [],
                'HttpEquiv' => ['content-language' => 'es'],
                'CDNs' => []
            ]
];


