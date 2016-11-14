<?php
/* Smarty version 3.1.30, created on 2016-11-13 19:21:47
  from "E:\www\CcMvcProyect\protected\view\index.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_5828af3bbdd803_90694542',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '3fb9e18ae868b9ba1dd75c6683975e6de23ee8f9' => 
    array (
      0 => 'E:\\www\\CcMvcProyect\\protected\\view\\index.tpl',
      1 => 1479061169,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5828af3bbdd803_90694542 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div class="container"><h1 class="text-center">Proyecto Base CcMvc
        <?php if (!empty($_smarty_tpl->tpl_vars['mensaje']->value)) {?>
            <br><small><div class="alert alert-success"><?php echo $_smarty_tpl->tpl_vars['mensaje']->value;?>
</div></small>
            <?php }?>
    </h1>
    <p>
        Este es un ejempo basico de algunas de las caracteristicas de CcMvc 
    </p>

    <p>
        <b>Minifi automatico de javascript, styles y html:</b> 
        En modo de produccion el framework minifica los scripts javascript, styles y html donde se encuentren sea en el documento html 
        o los archivos js y css este proyecto esta por defecto en modo de depuracion pudes activar el modo de produccion en el archivo de configuracion 
        en protected/configuracion.php agregar el indice al array 'debung'=>false y recargar el proyecto en el navegador desactivando el cache del mismo 
        y se podra notar la diferencia esto es asi para que en modo de depuracion puedas depurar javascripts y css de forma normal .<br>
        Los archivo que tengan en su extencion .min no seran afectados por esta caracteristica puesto que se supone que ya estan comprimidos 
        El minifi no afecta el tiempo de respuesta de servidor ya que se almacena en cache el archivo minifiado para la proxima peticion  

    </p>
    <p><b>DomPdf:</b> para ver el trabajo que realiza DomPdf ingrese <a href="index.pdf">index.pdf</a><br>
        EL formulario no se vera bien pero normalmente normalmente no se utilizan formularios en pdf,
        Por lo general DomPdf funciona muy bien con bootstrap 
    </p>
    <p>
        <b>Redimencionamiento de imagenes desde el servidor:</b>
        Para redimencionar imagenes desde el servidor solo se nesesita pasar via get las dimenciones de la imagen  sin escribir ningun cogido php en el servidor
        esto puede ayudar al optimizar las imagenes cuando se realiza SEO puesto que no se tiene que crear una imagen nueva con menor tamaño si no que 
        solo pasar las dimenciones via get, funciona tanto con imagenes estaticas como con dinamicas creadas en los controladores.
        <br> 
        Esto no afecta en gran medida el tiempo de respuesta del servidor ya que se almacena cache de las imagenes redimencionadas, 
        ademas se envian los heades correspondientes como lo hace normalmente apache  para 
        que el navegador del cliente almacene su cache.
        <br>
        Actualmente esta caracteristica solo funciona en imagenes gif,jpg y png 
    <ul class="list-unstyled">
        <li><b>GDw</b> indica el ancho de la imagen si solo se pasa este parametro el alto se calculara </li>
        <li><b>GDh</b> indica el alto de la imagen si solo se pasa este parametro el ancho se calculara </li>
        <li><b>GDc</b> en imagenes jpeg indica la calidad de la imagen que va desde 0 a 100 y en imagenes png indica la compresion de la imagen que va desde 0 a 9 </li>

    </ul>
    Ejemplos:<br>
    <b>src/images/CcMvc-peque.png?GDw=50</b><br>
    <img src="src/images/CcMvc-peque.png?GDw=50"><br>
    <b>src/images/CcMvc-peque.png?GDh=70</b><br>
    <img src="src/images/CcMvc-peque.png?GDh=70"><br>
    <b>src/images/CcMvc-peque.png?GDw=50&GDh=70</b><br>
    <img src="src/images/CcMvc-peque.png?GDw=50&GDh=70">
    </p>
    <div class="row">
        <div class="col-xs-6">
            <h3>Un Formulario basico 

            </h3>
            <div class="form-control" style="height: auto;">
                <?php $_block_plugin1 = isset($_smarty_tpl->smarty->registered_objects['basicForm'][0]) ? $_smarty_tpl->smarty->registered_objects['basicForm'][0] : null;
if (!is_callable(array($_block_plugin1, 'Form'))) {
throw new SmartyException('block tag \'basicForm\' not callable or registered');
}
$_smarty_tpl->smarty->_cache['_tag_stack'][] = array('basicForm', array());
$_block_repeat1=true;
echo $_block_plugin1->Form(array(), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>

                <ul class="list-unstyled">
                    <li>
                        <label>Input </label>
                        <?php echo $_smarty_tpl->smarty->registered_objects['basicForm'][0]->Input(array('name'=>"unInput"),$_smarty_tpl);?>

                    </li>
                    <li>
                        <label>Select </label>
                        <?php echo $_smarty_tpl->smarty->registered_objects['basicForm'][0]->Select(array('name'=>"unSelect"),$_smarty_tpl);?>

                    </li>
                    <li>
                        <label>Date </label>
                        <?php echo $_smarty_tpl->smarty->registered_objects['basicForm'][0]->Input(array('name'=>"unDate"),$_smarty_tpl);?>

                    </li>
                    <li >

                        <?php echo $_smarty_tpl->smarty->registered_objects['basicForm'][0]->ButtonSubmit(array('value'=>"Guardar <spam class='glyphicon  glyphicon-cloud-upload'></spam>",'class'=>"btn btn-success"),$_smarty_tpl);?>

                    </li>
                </ul>
                <?php $_block_repeat1=false;
echo $_block_plugin1->Form(array(), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>

            </div>
        </div>
        <div class="col-xs-6">
            <h3>Ejempo de base de datos</h3>
            <table class="table table-responsive">
                <thead>
                    <tr>               
                        <th>campo[campo1]</th>
                        <th>campo[campo2]</th>
                        <th>campo[campo3]</th>
                        <td></td>

                    </tr>  
                </thead>
                <tbody>
                    <?php $_block_plugin1 = isset($_smarty_tpl->smarty->registered_objects['test'][0]) ? $_smarty_tpl->smarty->registered_objects['test'][0] : null;
if (!is_callable(array($_block_plugin1, 'each'))) {
throw new SmartyException('block tag \'test\' not callable or registered');
}
$_smarty_tpl->smarty->_cache['_tag_stack'][] = array('test', array('row'=>'row'));
$_block_repeat1=true;
echo $_block_plugin1->each(array('row'=>'row'), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>

                    <tr>

                        <td><?php echo $_smarty_tpl->tpl_vars['row']->value['campo1'];?>
</td>
                        <td><?php echo $_smarty_tpl->tpl_vars['row']->value['campo2'];?>
</td>
                        <td><?php echo $_smarty_tpl->tpl_vars['row']->value['campo3'];?>
</td>
                        <td><a href="eliminar?id=<?php echo $_smarty_tpl->tpl_vars['row']->value['id'];?>
"><span class="glyphicon glyphicon-remove"></span></a></td>
                    </tr>
                    <?php $_block_repeat1=false;
echo $_block_plugin1->each(array('row'=>'row'), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>

                </tbody>
            </table>

        </div>
    </div>
</div><?php }
}
