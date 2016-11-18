<?php

namespace Cc;

/**
 * Manipulacion de imagenes
 * @autor ENYREBER FRANCO       <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                    
 * @package Cc
 * @subpackage GD 
 * @todo falta mejorar, implementar todos las funciones GD
 */
class ImageGD
{

    /**
     * identificador de imagen
     * @var resourece 
     */
    protected $img;

    /**
     * ancho de la imagen
     * @var  int
     */
    public $w;

    /**
     * alto de la imagen
     * @var int  
     */
    public $h;

    /**
     * extencion de la imagen
     * @var string 
     */
    protected $tipo;

    /**
     * indice de color de fondo de la imagen
     * @var int 
     */
    protected $fondo;

    /**
     * fuentes
     * @var array 
     */
    protected $fuente;

    /**
     * colores
     * @var array 
     */
    protected $colores;

    /**
     *
     * @var array 
     */
    protected $img_import;
    protected $mask_color;
    protected $hader;

    /**
     * 
     * @param string|int $ancho si es int es el ancho de la imagen que se creara, si es un binario de imagen se cargara la misma, si es el nombre de el archivo de una imagen se cargara
     * @param int $alto alto de la imagen
     * @param string $tipo tipo de imagen
     */
    public function __construct($ancho = NULL, $alto = NULL, $tipo = NULL)
    {
        $this->fuente = NULL;
        $this->colores = array();
        $this->img_import = array();
        $this->fuente = array();
        if (!is_null($ancho))
        {
            if (is_numeric($ancho))
            {
                $this->Create($ancho, $alto, $tipo);
            } elseif (is_file($ancho) || is_readable($ancho))
            {
                $this->LoadFile($ancho);
            } elseif (is_string($ancho))
            {
                $this->LoadString($ancho);
            }
        }
    }

    /**
     * crea una imagen en blanco
     * @param int $ancho ancho de la imagen
     * @param int $alto alto de la imagen
     * @param string $tipo mime-type de la imagen
     */
    public function Create($ancho, $alto, $tipo)
    {
        $this->w = $ancho;
        $this->h = $alto;

        $this->fuente = NULL;
        $this->colores = array();
        $this->img_import = array();
        $this->fuente = array();

        $this->hader = $tipo;
        if ($tipo == "image/x-png" || $tipo == "image/png")
        {
            $this->img = imagecreatetruecolor($ancho, $alto);
            $this->tipo = 'png';
        }
        if ($tipo == "image/pjpeg" || $tipo == "image/jpeg" || $tipo == "image/jpg")
        {
            $this->tipo = 'jpeg';
            $this->img = imagecreatetruecolor($ancho, $alto);
        }
        if ($tipo == "image/gif" || $tipo == "image/gif")
        {
            $this->tipo = 'gif';
            $this->img = imagecreatetruecolor($ancho, $alto);
        }

        $this->fondo = imagecolorallocate($this->img, 255, 255, 255);
        $this->mask_color = imagecolorallocate($this->img, 255, 255, 255);
        //$this->rectangulo_ex(0,0,$this->w,$this->h,imagecolorallocate ($this->img, 255, 0, 255));
        imagefill($this->img, 0, 0, $this->fondo);
        //imagecolortransparent($this->img ,$this->fondo);
    }

    /**
     * Carga una imagen de un archivo
     * @param string $filename nombre del archivo
     * @throws Exception si el archivo no existe o si ocurre un error en la lectura
     */
    public function LoadFile($filename)
    {
        //$tam = getimagesize($filename);
        $data = file_get_contents($filename);
        if ($data !== false)
        {
            $this->LoadString($data);
        } else
        {
            throw new Exception("El archivo '" . $filename . "' no existe");
        }
    }

    /**
     * cargar una imagen de un string
     * @param binary $string imagen
     */
    public function LoadString($string)
    {
        $tam = getimagesizefromstring($string);
        list( $this->w, $this->h ) = $tam;
        $type = $tam['mime'];
        // return var_export($tam, true);
        $this->img = imagecreatefromstring($string);
        switch ($type)
        {
            case 'image/png':
                $this->tipo = 'png';

                break;
            case 'image/jpg':
            case 'image/jpeg':

                $this->tipo = 'jpeg';
                break;
            case 'image/gif':

                $this->tipo = 'gif';
                break;
        }
        $this->Ini();
    }

