<?php
require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/session_timeout.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: /CyberData/login.php");
    exit;
}

$rolTexto = $_SESSION["rol_nombre"] ?? match ((int) ($_SESSION["rol_id"] ?? 0)) {
    1 => "Administrador",
    2 => "Analista SOC",
    3 => "Cliente",
    default => "Sin rol"
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberData</title>
    <link rel="stylesheet" href="/CyberData/css/estilos.css">
</head>
<body>
