<?php
/**
 * Configuración general de CyberData.
 * En producción reemplace los valores SMTP y de base de datos.
 */

return [
    'app' => [
        'name' => 'CyberData',
        'environment' => 'development', // development | production
        'base_url' => '', // Ej.: https://tudominio.com. Vacío = detección automática.
        'timezone' => 'America/Santiago',
    ],

    'database' => [
        'host' => 'localhost',
        'port' => '3306',
        'name' => 'cyberdata_db',
        'user' => 'root',
        'password' => '',
    ],

    'mail' => [
        'enabled' => true,
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls', // tls para puerto 587
        'username' => 'cyberdata.soporte1@gmail.com',
        'password' => 'tomrsslzmgnotqdb',
        'from_email' => 'cyberdata.soporte1@gmail.com',
        'from_name' => 'CyberData',
        'timeout' => 20,
    ],

    'security' => [
        'password_reset_minutes' => 15,
    ],
];
