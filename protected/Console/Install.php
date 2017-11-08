<?php

/*
 * Copyright (C) 2017 Enyerber Franco
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
 */

namespace Cc\Mvc\Console;

use Cc\Mvc\AbstracConsole;
use Cc\Mvc\Json;
use Cc\Mvc;

/**
 * Crea una nueva aplicacion CcMvc 
 * @author ENYREBER FRANCO <enyerverfranco@gmail.com> , <enyerverfranco@outlook.com>  
 * 
 */
class Install extends AbstracConsole
{

    protected $vendorDir = NULL;
    protected $protected = 'protected';

    /**
     * Crea una nueva aplicacion CcMvc 
     * @param string $path directorio donde sera creada la aplicacion
     * @return type
     */
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

    /**
     * crea los directorios de la aplicacion 
     * @param string $path directorio
     */
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

    /**
     * copia un directorio a otro
     * @param string $dir
     * @param string $dir2
     */
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

    /**
     * crea el codigo php del archivo index de la aplicacion 
     * @return string
     */
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
