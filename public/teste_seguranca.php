<?php
require_once __DIR__ . '/../../kairos-connect/bootstrap.php';
use src\Helpers\SecurityHelper;

$teste = "Kairós 2026";
$criptografado = SecurityHelper::encrypt($teste);
$decriptografado = SecurityHelper::decrypt($criptografado);

echo "<h3>Teste de Blindagem</h3>";
echo "Original: " . $teste . "<br>";
echo "Criptografado: " . $criptografado . "<br>";
echo "Decriptografado: " . $decriptografado . "<br>";

echo "<h3>Verificação de Cofre</h3>";
echo "MASTER_KEY existe no ENV? " . (isset($_ENV['Ambiente_Desenvolvimento']['MASTER_KEY']) ? "✅ SIM" : "❌ NÃO");