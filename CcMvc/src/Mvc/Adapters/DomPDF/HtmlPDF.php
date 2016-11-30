<?php

/*
 * Copyright (C) 2016 Enyerber Franco
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  
 */

namespace Cc\Mvc;

use Dompdf\Dompdf;
use Dompdf\Canvas;
use Dompdf\Frame;
use Cc\Mvc;

/**
 * CLASE DE RESPUESTA PROCESA EL CONTENIDO HTML Y LO ENVIA COMO PDF 
 * NESESITA DE LA LIBRERIA DomPDF LA CUAL NO ESTA INCLUIDA EN EL FRAMEWORK
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc
 * @subpackage Adapters
 */
class HtmlPDF extends Html
{

    /**
     * instancia de \Dompdf\Dompdf 
     * @var \Dompdf\Dompdf 
     */
    protected $domPdf;

    /**
     * encodig
     * @var string 
     */
    protected $encoding = 'utf-8';

    /**
     *
     * @var array 
     */
    private $eventos = [];

    /**
     * Eventos del usuario 
     * @var array
     */
    protected $UserEvents = ['BeginPage' => [], 'EndPage' => []];

    /**
     * DIMENCIONES PARA UNA PAGINA DE TAMAÑO CARTA
     */
    const PageLetter = [0, 0, 612, 792];

    /**
     * DIMENCIONES PARA UNA PAGINA DE TAMAÑO OFICIO
     */
    const PageLegal = [0, 0, 612, 1008];

    /**
     * DIMENCIONES PARA UNA PAGINA DE TAMAÑO a3
     */
    const PageA3 = [0, 0, 841.89, 1190.55];

    /**
     * DIMENCIONES PARA UNA PAGINA DE TAMAÑO a4
     */
    const pageA4 = [0, 0, 595.28, 841.89];

    /**
     * DIMENCIONES PARA UNA PAGINA DE TAMAÑO a5
     */
    const PageA5 = [0, 0, 420.94, 595.28];

    /**
     * PAGINA VERICAL 
     */
    const PageVertical = 'portrait';

    /**
     * PAGINA ORIZONTAL
     */
    const PageOrizontal = 'landscape';

    protected $size;

    /**
     * @access private
     * @return self
     */
    public static function CtorParam()
    {
        Mvc::App()->ChangeResponseConten('application/pdf');
        return Mvc::App()->Response;
    }

    /**
     * 
     * @param string|array $size dimenciones de las paginas se pueden usar las constantes {@link HtmlPDF::PageLetter}, {@link HtmlPDF::PageLegal}, {@link HtmlPDF::PageA3}, {@link HtmlPDF::pageA4},  {@link HtmlPDF::PageA5}
     * @param string $orientation indica la orientacion de las paginas se pueden usar las constantes {@link HtmlPDF::PageVertical}, {@link HtmlPDF::PageOrizontal}
     * @param bool $compress indica si se conprimira o no 
     */
    public function __construct($size = 'letter', $orientation = "portrait", $compress = true)
    {
        parent::__construct($compress, false);

        $this->setPaper($size, $orientation);
    }

    /**
     * RETORNA EL CONTENIDO PARA SER INSERTADO DETRO DE HEAD GENERALMENTE SE USA EN EL LAYAUT
     * ESTO INCLUYE LAS ETIQUETAS <TITLE><SCRITS><LINK> Y EL ICONO
     * @return string  
     */
    public function GetContenHead()
    {

        $js = $this->GetJsScript();
        $css = $this->GetCssScript();

        $head = "\n" . $this->Head . "\n";



        $head .= self::base(['href' => $this->BasePath]);
        if ($this->ico)
            $head .= self::link(['rel' => 'shortcut icon', 'href' => $this->ico, 'media' => 'monochrome']);
        if ($this->titulo)
            $head.=self::title($this->titulo);

        $head.=$this->link_cssjs();
        if ($js != "")
            $head.=self::script($js, ['type' => 'text/javascript']);
        if ($css != "")
            $head.=self::style($css, ['type' => 'text/css']);
        return $head;
    }

    /**
     * AGREGA UN EVENTO 
     * @param \Cc\Mvc\EventsHtmlPDF|string $event
     * @param callable $callback
     */
    public function On($event, callable $callback)
    {
        if ($event instanceof EventsHtmlPDF)
        {
            $this->UserEvents['BeginPage'][] = [&$event, 'BeginPage'];
            $this->UserEvents['EndPage'][] = [&$event, 'EndPage'];
        } else
        {
            $this->UserEvents[$event][] = $callback;
        }
    }

    /**
     * @internal 
     * @param array $agrs
     */
    public function begin_page_reflow($agrs)
    {
        
    }

