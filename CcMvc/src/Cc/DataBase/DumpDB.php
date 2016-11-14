<?php

namespace Cc;

/**
 * realiza un dump de la base de datos
 *
 * @author Enyerber Franco
 * @package Cc
 * @subpackage DataBase
 * @deprecated
 */
trait DumpDB
{

    protected $user;
    protected $pass;
    protected $db = NULL;
    protected $typeDB = 'mysql';

    public function ExportDB($mysql = NULL, $BD = NULL)
    {
        $bd = is_null($BD) ? $this->db : $BD;

        if(!empty($_SERVER['MYSQL_HOME']))
        {
            $dirmysql = $_SERVER['SystemRoot'] . "\\.." . $_SERVER['MYSQL_HOME'] . "";
        } else
        {
            $dirmysql = $_SERVER['DOCUMENT_ROOT'] . "\\..\\bin";
        }

        $dump = "/" . $this->typeDB . "dump -u " . $this->user . " -p" . $this->pass . " --single-transaction=TRUE --complete-insert --create-options --hex-blob --skip-add-locks  --databases " . $bd . " --routines --events " . $bd;
        // mysqldump -u root -pever2310 --single-transaction=TRUE --complete-insert --create-options --hex-blob --skip-add-locks  --databases bd_teg --routines --events bd_teg
        $output = shell_exec(realpath($mysql) . $dump);


        if(is_null($output))
        {
            $output = shell_exec(realpath($dirmysql) . $dump);
            if(is_null($output))
            {
                new CcException("ERROR AL EXPORTAR LA BASE DE DATOS ");
            }
        }
        return $output;
    }

    public function ExportDBGzip($mysql = '', $BD = NULL)
    {
        $conten = $this->ExportDB($mysql, $BD);
        if(is_null($conten))
        {
            return $conten;
        }
        return gzencode($conten, 9, FORCE_GZIP);
    }

    public function ImportDBFile($file)
    {
        $cont = file_get_contents($file);
        $sql = str_replace("\n", "\r\n", $cont);
        ;

        return $this->ImportDB($sql);
    }

    public function ImportDBFileGzip($file)
    {
        $f = gzopen($file, "r9");
        $sql = ''; //gzread($f,gzsize($file));
        while(!gzeof($f))
        {
            $sql.=gzgets($f) . "\n";
        }
        gzclose($f);
        //echo $sql;
        //$sql= str_replace("\n","\r\n",$sql);;
        return $this->ImportDB($sql);
    }

    protected function VerificaImportDB()//redefinir
    {
        if($this->error)
        {

            $e = new Exception("ERROR AL IMPORTAR LA BASE DE DATOS ");
            $e->AddMsjMysql($this->error, $this->errno);
            return FALSE;
        } else
        {

            return true;
        }
    }

    public function ImportDB($sql)
    {
        if($sql == '')
            return false;

        $del = false;
        $delsql = '';
        //$sql=str_replace("","",$sql);
        //echo '<pre>'; print_r(explode(";\n",$sql) );
        foreach(explode(";\n", $sql) as $i => $q)
        {
            $query = $q;
            if(substr(trim($query), 0, 9) == 'DELIMITER')
            {
                if(!$del)
                {
                    $del = true;
                    $query = '';
                } else
                {
                    $del = false;
                    $query = substr(trim($delsql), 0, strlen(trim($delsql)) - 1);
                    $delsql = '';
                }
            }
            if(trim($query) == '' || trim($query) == ';')
                continue;
            if($del)
            {
                $delsql.=$query . (substr($query, strlen($query) - 1, 1) == ';' ? '' : ';');
                $query = '';
            } else
            {
                $this->query($query);
                if($this->error)
                {
                    new Exception("error " . $query . ";" . $i . ' ' . $this->error);
                    break;
                }
            }
        }
        return $this->VerificaImportDB();
    }

}
