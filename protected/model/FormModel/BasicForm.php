<?php

namespace Cc\Mvc;

/**
 * Modelo de formulario BasicForm
 *
 */
class BasicForm extends FormModel
{

    /**
     * Usa este metodo para definir los capos que tendra el formulario
     * <code>
     * <?php
     * $this->email('email_contac')->Validator('max:200|required|placeholder:Email');
     * $this->tel('telf_contac')->Validator('max:200|required|placeholder:Telefono');
     * $this->string('texto')->Validator('max:600|required|placeholder:tu texto');
     * </code>
     *
     */
    protected function Campos()
    {
        $select = [
            ['value' => 'id1', 'text' => 'texto1'],
            ['value' => 'id2', 'text' => 'texto2']
        ];
        $this->text('unInput')->Validator("required|maxlength:20");
        $this->text('unSelect')->Validator("required")->type('select')->in_options($select);
        $this->date('unDate')->Validator("required");
    }

    /**
     * Este metodo se ejecutara cuando se reciban datos del formulario
     *
     */
    protected function OnSubmit()
    {
        //tu codigo aqui
    }

}
