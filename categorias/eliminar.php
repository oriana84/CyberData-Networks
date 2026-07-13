<?php
require_once "../config/bootstrap.php";
require_once "../config/session.php";
require_once "../includes/session_timeout.php";
require_once "../config/conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: /CyberData/login.php");
    exit;
}

if ((int) ($_SESSION["rol_id"] ?? 0) !== 1) {
    header("Location: ../dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Método no permitido.");
}

csrf_require_valid();

$id = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {
    header("Location: listar.php?error=id_invalido");
    exit;
}

$sqlCategoria = "SELECT
                    id_categoria,
                    nombre_vector
                 FROM categorias_amenaza
                 WHERE id_categoria = :id
                 LIMIT 1";

$stmtCategoria = $conexion->prepare(
    $sqlCategoria
);

$stmtCategoria->execute([
    ":id" => $id
]);

$categoria = $stmtCategoria->fetch(
    PDO::FETCH_ASSOC
);

if (!$categoria) {
    header(
        "Location: listar.php?error=categoria_no_encontrada"
    );
    exit;
}

$sqlUso = "SELECT COUNT(*)
           FROM incidentes
           WHERE CATEGORIAS_AMENAZA_id_categoria = :id";

$stmtUso = $conexion->prepare($sqlUso);

$stmtUso->execute([
    ":id" => $id
]);

$totalIncidentes = (int) $stmtUso->fetchColumn();

if ($totalIncidentes > 0) {
    header("Location: listar.php?error=categoria_en_uso");
    exit;
}

$sqlDelete = "DELETE FROM categorias_amenaza
              WHERE id_categoria = :id";

$stmtDelete = $conexion->prepare($sqlDelete);

$stmtDelete->execute([
    ":id" => $id
]);

header("Location: listar.php?mensaje=eliminada");
exit;