<?php

namespace Cc\Mvc;

use Cc\Mvc;

/**
 * clase encargada de cargar los views de CcMvc
 *                                                           
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * @package CcMvc  
 * @subpackage view
 * @internal usada por {@link App} y {@link Controllers} para cargar los views
 * @property string $ObjResponse Objeto de respuesta de CcMvc                                     
 */
class ViewController
{

    /**
     * directorio donde se encuentra los views de la aplicacion
     * @var string 
     */
    protected $_View_Directory;

    /**
     * directorio de los views internos 
     * @var string 
     */
    protected $_Internal_Directory;

    /**
     * variables que seram pasadas a el view que sea cargado
     * @var array 
     */
    protected $ViewVars = [];

    /**
     * 
     * @internal 
     * @param string $dir directorio donde se encuentran los archivos de vista 
     */
    public function __construct($dir = NULL)
    {
        if (!is_dir($dir))
        {
            throw new Exception("EL DIRECTORIO DE VISTAS (" . $dir . ") NO EXISTE ");
        }
        $this->_View_Directory = $dir;
        $this->_Internal_Directory = dirname(__FILE__) . DIRECTORY_SEPARATOR;
    }

    /**
     * @access private
     * @return type
     */
    public function __debugInfo()
    {
        return $this->ViewVars;
    }

    public function ListOfViews($dirView = '', $completePath = false, $recursive = false)
    {
        $dir = [];
        $dirrectory = $this->_View_Directory . $dirView;
        if ($dirrectory[strlen($dirrectory) - 1] != '/' || $dirrectory[strlen($dirrectory) - 1] != DIRECTORY_SEPARATOR)
        {
            $dirrectory.=DIRECTORY_SEPARATOR;
        }

        // $dir = scandir($dirrectory);
        foreach (scandir($dirrectory) as $d)
        {
            if (is_dir($d) && $recursive)
            {
                $dir+=$this->ListOfViews($dirrectory . $d . DIRECTORY_SEPARATOR, $completePath, $recursive);
            } elseif (is_file($dirrectory . $d) && substr($d, -4, 4) == '.' . Mvc::App()->Config()->ViewLoaders['Default']['ext'])
            {
                /* @var $completePath type */
                if ($completePath)
                {
                    array_push($dir, $dirrectory . $d);
                } elseif ($recursive)
                {
                    array_push($dir, ($dirView != '' ? '/' : '' ) . substr($d, 0, -4));
                } else
                {
                    array_push($dir, substr($d, 0, -4));
                }
            }
        }
        return $dir;
    }

    /**
     *  CARGAR DOCUMENTO VISTA (view)
     *  @param string $view nombre del documento sin extencion
     *  @param array $agrs ARRAY ASOCIATIVO CON LAS VARIABLES QUE TENDRA DISPONIBLE EL DOCUMENTO VISTA EL OBJETO DE CONTROL DE CONTENIDO SE PARASA EN LA VARIABLE $OBjconten COMO REFERENCIA
     *  POR LO QUE NO SERA NESEARIO PARASALO EN EL ARRAY
     *  @example ../examples/CERQU/protected/view/estudiantes/index.php ejemplo de un archivo view o vista 
     */
    public function Load($view, array ...$agrs)
    {
        return $this->LoadView($this->_View_Directory, $view, ...$agrs);
    }

    /**
     *  CARGAR DOCUMENTO VISTA (view) Y RETORNA SU CONTENIDO
     *  @param string $view nombre del documento sin extencion
     *  @param array $agrs ARRAY ASOCIATIVO CON LAS VARIABLES QUE TENDRA DISPONIBLE EL DOCUMENTO VISTA EL OBJETO DE CONTROL DE CONTENIDO SE PARASA EN LA VARIABLE $OBjconten COMO REFERENCIA
     *  POR LO QUE NO SERA NESEARIO PARASALO EN EL ARRAY
     * @return string 
     *  @example ../examples/CERQU/protected/view/estudiantes/index.php ejemplo de un archivo view o vista 
     */
    public function Fetch($view, array ...$agrs)
    {
        return $this->FetchView($this->_View_Directory, $view, ...$agrs);
    }

