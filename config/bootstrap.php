<?php
$config = require __DIR__ . '/app.php';

$environment = (string) ($config['app']['environment'] ?? 'production');
$isProduction = $environment === 'production';

ini_set('display_errors', $isProduction ? '0' : '1');
ini_set('display_startup_errors', $isProduction ? '0' : '1');
error_reporting(E_ALL);

date_default_timezone_set($config['app']['timezone'] ?? 'America/Santiago');

if (!headers_sent() && PHP_SAPI !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

function app_config(?string $key = null, $default = null)
{
    global $config;
    if ($key === null) {
        return $config;
    }

    $value = $config;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

function app_base_url(): string
{
    return rtrim((string) app_config('app.base_url', ''), '/');
}

function app_url(string $path = ''): string
{
    return app_base_url() . '/' . ltrim($path, '/');
}

function redirect_to(string $path, int $status = 302): never
{
    header('Location: ' . app_url($path), true, $status);
    exit;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        require_once __DIR__ . '/session.php';
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_validate(?string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        require_once __DIR__ . '/session.php';
    }
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_require_valid(): void
{
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Solicitud inválida o token CSRF incorrecto. Actualice la página e inténtelo nuevamente.');
    }
}
