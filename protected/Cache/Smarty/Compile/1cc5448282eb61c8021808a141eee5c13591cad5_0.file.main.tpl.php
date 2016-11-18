<?php
/* Smarty version 3.1.30, created on 2016-11-18 01:46:00
  from "E:\www\CcMvcProyect\protected\layauts\main.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_582e4f48ed4e01_89704590',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '1cc5448282eb61c8021808a141eee5c13591cad5' => 
    array (
      0 => 'E:\\www\\CcMvcProyect\\protected\\layauts\\main.tpl',
      1 => 1479063270,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_582e4f48ed4e01_89704590 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html>
    <head>
        <?php echo $_smarty_tpl->tpl_vars['this']->value->GetContenHead();?>

        <?php echo '<script'; ?>
  src='src/js/jquery-3.1.0.min.js' type='text/javascript'><?php echo '</script'; ?>
>
        <?php echo '<script'; ?>
  src='src/js/bootstrap.min.js' type='text/javascript'><?php echo '</script'; ?>
>
        <link  rel='stylesheet' href='src/css/bootstrap.min.css' media='screen'/>
        <link  rel='stylesheet' href='src/css/styles.css' media='screen'/>
        <!--[if lt IE 9]>
            <?php echo '<script'; ?>
 src="src/js/html5.js"><?php echo '</script'; ?>
>
        <![endif]-->
        <!--[if IE]>
             <?php echo '<script'; ?>
 src="src/js/html5shiv.js"><?php echo '</script'; ?>
>
        <![endif]-->
    </head>
    <body>
        <header class="navbar navbar-default  shadow" > <img src="src/images/CcMvc-peque.png" class="img-circle img-responsive pull-left "  /> 
            <h1 class="text-center">CcMvc Framework</h1>
        </header>
        <div class=" main_conten">
            <div class="container"> 
                <!--  contenido proveniente de el controlador y los views  -->
                <?php echo $_smarty_tpl->tpl_vars['content']->value;?>

            </div>
        </div> 
        <div class="page-header"></div>
        <footer style="padding: 20px" class="text-center"> 
            <div class="row">
                <div class="col-sm-3 text-center"> <b class="pull-left"> Powered by<a href="http://ccmvc.com.ve" title="CcMvc"><img alt="CcMvc" src="src/images/CcMvc-peque.png?GDw=30"></a></b>
                </div>
                <div class="col-sm-6 text-center"><b >Copyright <a href="http://enyerberfranco.com.ve">Enyerber Franco</a>,  2015-2016</b></div>
                <div class="col-sm-3 text-center"> </div>
            </div>
        </footer>
    </body>
</html>


<?php }
}
