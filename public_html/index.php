<?php

$vendor = "../vendor/autoload.php";

include ($vendor);
if (!class_exists("\\CcMvc"))
{
    trigger_error("Porfavor instala CcMvc via composer ejecutando 'composer install --prefer-dist'", E_USER_ERROR);
}
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

