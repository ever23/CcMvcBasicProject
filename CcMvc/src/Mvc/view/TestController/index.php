<!--<script type="text/javascript">
    var popUpWin = false;
    function popUpWindow(URLStr, w, h)
    {
        /*if (popUpWin)
         {
         if (!popUpWin.closed)
         popUpWin.close();
         }
         popUpWin = */open(URLStr, null, 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,copyhistory=yes,');
    }
<?php
$json = new \Cc\Json(false);
$json->CreateJson($controladores);
echo 'var controladores=' . $json . ';'
?>
    function lanzar(e)
    {
        //  e.preventDefault();
        for (paquete in controladores)
        {
            for (controlador in controladores[paquete])
            {
                var c = controlador.replace('<?php echo $config['Controllers']['Prefijo'] ?>', '');
                // console.log(controlador)
                for (metodo in controladores[paquete][controlador])
                {

                    console.log(c + '/' + metodo);
                    popUpWindow(c + '/' + metodo);
                }
            }
        }
    }

</script>-->
<h1>CONTROLADORES EXISTENTE <br><!--<a  onclick="lanzar()">lanzar todos los controladores</a> --></h1>
<ul>
    <?php
    foreach ($controladores as $paquete => $controladors)
    {
        ?>
        <li ><h1><?php echo $paquete ?></h1>
            <?php
            foreach ($controladors as $controlador => $metodos)
            {

                echo " <h2>" . $controlador . "</h2>";
                ?>
                <ul>

                    <?php
                    foreach ($metodos as $metodo => $parametros)
                    {
                        $b = NULL;
                        $fatal = false;
                        foreach ($parametros as $i => $p)
                        {
                            //var_dump($p);
                            $b .= $p['type'] . " \$" . $p['name'] . (!empty($p['default']) ? '=' . $p['default'] : '') . ",";
                            if ($p['type'] == '(undefined_class)')
                            {
                                $fatal = true;
                            }
                        }

                        $c = substr($controlador, strlen($config['Controllers']['Prefijo']));
                        $link = Cc\Mvc\Router::Href(['paquete' => $paquete, 'controller' => $c, 'method' => $metodo]);
                        if ($fatal)
                        {
                            echo "<li style='background-color: #CC0000;'><a href='" . $link . "'>" . $metodo . "(";
                        } else
                        {
                            echo "<li ><a href='" . $link . "'>" . $metodo . "(";
                        }
                        echo $b;
                        echo ")</a></li>";
                    }
                    ?>

                </ul>
                <?php
            }
            echo '</li>';
        }
        ?></ul>
