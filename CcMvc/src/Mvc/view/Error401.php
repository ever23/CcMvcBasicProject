<?php
/**
 * @package CcMvc
 * @subpackage view
 */
?><style type="text/css">


    #error401
    {
        color: #000000;
        background-color: #FFFFFF;
    }

    #error401 >a:link { color: #0000CC; }

    p, address { margin-left: 3em; }

    span { font-size: smaller; }

</style>
<div id="error401">
    <h1>¡Autentificación requerida!</h1>
    <p> 

        El servidor no puede certificar que usted esté autorizado para acceder a la URL " <?PHP echo $_SERVER['REQUEST_URI'] ?>". Ha podido suministrar información incorrecta (ej. contraseña no válida) o el navegador no sabe cómo suministrar la información requerida.
        <br>
        En caso de que usted tenga permiso para acceder al documento, por favor verifique su nombre de usuario y contraseña y vuélvalo a intentar.

        Si usted cree que esto es un error del servidor, por favor comuníqueselo al .<a href="mailto:<?php echo $config['WebMaster']['email']; ?>">administrador del portal.</a>

    <H2>Error 401</h2>
    <address>
        <a href="<?PHP echo $_SERVER['PHP_SELF'] ?>">
            <?PHP echo $_SERVER['PHP_SELF'] ?>
        </a>
    </address>
    <?php
    if(!empty($error))
    {
        echo $error;
    }
    ?>
</div>