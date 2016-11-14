<?php

namespace Cc\Mvc;

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
     * tiene un parametro con esta clase se tomara el nombre del parametro como el nombre de la tabla que se requiere  
     * @param \Cc\Mvc\Cookie $cookie objeto para manejar cookies 
     */
    public function index(Html $h, DBtabla $test, Cookie $cookie)
    {
        $basicForm = new BasicForm();
        if ($basicForm->IsSubmited() && $basicForm->IsValid())
        {
            $this->view->mensaje = 'El formulario se recibio y es valido';
            $id = $test->AutoIncrement('id') + 1; // sqlite no acepta auto_increment  por lo que se realiza manualmente 
            $test->Insert($id, $basicForm->unInput, $basicForm->unSelect, $basicForm->unDate); // inserta los datos en la tabla test 
        }
        $this->view->basicForm = $basicForm; // envio el formulario a el view 
        if ($h instanceof HtmlPDF)// cuando el controlador es llamado por index.pdf
        {
            $h->AddCssScript(" footer div {display:none;}");
        }

        if (isset($cookie['mensaje']))// verifica si la cookie existe 
        {
            $this->view->mensaje = $cookie->mensaje;
            unset($cookie['mensaje']); // eliminado la cookie del navegador  
        }
        $this->view->test = $test->Select(); // envio los datos de la base de datos al view 
        $this->LoadView('index.tpl');
    }

    /**
     * controlador index/eliminar 
     * @param \Cc\Mvc\DBtabla $test
     * @param \Cc\Mvc\Cookie $cookie
     * @param type $id
     */
    public function eliminar(DBtabla $test, Cookie $cookie, $id)
    {
        $test->Select("id=" . $id);
        if ($test->num_rows != 1)
        {
            $this->HttpError(404);
        }
        $element = $test->fetch();
        $element->Delete();
        $cookie->mensaje = "El elemento se ha eliminado de la tabla "; // enviando una cookie al navegador 
        $this->Redirec('index'); // se redireciona al controlador index/index
    }

}