    /**
     * inicializacion
     */
    protected function Ini()
    {
        $this->fuente = NULL;
        $this->colores = array();
        $this->img_import = array();
        $this->fuente = array();

        if ($this->tipo == 'png' || $this->tipo == 'gif')
        {
            imagealphablending($this->img, false);
            imagesavealpha($this->img, true);
        }
        $this->fondo = imagecolorallocate($this->img, 255, 255, 255);
        $istransparent = imagecolortransparent($this->img);
        // throw new Exception($istransparent);
        if ($istransparent !== -1)
        {
            $tp = imagecolorsforindex($this->img, $istransparent);
            $this->mask_color = imagecolorallocatealpha($this->img, $tp['red'], $tp['green'], $tp['blue'], $tp['alpha']);
        } else
        {
            $this->mask_color = imagecolorallocatealpha($this->img, 255, 255, 255, 127);
            imagecolortransparent($this->img, $this->mask_color);
        }
        imagealphablending($this->img, true);
    }

    /**
     * Guarda o retorn la imagen
     * @param string $name nombre de laimagen
     * @param string $ouput indica la manera que se retornara la imagen "I" para vaciarla en el buffer de salida, "F" para almacenar la imagen el un archivo, "S" para retornar el binario de la imagen
     * @param array $options parametros de opciones para las funciones gd de salida
     * @return string
     */
    public function Output($name = "", $ouput = "I", $options = [])
    {

        $image = "image";
        $image.=$this->tipo;
        switch ($ouput)
        {
            case "I":
                {
                    header("Content-type: " . $this->hader);
                    header('Content-Disposition: inline; filename="' . $name . '"');
                    header('Pragma: public');
                    $image($this->img, NULL, ...$options);
                } break;
            case "F":
                {
                    $image($this->img, $name . "." . $this->tipo, ...$options);
                    return $name . "." . $this->tipo;
                }break;
            case 'S':
                $name = tempnam(sys_get_temp_dir(), 'ImageGD');
                $image($this->img, $name, ...$options);
                $str = file_get_contents($name);
                unlink($name);
                return $str;
        }
    }

    public function __destruct()
    {
        $this->destroy();
    }

    /**
     * destrulle la imagen
     */
    public function destroy()
    {

        imagedestroy($this->img);
    }

    /**
     * redimenciona la imagen
     * @param int $nuevo_ancho ancho 
     * @param int $nuevo_alto alto
     */
    public function Resize($nuevo_ancho = NULL, $nuevo_alto = NULL)
    {
        if (is_null($nuevo_ancho) && !is_null($nuevo_alto))
        {
            $proc = ($nuevo_alto * 100) / ( $this->h);
            $nuevo_ancho = ( $this->h * ($proc * 0.01));
        } elseif (is_null($nuevo_alto) && !is_null($nuevo_ancho))
        {
            $proc = ($nuevo_ancho * 100) / ($this->w);
            $nuevo_alto = ($this->w * ($proc * 0.01));
        }
        $temp = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
        //imagealphablending($temp, true);
        // imagecolormatch($this->img, $temp);

        if ($this->tipo == 'png' || $this->tipo == 'gif')
        {
            imagealphablending($temp, false);
            imagesavealpha($temp, true);
            imagefill($temp, 0, 0, $this->mask_color);
            imagecolortransparent($temp, $this->mask_color);
        }
        imagecopyresampled($temp, $this->img, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $this->w, $this->h);
        $istransparent = imagecolortransparent($temp);
        if ($istransparent !== -1)
        {
            $tp = imagecolorsforindex($temp, $istransparent);
            $this->mask_color = imagecolorallocatealpha($temp, $tp['red'], $tp['green'], $tp['blue'], $tp['alpha']);
        }
        imagedestroy($this->img);
        unset($this->img);
        $this->img = $temp;
        unset($temp);
        $this->w = $nuevo_ancho;
        $this->h = $nuevo_alto;
    }

