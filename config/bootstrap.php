<?php
$config = require __DIR__ . "/app.php";

date_default_timezone_set(
    $config["app"]["timezone"] ?? "America/Santiago"
);

function app_config(?string $key = null, $default = null)
{
    global $config;

    if ($key === null) {
        return $config;
    }

    $value = $config;

    foreach (explode(".", $key) as $segment) {
        if (
            !is_array($value)
            || !array_key_exists($segment, $value)
        ) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function app_base_url(): string
{
    $configured = rtrim(
        (string) app_config("app.base_url", ""),
        "/"
    );

    if ($configured !== "") {
        return $configured;
    }

    $https = (
        !empty($_SERVER["HTTPS"])
        && $_SERVER["HTTPS"] !== "off"
    ) || (
        ($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "") === "https"
    );

    $scheme = $https ? "https" : "http";
    $host = $_SERVER["HTTP_HOST"] ?? "localhost";

    $scriptDir = str_replace(
        "\\",
        "/",
        dirname($_SERVER["SCRIPT_NAME"] ?? "/")
    );

    $scriptDir = preg_replace(
        "#/(usuarios|clientes|incidentes|categorias|config|includes)$#",
        "",
        $scriptDir
    );

    $scriptDir = rtrim($scriptDir, "/");

    return $scheme . "://" . $host . $scriptDir;
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(
            random_bytes(32)
        );
    }

    return $_SESSION["csrf_token"];
}

function csrf_validate(?string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    return is_string($token)
        && isset($_SESSION["csrf_token"])
        && hash_equals(
            $_SESSION["csrf_token"],
            $token
        );
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(
            csrf_token(),
            ENT_QUOTES,
            "UTF-8"
        )
        . '">';
}

function csrf_require_valid(): void
{
    if (!csrf_validate($_POST["csrf_token"] ?? null)) {
        http_response_code(403);

        exit(
            "Solicitud inválida o token CSRF incorrecto. "
            . "Actualice la página e inténtelo nuevamente."
        );
    }
}