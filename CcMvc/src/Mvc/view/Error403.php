<?php
/**
 * @package CcMvc
 * @subpackage view
 */
?><style type="text/css">


#error403
{
	color: #000000;
	background-color: #FFFFFF;
}

#error403 >a:link { color: #0000CC; }

p, address { margin-left: 3em; }

span { font-size: smaller; }

</style>
<div id="error403">
<h1>Acceso prohibido!</h1>
<p> 

Usted no tiene permiso de accesar al objeto solicitado. El objeto está protegido contra lectura, o no puede ser leido por el servidor.

Si usted cree que esto es un error del servidor, por favor comuníqueselo al .<a href="mailto:<?php echo $config['WebMaster']['email'];?>">administrador del portal.</a>

<H2>Error 403</h2>
<address>
<a href="<?PHP echo $_SERVER['PHP_SELF']?>">
<?PHP echo  $_SERVER['PHP_SELF']?>
</a>
</address>
<?php
if(!empty($error))
{
echo $error;	
}?>
</div>