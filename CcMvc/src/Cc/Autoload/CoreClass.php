<?php

namespace Cc\Autoload;

include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'SearchClass' . DIRECTORY_SEPARATOR . 'SearchClass.php';

const FileCore = 'Cache.CoreClass.inc';

/**
 * carga automaticamente las clases 
 * 
 * @author ENYREBER FRANCO  <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>      
 * @package Cc
 * @subpackage Autoload
 * @internal 
 */
trait CoreClass
{

    /**
     * array asociativo con los nombre de archivos donde se encuentran las clase 
     * @var array 
     */
    protected $CoreClass = [];
    protected $AppDir;
    protected $FileCoreClass;
    private $Reestart = false;

    /**
     * 
     * @param string $AppDir directorio de donde se alojan las clases
     */
    protected function StartAutoloadCore($AppDir, $RestartIsCreate = true)
    {
        $this->Reestart = $RestartIsCreate;
        $this->AppDir = realpath($AppDir);
        $this->FileCoreClass = DIRECTORY_SEPARATOR . FileCore;
        // $this->CoreClass = [ "SearchClass" => dirname(__FILE__).DIRECTORY_SEPARATOR."SearchClass" . DIRECTORY_SEPARATOR . "SearchClass.php"];
        spl_autoload_register([&$this, 'autoloadCore']);
        if (!file_exists($this->AppDir . $this->FileCoreClass))
        {
            $this->CreateCoreClass();
        }
        try
        {
            $core = include($this->AppDir . $this->FileCoreClass);
        } catch (\Exception $ex)
        {
            $this->CreateCoreClass();
        } catch (\Error $ex)
        {
            $this->CreateCoreClass();
        }

        if ($core['DIRECTORY_SEPARATOR'] !== DIRECTORY_SEPARATOR)
        {
            $this->CreateCoreClass();
        } else
        {
            $this->CoreClass = $core['class'];
        }
    }

    public function StopAutoloadCore()
    {
        spl_autoload_unregister([&$this, 'autoloadCore']);
    }

    /**
     * retorna el nombre del archivo donde se guarda el cache de las clases almacenadas 
     * @return string
     */
    public function GetFileCoreClass()
    {
        return $this->AppDir . $this->FileCoreClass;
    }

    /**
     * 
     */
    private function CreateCoreClass()
    {
        $app = $this->AppDir . DIRECTORY_SEPARATOR;
        $this->CoreClass = SearchClass::GetListAllClass($app);
        $ARRAY = var_export(['class' => $this->CoreClass, 'DIRECTORY_SEPARATOR' => DIRECTORY_SEPARATOR], true);
        $f = fopen($this->AppDir . $this->FileCoreClass, 'w+');
        $a = "<?php\n/* Create by \\" . static ::class . " " . date("Y-m-d H:i:s") . " */\n return " . $ARRAY . ';';
        fwrite($f, $a, strlen($a));
        fclose($f);

        if ($this->Reestart)
        {
            if (php_sapi_name() !== 'cli')
            {
                header("Location: " . $_SERVER['REQUEST_URI']);
            }
        }
    }

    /**
     * CARGA LAS CLASES DEL CORE DEL FRAMEWORK
     * @param string $class
     * @return boolean
     * @internal callback para spl_autoload_register
     */
    public function autoloadCore($class)
    {

        if (isset($this->CoreClass[$class]) && file_exists($this->AppDir . DIRECTORY_SEPARATOR . $this->CoreClass[$class]))
        {
            include_once($this->AppDir . DIRECTORY_SEPARATOR . $this->CoreClass[$class]);
            if (class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false))
            {
                return true;
            }
        }
        return false;
    }

}
