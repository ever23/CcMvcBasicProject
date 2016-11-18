<?php

/**
 * Copyright © 2015-2016 por Enyerber Franco 
 *
 * El Framework CcWs es software libre: usted puede redistribuirlo y / o modificarlo
 *  bajo los términos de la General Public License, de GNU según lo publicado por
 * la Free Software Foundation, bien de la versión 3 de la Licencia, o
 * cualquier versión posterior.
 *
 * La redistribucion y uso de el codigo fuente, con o sin modificacion,
 * se permiten con el cimplimiento de las siguientes
 * condiciones:
 * * Las redistribuciones del codigo fuente deben tener el aviso de copyright anterior,
 * en la lista de condiciones y los siguientes avisos legales en la documentacion y/o
 * en otros materiales provistos en la distribucion
 * * Ni el nombre CcWs framework  ni el nombre de sus colaboradores pueden usarse para promocionar 
 * productos derivados de este software sin  previos permiso especificos por escrito.
 *
 * ESTE SOFTWARE ES PROPORCIONADO POR EL PROPIETARIO DEL COPYRIGHT Y COLABORADORES
 * "TAL COMO ESTÁ" SIN GARANTIA EXPRESA O IMPLÍCITAS DE CUALQUIER TIPO, INCLUYENDO, PERO NO LIMITADO A,
 * LAS GARANTÍAS IMPLÍCITAS DE COMERCIABILIDAD E IDONEIDAD PARA UN PROPÓSITO.
 * EN NINGÚN CASO LOS DERECHOS DE AUTOR DEL PROPIETARIO O COLABORADORES SERÁN RESPONSABLES DE DAÑOS DIRECTOS,
 * INDIRECTOS, INCIDENTALES, ESPECIAL, EJEMPLARES O CONSECUENTES( INCLUYENDO,
 * PERO NO LIMITADO A,ADQUISISION Y SUSTITUCIÓN DE BIENES Y SERVICIOS;
 * PÉRDIDA DE USO, DE DATOS, O BENEFICIOS O INTERRUPCIÓN DEL NEGOCIO) CAUSADOS COMO FUERE EN CUALQUIER TEORÍA DE RESPONSABILIDAD
 * CONTRACTUAL, RESPONSABILIDAD ESTRICTA O RESPONSABILIDAD CIVIL( INCLUYENDO NEGLIGENCIA O CUALQUIER OTRA FORMA)
 * QUE SURJAN DE NINGUNA MANERA DEL USO DE ESTE SOFTWARE, AUNQUE INFORMADOS DE LA POSIBILIDAD DE TALES DAÑOS.
 * 
 * 
 * certificado de origen  del desarrollador  1.0
 * haciendo una contribución a este proyecto, Certifico que:
 *
 * (a) La contribución se ha creado en su totalidad o en parte por mí (Enyerber Franco) y tengo el derecho a
 * presentar los términos de la licencia de código abierto que se indica en el archivo, o
 * 
 * (b) La contribución se basa en previos trabajos que e desarrolado, con lo mejor de mis conocimientos, es cubierto
 * por una adecuada licencia de código abierto y tengo el derecho que la licencia sostienen para  trabajar
 * con modificaciones, ya sea creado en su totalidad o en parte por mí, en las mismas  licencia de código abierto
 * (a menos que me permite presentar en una licencia diferente), como se indica en el archivo,
 *
 * (c) Entiendo y de acuerdo que este proyecto y la contribución son públicas y que un registro de
 * la contribución( incluidos todos los datos personales que presento con él, incluyendo mi signo-off)
 * se mantiene indefinidamente y puede ser redistribuido en consonancia con este proyecto o la  licencia(s) de
 * código abierto en cuestión
 *
 * Debería haber recibido una copia de la General Public License de GNU
 * junto con este framework. Si no es así, consulte <http://www.gnu.org/licenses/>
 */
if (!version_compare(PHP_VERSION, '5.6', '>='))
{
    trigger_error("CcWs no es compatible con php " . PHP_VERSION . " porfavor use php 5.6 o mayor", E_USER_ERROR);
    exit;
}
include_once 'Ws/Ws.php';

/**
 * CcWS : Cover Code Web Socket                                                         
 * Servidor WebSocket                     
 * <code>
 * <?php
 * $dir=  dirname(__FILE__);
 * include $dir.'/../CcMvc/CcWS.php';
 * CcWS::Start($dir.'/config/configuracion.php')->Run();
 * ?>
 * </code>                                                                             
 * @version 0.5.4.1                                                        
 * @fecha:  2016-04-28                                                         
 * @autor ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                       
 * @copyright © 2016, Enyerber Franco 
 * @license http://opensource.org/licenses/gpl-license.php GNU  GENERAL PUBLIC LICENSE 
 * @package CcWs
 * 
 * @example ../examples/WebSocket/WebSocket/config/configuracion.php EJEMPLO DEL ARCHIVO DE CONFIGURACION DEL SERVIDOR
 * @link http://ccmvc.890m.com/websocket SITIO WEB
 */
class CcWS extends Cc\Ws
{

    /**
     * CREA UNA ISNTANCIA DEL SERVIDOR
     * @param string $conf EL NOMBRE DEL ARCHIVO DE CONFIGURACION CON EL QUE SE INICIARA LA APLICACION  
     * @return this
     */
    public static function Start($conf = NULL)
    {
        return new static($conf);
    }

}
