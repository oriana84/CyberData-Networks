<?php
if (session_status() === PHP_SESSION_NONE) {
    $forwardedHttps = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    $directHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    session_name('CYBERDATASESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $directHttps || $forwardedHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
