<?php
$vendor = "../vendor/autoload.php";

include ($vendor);

if (!class_exists("\\CcMvc"))
{
    trigger_error("Porfavor instala CcMvc via composer ejecutando 'composer install --prefer-dist'", E_USER_ERROR);
}

$app = CcMvc::Start("../protected/", "Mi Aplicacion Web");

$app->Run();