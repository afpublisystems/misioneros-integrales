<?php
require_once 'app/config/db.php';

// Temporal: mostrar error real para diagnóstico
ini_set('display_errors', 1);
error_reporting(E_ALL);

$db = Database::getConnection();
echo $db ? '✅ Conexión exitosa' : '❌ Falló';