    /**
     * @internal 
     * @param array $agrs
     */
    public function begin_page_render($agrs)
    {
        foreach ($this->UserEvents['BeginPage'] as $f)
        {
            if (is_callable($f))
            {
                if (is_array($f))
                {
                    $f[0]->{$f[1]}($agrs[0], $agrs[1]);
                } else
                {
                    $f($agrs[0], $agrs[1]);
                }
            }
        }
    }

    /**
     * @internal 
     * @param array $agrs
     */
    public function end_page_render($agrs)
    {
        foreach ($this->UserEvents['EndPage'] as $f)
        {
            if (is_callable($f))
            {
                if (is_array($f))
                {
                    $f[0]->{$f[1]}($agrs[0], $agrs[1]);
                } else
                {
                    $f($agrs[0], $agrs[1]);
                }
            }
        }
    }

    /**
     * establece las dimenciones de las paginas 
     * @param string|array $size dimenciones de las paginas se pueden usar las constantes {@link HtmlPDF::PageLetter}, {@link HtmlPDF::PageLegal}, {@link HtmlPDF::PageA3}, {@link HtmlPDF::PageA4},  {@link HtmlPDF::PageA5}
     * @param string $orientation indica la orientacion de las paginas se pueden usar las constantes {@link HtmlPDF::PageVertical}, {@link HtmlPDF::PageOrizontal}
     *
     */
    public function setPaper($size, $orientation = "portrait")
    {
        $this->size = [$size, $orientation];
        // $this->domPdf->setCallbacks($callbacks);
    }

    /**
     * 
     * @param string $name
     * @param \Cc\Mvc\callable $callback
     */
    private function Event($name, callable $callback)
    {
        $c = [];
        $c['event'] = $name;
        $c['f'] = $callback;

        array_push($this->eventos, $c);
    }

    public function GetLayaut()
    {
        $layaut = parent::GetLayaut();
        $content = '';
        if (!is_null($layaut['Layaut']) && $layaut['Layaut'] != '')
        {
            $name = ($layaut['Dir'] . $layaut['Layaut']);
            if (!file_exists($name))
            {
                $name.='.php';
            }
            if ((strpos($layaut['Layaut'], ':') !== false))
            {
                $name.=$layaut['Layaut'];
            }
            Mvc::App()->Log("CARGANDO EL LAYAUT " . $name . " ...");
            try
            {
                $param = ['content' => DocumentBuffer::Conten()] + Mvc::App()->LayautManager->jsonSerialize();
                if (isset($layaut['params']))
                {
                    $param+=$layaut['params'];
                }


                DocumentBuffer::Clear();
                $loader = new TemplateLoad(Mvc::App()->Config());
                $content = $loader->Fetch($this, $name, $param);
                Mvc::App()->Log("LAYAUT " . $name . " CARGADO  ...");
            } catch (LayautException $ex)
            {
                throw $ex;
            } catch (TemplateException $ex)
            {
                throw new LayautException("EL LAYAUT " . $name . " NO EXISTE ");
            } catch (Exception $ex)
            {
                throw $ex;
            }
        }
        $layaut['Layaut'] = NULL;
        if (http_response_code() !== 200)
        {
            echo $content;
            return $layaut;
        }

        try
        {

            $conten = $this->ProccessPdf($content);
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $this->titulo . '.pdf"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            Mvc::App()->Log("PDF COMPLETO...");

            echo $conten;
        } catch (\Exception $ex)
        {
            throw $ex;
        } catch (\Error $ex)
        {
            throw $ex;
        }
        return $layaut;
    }

    private function ProccessPdf($str)
    {
        if (!class_exists("\\Dompdf\\Dompdf"))
        {

            throw new Exception("HtmlPDF REQUIERE LA LIBRERIA EXTERNA Dompdf");
        }
        $this->domPdf = new Dompdf(['isHtml5ParserEnabled' => true, 'enable_remote' => true, 'isJavascriptEnabled' => true, 'isFontSubsettingEnabled' => true]);
        $this->domPdf->setBaseHost($_SERVER['HTTP_HOST']);
        $this->domPdf->setProtocol($this->AppConfig->Router['protocol']);
        $this->domPdf->setBasePath($this->BasePath);
        $this->domPdf->setPaper(...$this->size);

        $this->Event('begin_page_reflow', [&$this, 'begin_page_reflow']);
        $this->Event('begin_page_render', [&$this, 'begin_page_render']);
        $this->Event('end_page_render', [&$this, 'end_page_render']);
        // $this->domPdf->setOptions($options);
        $this->domPdf->setCallbacks($this->eventos);
        $this->domPdf->loadHtml($str);
        Mvc::App()->Log("GENERANDO PDF...");


        $this->domPdf->render();

        return $this->domPdf->output();
    }

}

/**
 * interface para eventos de HtmlPDF
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc
 * @subpackage Response
 */
interface EventsHtmlPDF
{

    public function BeginPage(Canvas $canvas, Frame $frame);

    public function EndPage(Canvas $canvas, Frame $frame);
}
