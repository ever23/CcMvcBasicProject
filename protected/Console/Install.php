<?php

namespace Cc\Mvc\Console;

use Cc\Mvc\AbstracConsole;
use Cc\Mvc\Json;
use Cc\Mvc;

class Install extends AbstracConsole
{

    protected $vendorDir = NULL;
    protected $protected = 'protected';

    public function index($path = '.')
    {

        $realParh = realpath($path);
        if ($realParh == false)
        {
            $this->OutLn("El directorio no existe ");
            return;
        }
        $this->OutLn("\nIniciando instalacion de CcMvc \n");
        if ($path == '.' && preg_match('/' . preg_quote(DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'bin') . '$/i', $realParh))
        {
            $realParh = realpath($realParh . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR);
            $this->vendorDir = realpath($realParh . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR);
        } else
        {
            $vendor = Mvc::App()->Config()->App['app'] . '..' . DIRECTORY_SEPARATOR . 'vendor';
            if (file_exists($vendor . DIRECTORY_SEPARATOR . "autoload.php"))
                if (($vendor = realpath($vendor) ) !== false)
                {
                    $this->vendorDir = $vendor;
                }
        }

        $this->CreateDirectories($realParh);
        $this->OutLn("\nInstalacion finalizada \n");
    }

    private function CreateDirectories($path)
    {
        $protected = 'protected';
        $public_html = 'public_html';
        $direactories = [
            'Config',
            'Console',
            'controllers',
            'extern',
            'layauts',
            'model',
            'view'
        ];
        $files = ['.htaccess', 'CcMvc', 'CcMvc.bat'];
        $actualPath = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR);
        $this->OutLn($path . DIRECTORY_SEPARATOR . $public_html);
        mkdir($path . DIRECTORY_SEPARATOR . $public_html);
        $dir = dir($path . DIRECTORY_SEPARATOR . $public_html);
        $this->CopyDir($actualPath . DIRECTORY_SEPARATOR . "public_html", realpath($path . DIRECTORY_SEPARATOR . $public_html));
        $this->OutLn($path . DIRECTORY_SEPARATOR . $protected);
        mkdir($path . DIRECTORY_SEPARATOR . $protected);
        foreach ($direactories as $dir)
        {
            mkdir($path . DIRECTORY_SEPARATOR . $protected . DIRECTORY_SEPARATOR . $dir);
            if ($dir != 'Console')
                $this->CopyDir($actualPath . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . $dir, $path . DIRECTORY_SEPARATOR . $protected . DIRECTORY_SEPARATOR . $dir);
        }
        foreach ($files as $f)
        {
            if (file_exists($actualPath . DIRECTORY_SEPARATOR . $f))
            {
                $this->OutLn($path . DIRECTORY_SEPARATOR . $f);
                copy($actualPath . DIRECTORY_SEPARATOR . $f, $path . DIRECTORY_SEPARATOR . $f);
            }

            if (file_exists($actualPath . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . $f))
            {
                $this->OutLn($path . DIRECTORY_SEPARATOR . $protected . DIRECTORY_SEPARATOR . $f);
                copy($actualPath . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . $f, $path . DIRECTORY_SEPARATOR . $protected . DIRECTORY_SEPARATOR . $f);
            }
        }
        $composer = new Json();
        $composer['require'] = ['ccmvc/ccmvc' => \CcMvc::Version];
        $composer->SaveToFile($path . DIRECTORY_SEPARATOR . "composer.json", JSON_PRETTY_PRINT ^ JSON_UNESCAPED_SLASHES);
        file_put_contents($path . DIRECTORY_SEPARATOR . $public_html . DIRECTORY_SEPARATOR . 'index.php', $this->CreateIndexPHP());
        if ($this->vendorDir && !file_exists($path . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php'))
        {
            $this->OutLn("\nInstalando Vendor\n");
            mkdir($path . DIRECTORY_SEPARATOR . 'vendor');
            $this->CopyDir($this->vendorDir, realpath($path . DIRECTORY_SEPARATOR . 'vendor'));
        }
    }

    private function CopyDir($dir, $dir2)
    {
        $d = dir($dir);
        while ($f = $d->read())
        {
            if ($f == '.' || $f == '..' || $f == \Cc\Autoload\FileCore)
                continue;
            if (is_file($dir . DIRECTORY_SEPARATOR . $f) && file_exists($dir . DIRECTORY_SEPARATOR . $f))
            {
                $this->OutLn($dir2 . DIRECTORY_SEPARATOR . $f);
                copy($dir . DIRECTORY_SEPARATOR . $f, $dir2 . DIRECTORY_SEPARATOR . $f);
            } elseif (is_dir($dir . DIRECTORY_SEPARATOR . $f))
            {
                mkdir($dir2 . DIRECTORY_SEPARATOR . $f);
                $this->OutLn($dir2 . DIRECTORY_SEPARATOR . $f);
                $this->CopyDir($dir . DIRECTORY_SEPARATOR . $f, $dir2 . DIRECTORY_SEPARATOR . $f);
            }
        }
    }

    private function CreateIndexPHP()
    {
        $code = "<?php\n"
                . "\$vendor = \"../vendor/autoload.php\";\n\n"
                . "include (\$vendor);\n\n"
                . "if (!class_exists(\"\\\\CcMvc\"))\n"
                . "{\n"
                . "    trigger_error(\"Porfavor instala CcMvc via composer ejecutando 'composer install --prefer-dist'\", E_USER_ERROR);\n"
                . "}\n\n"
                . "\$app = CcMvc::Start(\"../" . $this->protected . "/\", \"Mi Aplicacion Web\");\n\n"
                . "\$app->Run();";
        return $code;
    }

}
