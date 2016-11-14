<?php
/**
 * @package CcMvc
 * @subpackage view
 */
?><style type="text/css">


#error404
{
	color: #000000;
	background-color: #FFFFFF;
}

#error404 > a:link { color: #0000CC; }

p, address { margin-left: 3em; }

span { font-size: smaller; }


</style>
<div id="error404">
<h1>Objeto no localizado!</h1>
<p> El URL solicitado no ha sido localizado en este Sitio.
  Si usted tecle&oacute; el URL manualmente, por favor revise su
  ortograf&iacute;a y vu&eacute;lvalo a intentar. </p>
Si usted cree que esto es un error del servidor, por favor comuníqueselo al .<a href="mailto:<?php echo $config['WebMaster']['email'];?>">administrador del portal.</a>
<H2>Error 404</h2>
<address>
<a href="<?PHP echo $_SERVER['REQUEST_URI']?>">
<?PHP echo  $_SERVER['REQUEST_URI']?>
</a>
</address>
<?php
if(!empty($error))
{
echo $error;	
}
?></div>