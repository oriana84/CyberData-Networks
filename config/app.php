<?php
/**
 * Configuración central de CyberData.
 *
 * Producción: complete únicamente config/credentials.php antes de subir.
 * Desarrollo: localhost/XAMPP se detecta automáticamente.
 */

$hostActual = strtolower((string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
$esLocal = $hostActual === 'localhost'
    || $hostActual === '127.0.0.1'
    || str_starts_with($hostActual, 'localhost:')
    || str_starts_with($hostActual, '127.0.0.1:');

$credenciales = require __DIR__ . '/credentials.php';

return [
    'app' => [
        'name' => 'CyberData',
        'environment' => $esLocal ? 'development' : 'production',
        'base_url' => $esLocal
            ? 'http://' . $hostActual . '/CyberData'
            : 'https://cyberdata2026.infinityfreeapp.com',
        'timezone' => 'America/Santiago',
    ],

    'database' => $esLocal
        ? [
            'host' => 'localhost',
            'port' => '3306',
            'name' => 'cyberdata_db',
            'user' => 'root',
            'password' => '',
        ]
        : [
            'host' => 'sql211.infinityfree.com',
            'port' => '3306',
            'name' => 'if0_42394554_cyberdata',
            'user' => 'if0_42394554',
            'password' => (string) ($credenciales['database_password'] ?? ''),
        ],

    'mail' => [
        'enabled' => !empty($credenciales['mail_password']),
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'cyberdata.soporte1@gmail.com',
        'password' => (string) ($credenciales['mail_password'] ?? ''),
        'from_email' => 'cyberdata.soporte1@gmail.com',
        'from_name' => 'CyberData',
        'timeout' => 20,
    ],

    'security' => [
        'password_reset_minutes' => 15,
        'session_timeout_seconds' => 900,
    ],
];
