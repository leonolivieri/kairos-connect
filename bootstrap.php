
<?php
/*// ARQUIVO: bootstrap.php

spl_autoload_register(function ($class) {

    $base_dir = __DIR__ . '/src/';

    $class = str_replace('src\\', '', $class);

    $file = $base_dir . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require $file;
    }

});  */

spl_autoload_register(function ($class) {

    echo "Tentando carregar: " . $class . "<br>";

    $base_dir = __DIR__ . '/src/';

    $class = str_replace('src\\', '', $class);

    $file = $base_dir . str_replace('\\', '/', $class) . '.php';

    echo "Arquivo esperado: " . $file . "<br>";

    if (file_exists($file)) {
        echo "Arquivo encontrado!<br>";
        require $file;
    } else {
        echo "Arquivo NÃO encontrado!<br>";
    }

});