<?php

namespace Cc;

/**
 * @deprecated 
 * @package Cc
 * @subpackage otros
 */
class DateTimeEx extends \DateTime
{

    public $meses = array(1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'sectiembre', 'octubre', 'nobiembre', 'diciembre');
    public $semanas = array('domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado');

    public function DateEdad($ano = NULL, $mes = 0, $dia = 0)
    {
        $b = $this->diff(new \DateTime($ano . "-" . $mes . "-" . $dia));
        return $b->format("%y");
    }

    public function actual_time()
    {

        $localtime = localtime();
        $time = localtime(time(), true);
        $this->setDate(date('Y'), date('m'), $time['tm_mday']);
        $this->setTime($time['tm_hour'], $time['tm_min'], $time['tm_sec']);
    }

    function mes_cadena($mes = NULL)
    {
        return $this->meses[((int) (!is_null($mes) ? $mes : $this->format('m')))];
    }

}
