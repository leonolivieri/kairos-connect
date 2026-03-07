<?php

$path = realpath(__DIR__ . '/../../kairos-connect');

if ($path) {
    echo "PASTA EXISTE:<br>";
    echo $path;
} else {
    echo "PASTA NÃO EXISTE";
}