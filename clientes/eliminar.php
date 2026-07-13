<?php
require_once "../config/bootstrap.php";
require_once "../config/session.php";
require_once "../includes/session_timeout.php";
require_once "../config/conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit;
}

if ($_SESSION["rol_id"] != 1) {
    header("Location: ../dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Método no permitido.");
}

if (!csrf_validate($_POST["csrf_token"] ?? null)) {
    http_response_code(403);
    exit("Solicitud inválida o token CSRF incorrecto. Actualice la página e inténtelo nuevamente.");
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(400);
    exit("ID de cliente inválido.");
}

$sql = "SELECT estado_cliente FROM cliente WHERE id_cliente = :id";
$stmt = $conexion->prepare($sql);
$stmt->execute([":id" => $id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    http_response_code(404);
    exit("Cliente no encontrado.");
}

$nuevoEstado = ($cliente["estado_cliente"] == "ACTIVO") ? "INACTIVO" : "ACTIVO";

$sqlUpdate = "UPDATE cliente
              SET estado_cliente = :estado
              WHERE id_cliente = :id";

$stmtUpdate = $conexion->prepare($sqlUpdate);
$stmtUpdate->execute([
    ":estado" => $nuevoEstado,
    ":id" => $id
]);

header("Location: listar.php");
exit;
