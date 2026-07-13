<?php
require_once "../config/bootstrap.php";
require_once "../config/session.php";
require_once "../includes/session_timeout.php";
require_once "../config/conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: /CyberData/login.php");
    exit;
}

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {
    header("Location: listar.php");
    exit;
}

$esCliente = (int) ($_SESSION["rol_id"] ?? 0) === 3;
$clienteSesion = (int) ($_SESSION["cliente_id"] ?? 0);
if ($esCliente && $clienteSesion <= 0) {
    http_response_code(403);
    exit("Usuario cliente sin empresa asociada.");
}

$sql = "SELECT
            i.id_incidente,
            i.criticidad,
            i.logs_crudos,
            i.fecha_registro,
            c.razon_social,
            ca.nombre_vector,
            u.email AS usuario_registro
        FROM incidentes i
        INNER JOIN cliente c
            ON c.id_cliente = i.CLIENTE_id_cliente
        INNER JOIN categorias_amenaza ca
            ON ca.id_categoria =
               i.CATEGORIAS_AMENAZA_id_categoria
        INNER JOIN usuario u
            ON u.id_usuario = i.USUARIO_id_usuario
        WHERE i.id_incidente = :id";

if ($esCliente) {
    $sql .= " AND i.CLIENTE_id_cliente = :cliente_id";
}

$sql .= " LIMIT 1";

$stmt = $conexion->prepare($sql);

$paramsIncidente = [":id" => $id];
if ($esCliente) {
    $paramsIncidente[":cliente_id"] = $clienteSesion;
}
$stmt->execute($paramsIncidente);

$incidente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$incidente) {
    header("Location: listar.php");
    exit;
}

$mensaje = "";
$error = "";

$estadoSeleccionado = "";
$comentario = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($esCliente) {
        http_response_code(403);
        exit("Acceso denegado. El rol Cliente es de solo lectura.");
    }

    csrf_require_valid();

    $estadoSeleccionado = $_POST["estado_actual"] ?? "";
    $comentario = trim($_POST["comentario"] ?? "");

    $estadosPermitidos = [
        "ABIERTO",
        "EN_PROCESO",
        "CERRADO"
    ];

    if (
        $estadoSeleccionado === ""
        || $comentario === ""
    ) {
        $error = "Debe seleccionar un estado e ingresar un comentario.";
    } elseif (
        !in_array(
            $estadoSeleccionado,
            $estadosPermitidos,
            true
        )
    ) {
        $error = "El estado seleccionado no es válido.";
    } else {
        $sqlInsert = "INSERT INTO historial_estados
                      (
                          estado_actual,
                          comentario,
                          INCIDENTES_id_incidente
                      )
                      VALUES
                      (
                          :estado_actual,
                          :comentario,
                          :id_incidente
                      )";

        $stmtInsert = $conexion->prepare(
            $sqlInsert
        );

        $stmtInsert->execute([
            ":estado_actual" => $estadoSeleccionado,
            ":comentario" => $comentario,
            ":id_incidente" => $id
        ]);

        $mensaje = "Estado actualizado correctamente.";

        $estadoSeleccionado = "";
        $comentario = "";
    }
}

$sqlHistorial = "SELECT
                    id_historial,
                    estado_actual,
                    comentario,
                    fecha_cambio
                 FROM historial_estados
                 WHERE INCIDENTES_id_incidente = :id
                 ORDER BY
                    fecha_cambio DESC,
                    id_historial DESC";

$stmtHistorial = $conexion->prepare(
    $sqlHistorial
);

$stmtHistorial->execute([
    ":id" => $id
]);

$historial = $stmtHistorial->fetchAll(
    PDO::FETCH_ASSOC
);

require_once "../includes/header.php";
?>

