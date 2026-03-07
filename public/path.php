<?php

echo "<pre>";

echo "DIR ATUAL:\n";
echo __DIR__;

echo "\n\n";

echo "DOCUMENT ROOT:\n";
echo $_SERVER['DOCUMENT_ROOT'];

echo "\n\n";

echo "REALPATH:\n";
echo realpath(__DIR__);

echo "\n\n";

echo "LISTANDO RAIZ:\n";

print_r(scandir(dirname(dirname(__DIR__))));

echo "</pre>";