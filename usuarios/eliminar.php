<?php
require_once "../config/bootstrap.php";
require_once "../config/session.php";
require_once "../includes/session_timeout.php";
require_once "../config/conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: /CyberData/login.php");
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

csrf_require_valid();

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: listar.php?error=usuario_no_encontrado");
    exit;
}

/* Evitar que el administrador cambie su propio estado */
if ((int) $id === (int) $_SESSION["id_usuario"]) {
    header("Location: listar.php?error=no_auto_inactivar");
    exit;
}

$sql = "SELECT id_usuario, estado
        FROM usuario
        WHERE id_usuario = :id
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->execute([
    ":id" => $id
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header("Location: listar.php?error=usuario_no_encontrado");
    exit;
}

$nuevoEstado = $usuario["estado"] === "ACTIVO"
    ? "INACTIVO"
    : "ACTIVO";

$sqlUpdate = "UPDATE usuario
              SET estado = :estado
              WHERE id_usuario = :id";

$stmtUpdate = $conexion->prepare($sqlUpdate);
$stmtUpdate->execute([
    ":estado" => $nuevoEstado,
    ":id" => $id
]);

header("Location: listar.php?mensaje=estado_actualizado");
exit;