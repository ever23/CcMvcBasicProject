<?php

/**
 * si tiene dudas sobre el codigo visite {@link  http://ccmvc.com} o contacte con <enyerverfranco@gmail.com>
 * el sitio del framework CcMvc Aun se encuentra en construccion
 * 
 */
include ("../CcMvc/CcMvc.php");
//include("vendor/autoload.php");
$config = dirname(__FILE__) . "/protected/configuracion.php";
$app = CcMvc::Start($config, "Proyecto Base");
/**
 * indico que el controlador index/eliminar podra ser llamado mediante eliminar esto es solo para demostrar 
 * el enrutamiento manual CcMvc normalmente enruta automaticamente los controladores pero se puede 
 * se puede tomar el mando completamente si es nesesario similar a laravel 
 */
$app->Router->Route('/{method}', 'index/{method}');

$app->Run(); // inica el funcionamiento del framework