<div class="layout">
    <?php require_once "../includes/menu.php"; ?>

    <main class="contenido">

        <section class="panel">

            <h1>
                Historial del Incidente
                #<?php echo (int) $incidente["id_incidente"]; ?>
            </h1>

            <?php if ($mensaje !== ""): ?>
                <p class="mensaje-ok">
                    <?php echo htmlspecialchars(
                        $mensaje,
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>
                </p>
            <?php endif; ?>

            <?php if ($error !== ""): ?>
                <p class="mensaje-error">
                    <?php echo htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>
                </p>
            <?php endif; ?>

            <div class="detalle-grid">

                <div>
                    <strong>Cliente:</strong>

                    <?php echo htmlspecialchars(
                        $incidente["razon_social"],
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>
                </div>

                <div>
                    <strong>Vector:</strong>

                    <?php echo htmlspecialchars(
                        $incidente["nombre_vector"],
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>
                </div>

                <div>
                    <strong>Criticidad:</strong>

                    <?php echo htmlspecialchars(
                        $incidente["criticidad"],
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>
                </div>

                <div>
                    <strong>Usuario registro:</strong>

                    <?php echo htmlspecialchars(
                        $incidente["usuario_registro"],
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>
                </div>

                <div>
                    <strong>Fecha registro:</strong>

                    <?php echo htmlspecialchars(
                        $incidente["fecha_registro"],
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>
                </div>

            </div>
        </section>

        <section class="panel">

            <h2>Log Crudo Original</h2>

            <pre class="log-crudo"><?php echo htmlspecialchars(
                $incidente["logs_crudos"],
                ENT_QUOTES,
                "UTF-8"
            ); ?></pre>

        </section>

        <?php if (!$esCliente): ?>
        <section class="panel">

            <h2>Actualizar Estado</h2>

            <form
                method="POST"
                class="formulario"
            >

                <?= csrf_input(); ?>

                <label for="estado_actual">
                    Nuevo estado
                </label>

                <select
                    id="estado_actual"
                    name="estado_actual"
                    required
                >
                    <option value="">
                        Seleccione estado
                    </option>

                    <option
                        value="ABIERTO"
                        <?php echo (
                            $estadoSeleccionado === "ABIERTO"
                        ) ? "selected" : ""; ?>
                    >
                        ABIERTO
                    </option>

                    <option
                        value="EN_PROCESO"
                        <?php echo (
                            $estadoSeleccionado === "EN_PROCESO"
                        ) ? "selected" : ""; ?>
                    >
                        EN PROCESO
                    </option>

                    <option
                        value="CERRADO"
                        <?php echo (
                            $estadoSeleccionado === "CERRADO"
                        ) ? "selected" : ""; ?>
                    >
                        CERRADO
                    </option>
                </select>

                <label for="comentario">
                    Comentario de auditoría
                </label>

                <textarea
                    id="comentario"
                    name="comentario"
                    rows="4"
                    required
                ><?php echo htmlspecialchars(
                    $comentario,
                    ENT_QUOTES,
                    "UTF-8"
                ); ?></textarea>

                <div class="acciones-form">

                    <button
                        type="submit"
                        class="btn"
                    >
                        Actualizar Estado
                    </button>

                    <a
                        href="listar.php"
                        class="btn-secundario"
                    >
                        Volver
                    </a>

                </div>

            </form>
        </section>
        <?php else: ?>
        <section class="panel">
            <h2>Modo solo lectura</h2>
            <p>Como usuario Cliente puedes revisar los datos y el historial del incidente, pero no modificar su estado.</p>
        </section>
        <?php endif; ?>

        <section class="panel">

            <h2>Historial de Estados</h2>

            <table class="tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Estado</th>
                        <th>Comentario</th>
                        <th>Fecha cambio</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($historial) === 0): ?>
                        <tr>
                            <td colspan="4">
                                No existe historial para este incidente.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($historial as $h): ?>
                        <tr>
                            <td>
                                <?php echo (int) $h["id_historial"]; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $h["estado_actual"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $h["comentario"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $h["fecha_cambio"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </section>

    </main>
</div>

<?php require_once "../includes/footer.php"; ?>