<?php

/**
 * si tiene dudas sobre el codigo visite {@link  http://ccmvc.com} o contacte con <enyerverfranco@gmail.com>
 * el sitio del framework CcMvc Aun se encuentra en construccion
 * 
 */
include ("../vendor/autoload.php");
//include("vendor/autoload.php");

$app = CcMvc::Start("../protected/", "Proyecto Base");
/**
 * indico que el controlador index/eliminar podra ser llamado mediante eliminar esto es solo para demostrar 
 * el enrutamiento manual CcMvc normalmente enruta automaticamente los controladores pero se puede 
 * se puede tomar el mando completamente si es nesesario similar a laravel 
 */
$app->Router->Route('/{method}', 'index/{method}')->has('index');
$app->Router->Route('/{method}/hola/{cosa}/{pelo}-{pelo2}.{ext}', 'index/{method}')->has('url');
$app->Run(); // inica el funcionamiento del framework

