<?php

namespace Cc\Mvc;

/**
 * controlador index sera ejecutado al llamar a / o a index/
 */
class Cindex extends Controllers implements ExtByController// se implementa ExtByController para que los controladores acepten estenciones de archivo
{

    public static function ExtAccept()// ExtByController
    {
        return [
            'accept' =>
            [
                'index' => 'pdf'// se indica que el metodo index aceptara la extencion pdf para que cuando se llame mediante index.pdf
            // la salida se pase automaticamente por DomPdf
            ]
        ];
    }

    /**
     * controlador index/index
     * los parametros de los controladores son resueltos e inyectados automaticamente por el framework
     * @param \Cc\Mvc\Html $h objeto de respuesta 
     * @param \Cc\Mvc\DBtabla $test objeto DBtabla asociado a la tabla test de la base de datos para esta clase en particular cuando un controlador 
     * tiene un parametro con esta clase se tomara el nombre del parametro como el nombre de la tabla que se requiere automaticamente 
     * @param \Cc\Mvc\Cookie $cookie objeto para manejar cookies 
     *
     */
    public function index(Html $h, DBtabla $test, Cookie $cookie, Request $r)
    {
        //  echo RouteByMatch::ResolveUrl('url', );

        $basicForm = new BasicForm(); /* se crea una instancia del formulario BasicForm */

        if ($basicForm->IsSubmited() && $basicForm->IsValid())/* se recivio y es valido */
        {
            $this->view->mensaje = 'El formulario se recibio y es valido';

            $test->Insert($basicForm); // inserta los datos en la tabla test 
        }
        $this->view->basicForm = $basicForm; // envio el formulario a el view 
        if ($h instanceof HtmlPDF)// cuando el controlador es llamado por index.pdf el objeto de respuesta sera instanciado de la clase HtmlPDF
        {
            $h->AddCssScript(" footer div {display:none;}");
        }

        if (isset($cookie['mensaje']))// verifica si la cookie existe 
        {
            $this->view->mensaje = $cookie->mensaje;
            unset($cookie['mensaje']); // eliminado la cookie del navegador  
        }
        $this->view->test = $test->Select(); // envio los datos de la base de datos al view 
        $this->view->Login = new LoginFormModel();


        $this->view->Load('index');
    }

    /**
     * controlador index/eliminar 
     * @param \Cc\Mvc\DBtabla $test
     * @param \Cc\Mvc\Cookie $cookie
     * @param type $id
     */
    public function eliminar(DBtabla $test, Cookie $cookie, $id)
    {
        $test->Select("id=" . $id); //comparable con hacer query("select * from test where id=$id");
        if ($test->num_rows != 1)
        {
            $this->HttpError(404);
        }
        $test->fetch()->Delete(); // se obtiene la primera fila del resultado y se elimina

        $cookie->mensaje = "El elemento se ha eliminado de la tabla "; // enviando una cookie al navegador incluso se pueden eviar arrays 
        $this->Redirec('index'); // se redireciona al controlador index/index
    }

}