    /**
     * establece el color de mascara 
     * @param int|array $color 
     */
    public function ColorMask($color)
    {
        $this->mask_color = $this->Color($color);
        imagecolortransparent($this->img, $this->mask_color);
    }

    /**
     * caraga un archivo ttf para las fuente de texto
     * @param string $name
     * @param string $file_ttf
     * @return int
     */
    public function LoadTtf($name, $file_ttf)
    {
        if ($file_ttf != '')
        {
            $this->fuente+=array($name => $file_ttf);
            return 0;
        }
        return 1;
    }

    /**
     * almacena un  color bajo un nombre
     * @param string $name nombre
     * @param int $R rojo
     * @param int $G verde
     * @param int $B azul
     * @param int $A alpha
     * @return int indice de color
     */
    public function SaveColor($name, $R, $G, $B, $A = 0)
    {
        $this->colores+=array($name => imagecolorallocatealpha($this->img, $R, $G, $B, (int) ($A)));
        return $this->colores[$name];
    }

    /**
     * crea un indice de color 
     * @param int $R rojo
     * @param int $G verde
     * @param int $B azul
     * @param int $A alpha
     * @return int indice de color
     */
    public function Rgba($R, $G, $B, $A = 0)
    {
        return imagecolorallocatealpha($this->img, $R, $G, $B, (int) ($A));
    }

    public function Fill($x, $y, $rgb_color)
    {
        imagefill($this->img, $x, $y, $this->Color($rgb_color));
    }

    public function Linea($x, $y, $w, $h, $rgb_color)
    {
        imageline($this->img, $x, $y, $w, $h, $this->Color($rgb_color));
    }

    public function Rectangulo($x, $y, $w, $h, $rgb_color)
    {
        imagerectangle($this->img, $x, $y, $w, $h, $this->Color($rgb_color));
    }

    public function FilleRectangulo($x, $y, $w, $h, $rgb_color)
    {
        imagefilledrectangle($this->img, $x, $y, $w, $h, $this->Color($rgb_color));
    }

    public function Text($cadena, $tam, $angulo, $x, $y, $rgb_color, $fuente = 'arial.ttf')
    {

        imagettftext($this->img, $tam, $angulo, $x, $y, $this->Color($rgb_color), $this->Font($fuente), $cadena);
    }

    public function CopyResample(ImageGD $img_class, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)
    {
        imagecopyresampled($this->img, $img_class->img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
    }

    private function Color($rgb_color)
    {
        if (is_array($rgb_color))
        {
            if (count($rgb_color) == 3)
            {
                list($red, $green, $blue) = $rgb_color;
                return imagecolorallocate($this->img, $red, $green, $blue);
            } else if (count($rgb_color) == 3)
            {
                list($red, $green, $blue, $alpha) = $rgb_color;
                return imagecolorallocatealpha($this->img, $red, $green, $blue, $alpha);
            }
        } elseif (isset($this->colores[$rgb_color]))
        {
            return $this->colores[$rgb_color];
        }
    }

    private function Font($fuente)
    {
        if (isset($this->fuente[$fuente]))
        {
            return $this->fuente[$fuente];
        } else
        {
            return $fuente;
        }
    }

    public function GetPixel($x, $y)
    {
        $rgb = imagecolorat($this->img, $x, $y);
        $color = imagecolorsforindex($this->img, $rgb);

        return [$color['red'], $color['green'], $color['blue'], $color['alpha']];
    }

}

/*
  $imagen=new IMG(900,1140,"image/png");
  $imagen->create_color('negro',255, 255, 255);
  $imagen->create_color('amarillo',255, 255,0);
  $imagen->linea(0,0,200,200,"negro");

  $imagen->create_color('alpha',255, 255,0,100);//crear solo antes de utilizar
  $imagen->rectangulo_ex(150, 150, 300, 300, 'alpha');
  $imagen->load_ttf('font','airstrike.ttf');
  $imagen->text_print_ttf(40,20,300,300,'negro','font'," esta es la cadena");
  $imagen->importar_img('2013','../img/2013.png');
  $imagen->print_img_import('2013',300,300,0,0,100,100);
  $imagen->print_img_import_alpha('2013',25,25,0,0,80);
 */
?>