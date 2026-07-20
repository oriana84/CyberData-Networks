<?php
require_once __DIR__ . '/bootstrap.php';

$host = (string) app_config('database.host');
$port = (string) app_config('database.port', '3306');
$db = (string) app_config('database.name');
$user = (string) app_config('database.user');
$pass = (string) app_config('database.password');

try {
    $conexion = new PDO(
        "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Error de conexión CyberData: ' . $e->getMessage());
    exit('No fue posible conectar con la base de datos. Revise la configuración del sistema.');
}
