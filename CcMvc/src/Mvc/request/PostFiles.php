<?php

namespace Cc\Mvc;

/**
 * recibe los archivos enviados por el navegador
 *  <code>
 * <?php
 * // cuando se recible un archivo mediante post
 * $file= new PostFiles('miFile');
 * if($file->is_Uploaded())
 * {
 *      $file->Copy('C:/Xampp/htdocs/miapp/miFile.txt');
 * }
 * ?>
 * </code>
 * En caso de esperar varios archivos 
 *   <code>
 * <?php
 * // cuando se recible varios archivos mediante post
 * $Files= new PostFiles('miFile');
 * if($Files->is_Uploaded())
 * {
 *      foreach($Files as $f)
 *      {
 *          $f->Copy('C:/Xampp/htdocs/miapp/'.$f['name']);
 *      }
 * }
 * ?>
 * </code>
 * @autor ENYREBER FRANCO       <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>                                                    
 * @package CcMvc
 * @subpackage Request
 *
 */
class PostFiles extends \SplFileInfo implements \ArrayAccess, \IteratorAggregate
{

    protected $name;
    protected $File = array();
    protected $ext;
    protected $multiple = false;
    protected $MultiFile = array();
    protected $numFiles = 0;

    /**
     * 
     * @param string $name nombre de la variable _FILES que contiene la informacion del archivo
     */
    public function __construct($name)
    {
        if (is_array($name))
        {
            $this->File = $name;
            if ($this->is_Uploaded())
            {
                $this->numFiles = 1;
                parent::__construct($this->File['name']);
            }
        } elseif (!empty($_FILES[$name]))
        {
            if (is_array($_FILES[$name]['name']))
            {
                $this->multiple = true;

                foreach ($_FILES[$name]['name'] as $i => $v)
                {
                    $file = [];
                    foreach ($_FILES[$name] as $j => $k)
                    {
                        $file[$j] = $v;
                    }
                    if (is_uploaded_file($file['tmp_name']))
                    {
                        $this->MultiFile[$i] = new self($file);
                        $this->numFiles++;
                    }
                }
                $this->File = [];
            } else
            {
                $this->File = $_FILES[$name];
                if ($this->is_Uploaded())
                {
                    $this->numFiles = 1;
                    parent::__construct($this->File['name']);
                }
            }
        }
    }

    /**
     * cantidad de archivos recibidos 
     * @return int
     */
    public function NumFiles()
    {
        return $this->numFiles;
    }

    /**
     * RETORNA EL OBJETO PostFiles ASOCIADO AL INDICE 
     * @param string $name
     * @return PostFiles|NULL
     */
    public function GetPostFile($name)
    {
        if (isset($this->MultiFile[$name]))
        {
            return $this->MultiFile[$name];
        }
    }

    /**
     * indica si fueron recibidos mas de un archivos 
     * @return boolean
     */
    public function IsUploadedMultiple()
    {
        if (!$this->multiple)
        {
            return false;
        }
        foreach ($this->MultiFile as $v)
        {
            if (is_uploaded_file($v['tmp_name']))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * retorna el error del archivo si ocurrio alguno 
     * @return boolean|string
     */
    public function error()
    {
        if (!isset($this->File['error']))
        {
            return false;
        }
        switch ($this->File['error'])
        {
            case UPLOAD_ERR_INI_SIZE:
                return 'El fichero subido excede la directiva upload_max_filesize de php.ini.';
            case UPLOAD_ERR_FORM_SIZE;
                return 'El fichero subido excede la directiva MAX_FILE_SIZE especificada en el formulario HTML.';
            case UPLOAD_ERR_PARTIAL:
                return 'El fichero fue sólo parcialmente subido';
            case UPLOAD_ERR_NO_FILE:
                return 'No se subió ningún fichero.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Falta el directorio temporal';
            case UPLOAD_ERR_CANT_WRITE:
                return 'No se pudo escribir el fichero en el disco';
            case UPLOAD_ERR_EXTENSION:
                return ' Una extensión de PHP detuvo la subida de ficheros. ';
        }
    }

    /**
     * retorna el numero de error del archivo si ocurrio alguno
     * @return int
     */
    public function errno()
    {
        if (!isset($this->File['error']))
        {
            return false;
        } else
        {
            return $this->File['error'];
        }
    }

    /**
     * @ignore
     * @return type
     */
    public function __debugInfo()
    {
        return $this->File;
    }

    /**
     * indica si el archivo se recibio o no
     * @return boolean
     */
    public function is_Uploaded()
    {
        if ($this->multiple)
        {
            return $this->IsUploadedMultiple();
        }
        if (!empty($this->File['tmp_name']))
        {
            return is_uploaded_file($this->File['tmp_name']);
        } else
        {
            return false;
        }
    }

    /**
     * copia el archivo recibido al destino 
     * @param string $dest directorio donde se copiara
     */
    public function Copy($dest)
    {
        copy($this->File['tmp_name'], $dest);
    }

    /**
     * retorna el contenido del archivo recibido
     * @return string
     */
    public function GetContent()
    {
        return file_get_contents($this->File['tmp_name']);
    }

    /**
     * retorna un objeto SplFileObject con el archivo recibido
     * @param string $open_mode
     * @param string $use_include_path
     * @param string $context
     * @return SplFileObject
     */
    public function openFile($open_mode = "r", $use_include_path = false, $context = null)
    {
        return new \SplFileObject($this->File['tmp_name'], $open_mode, $use_include_path, $context);
    }

    public function isDir()
    {
        return isset($this->File['tmp_name']) && is_dir($this->File['tmp_name']);
    }

    public function isFile()
    {
        return isset($this->File['tmp_name']) && is_file($this->File['tmp_name']);
    }

    public function isReadable()
    {
        return isset($this->File['tmp_name']) && is_readable($this->File['tmp_name']);
    }

    public function isExecutable()
    {
        return isset($this->File['tmp_name']) && is_executable($this->File['tmp_name']);
    }

    public function getSize()
    {
        return isset($this->File['tmp_name']) && filesize($this->File['tmp_name']);
    }

    /**
     * @access private
     * @param type $offset
     * @param type $value
     */
    public function offsetSet($offset, $value)
    {
        $this->File[$offset] = $value;
    }

    /**
     *  @access private
     * @param type $offset
     * @return type
     */
    public function offsetExists($offset)
    {
        return isset($this->File[$offset]);
    }

    /**
     *  @access private
     * @param type $offset
     */
    public function offsetUnset($offset)
    {

        unset($this->File[$offset]);
    }

    /**
     *  @access private
     * @return type
     */
    public function __toString()
    {
        if ($this->is_Uploaded())
        {
            return $this->File['tmp_name'];
        }
        return '';
    }

    /**
     *  @access private
     * @param type $offset
     * @return type
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset))
        {
            ErrorHandle::Notice("Undefined index: " . $offset);
            return;
        }
        return $this->File[$offset];
    }

    public function getIterator()
    {
        if ($this->multiple)
        {
            return new \ArrayIterator($this->MultiFile);
        } else
        {
            return new \ArrayIterator([$this->File]);
        }
    }

}
