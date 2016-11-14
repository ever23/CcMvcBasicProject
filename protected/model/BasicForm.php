<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cc\Mvc;

/**
 * Description of BasicForm
 *
 * 
 */
class BasicForm extends FormModel
{

    public function campos()
    {
        $select = [
            ['value' => 'id1', 'text' => 'texto1'],
            ['value' => 'id2', 'text' => 'texto2']
        ];
        return [
            'unInput' => ['text', '', ['required' => true, 'maxlength' => 20]],
            'unSelect' => ['select', '', ['required' => true, 'options' => $select]],
            'unDate' => ['date', '', ['required' => false]],
        ];
    }

}