    /**
     *  CARGAR DOCUMENTO VISTA (view) INTERNO
     *  @param string $view nombre del documento sin extencion
     *  @param array $agrs ARRAY ASOCIATIVO CON LAS VARIABLES QUE TENDRA DISPONIBLE EL DOCUMENTO VISTA EL OBJETO DE CONTROL DE CONTENIDO SE PARASA EN LA VARIABLE $OBjconten COMO REFERENCIA
     *  POR LO QUE NO SERA NESEARIO PARASALO EN EL ARRAY
     *  
     */
    public function LoadInternalView($view, array ...$agrs)
    {
        return $this->LoadView($this->_Internal_Directory, $view, ...$agrs);
    }

    /**
     * 
     */
    private function LoadView($dir, $view, array ...$agrs)
    {
        // $view = ValidFilename::ValidName($view, true);

        foreach ($agrs as &$_________agrs)
        {
            foreach ($_________agrs as $_i => &$_v)
            {
                $this->ViewVars[$_i] = &$_v;
            }
        }
        unset($_________agrs, $_i, $_v);
        if (!isset($this->ViewVars['ObjResponse']))
            $this->ViewVars['ObjResponse'] = &Mvc::App()->Response;

        if (preg_match('/\.\.\//', $view))
        {
            throw new ViewException("EL NOMBRE DEL VIEW " . $view . " NO ES VALIDO");
        }
        if ((strpos($view, ':') !== false))
        {
            $this->_include($view);
        } elseif (file_exists($dir . $view . '.' . Mvc::App()->Config()->ViewLoaders['Default']['ext']))
        {
            $this->ViewVars['ViewName'] = $view;
            $this->_include($dir . $view . '.' . Mvc::App()->Config()->ViewLoaders['Default']['ext']);
        } elseif (is_dir($dir . $view) && file_exists($dir . $view . 'index.' . Mvc::App()->Config()->ViewLoaders['Default']['ext']))
        {
            $this->ViewVars['ViewName'] = $view . 'index';
            $this->_include($dir . $view . 'index.' . Mvc::App()->Config()->ViewLoaders['Default']['ext']);
        } else
        {
            $this->_include($dir . $view);
        }
    }

    /**
     * 
     */
    private function FetchView($dir, $view, array ...$agrs)
    {
        // $view = ValidFilename::ValidName($view, true);

        foreach ($agrs as &$_________agrs)
        {
            foreach ($_________agrs as $_i => &$_v)
            {
                $this->ViewVars[$_i] = &$_v;
            }
        }
        unset($_________agrs, $_i, $_v);
        if (!isset($this->ViewVars['ObjResponse']))
            $this->ViewVars['ObjResponse'] = &Mvc::App()->Response;

        if (preg_match('/\.\.\//', $view))
        {
            throw new ViewException("EL NOMBRE DEL VIEW " . $view . " NO ES VALIDO");
        }
        if ((strpos($view, ':') !== false))
        {
            return $this->_include($view, true);
        } elseif (file_exists($dir . $view . '.' . Mvc::App()->Config()->ViewLoaders['Default']['ext']))
        {
            $this->ViewVars['ViewName'] = $view;
            return $this->_include($dir . $view . '.' . Mvc::App()->Config()->ViewLoaders['Default']['ext'], true);
        } elseif (is_dir($dir . $view) && file_exists($dir . $view . 'index.' . Mvc::App()->Config()->ViewLoaders['Default']['ext']))
        {
            $this->ViewVars['ViewName'] = $view . 'index';
            return $this->_include($dir . $view . 'index.' . Mvc::App()->Config()->ViewLoaders['Default']['ext'], true);
        } else
        {
            return $this->_include($dir . $view, true);
        }
    }

    /**
     * 
     * @param string $file
     */
    private function _include($file, $fetch = false)
    {
        try
        {
            $loader = new ViewLoader(Mvc::App()->Config());
            if ($fetch)
            {
                return $loader->Fetch($this, $file, $this->ViewVars);
            } else
            {
                $loader->Load($this, $file, $this->ViewVars);
            }
        } catch (ViewLoaderException $ex)
        {
            throw new ViewException("El View " . $file . " no existe");
        }
    }

    public function __set($name, $value)
    {
        $this->ViewVars[$name] = $value;
    }

    public function __get($name)
    {
        if (isset($this->ViewVars[$name]))
        {
            return $this->ViewVars[$name];
        } else
        {
            ErrorHandle::Notice("Propiedad no Establecida: " . $name);
        }
    }

    public function __unset($name)
    {
        unset($this->ViewVars[$name]);
    }

    public function __isset($name)
    {
        return isset($this->ViewVars[$name]);
    }

}

class ViewException extends Exception
{
    
}
