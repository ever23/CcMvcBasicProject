<?php
/**
 * @package CcMvc
 * @subpackage view
 */
?><style type="text/css">
<!--/*--><![CDATA[/*><!--*/

#error500
{
	color: #000000;
	background-color: #FFFFFF;
}

#error500 >a:link { color: #0000CC; }

p, address { margin-left: 3em; }

span { font-size: smaller; }
/*]]>*/-->
</style>
<div id="error500">
<h1>Error del Servidor!</h1>
<p> 

Se produjo un error interno en el servidor y le fue imposible completar su solicitud. 

Si usted cree que esto es un error del servidor, por favor comuníqueselo al .<a href="mailto:<?php echo $config['WebMaster']['email'];?>">administrador del portal.</a></p>
<H2>Error 500</h2>
<?php
if(!empty($error))
{
echo $error;	
}
?></div>