<?php
require_once "../config/bootstrap.php";
require_once "../config/session.php";
require_once "../includes/session_timeout.php";
require_once "../config/conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: /CyberData/login.php");
    exit;
}

if ((int) ($_SESSION["rol_id"] ?? 0) === 3) {
    http_response_code(403);
    exit("Acceso denegado. El rol Cliente es de solo lectura.");
}

$sqlClientes = "SELECT id_cliente, razon_social
                FROM cliente
                WHERE estado_cliente = 'ACTIVO'
                ORDER BY razon_social ASC";

$stmtClientes = $conexion->query($sqlClientes);
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

$sqlCategorias = "SELECT id_categoria, nombre_vector
                  FROM categorias_amenaza
                  ORDER BY nombre_vector ASC";

$stmtCategorias = $conexion->query($sqlCategorias);
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

$mensaje = "";
$error = "";

$idCliente = "";
$idCategoria = "";
$criticidad = "";
$logsCrudos = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    csrf_require_valid();

    $idCliente = $_POST["id_cliente"] ?? "";
    $idCategoria = $_POST["id_categoria"] ?? "";
    $criticidad = $_POST["criticidad"] ?? "";
    $logsCrudos = trim($_POST["logs_crudos"] ?? "");

    $criticidadesPermitidas = [
        "BAJA",
        "MEDIA",
        "ALTA",
        "CRITICA"
    ];

    if (
        $idCliente === ""
        || $idCategoria === ""
        || $criticidad === ""
        || $logsCrudos === ""
    ) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!ctype_digit((string) $idCliente)) {
        $error = "El cliente seleccionado no es válido.";
    } elseif (!ctype_digit((string) $idCategoria)) {
        $error = "La categoría seleccionada no es válida.";
    } elseif (
        !in_array(
            $criticidad,
            $criticidadesPermitidas,
            true
        )
    ) {
        $error = "La criticidad seleccionada no es válida.";
    }

    if ($error === "") {
        $sqlClienteExiste = "SELECT COUNT(*)
                             FROM cliente
                             WHERE id_cliente = :id_cliente
                               AND estado_cliente = 'ACTIVO'";

        $stmtClienteExiste = $conexion->prepare(
            $sqlClienteExiste
        );

        $stmtClienteExiste->execute([
            ":id_cliente" => (int) $idCliente
        ]);

        if (
            (int) $stmtClienteExiste->fetchColumn() === 0
        ) {
            $error = "El cliente seleccionado no existe o se encuentra inactivo.";
        }
    }

    if ($error === "") {
        $sqlCategoriaExiste = "SELECT COUNT(*)
                               FROM categorias_amenaza
                               WHERE id_categoria = :id_categoria";

        $stmtCategoriaExiste = $conexion->prepare(
            $sqlCategoriaExiste
        );

        $stmtCategoriaExiste->execute([
            ":id_categoria" => (int) $idCategoria
        ]);

        if (
            (int) $stmtCategoriaExiste->fetchColumn() === 0
        ) {
            $error = "La categoría seleccionada no existe.";
        }
    }

    if ($error === "") {
        try {
            $conexion->beginTransaction();

            $sql = "INSERT INTO incidentes
                    (
                        criticidad,
                        logs_crudos,
                        CLIENTE_id_cliente,
                        CATEGORIAS_AMENAZA_id_categoria,
                        USUARIO_id_usuario
                    )
                    VALUES
                    (
                        :criticidad,
                        :logs_crudos,
                        :id_cliente,
                        :id_categoria,
                        :id_usuario
                    )";

            $stmt = $conexion->prepare($sql);

            $stmt->execute([
                ":criticidad" => $criticidad,
                ":logs_crudos" => $logsCrudos,
                ":id_cliente" => (int) $idCliente,
                ":id_categoria" => (int) $idCategoria,
                ":id_usuario" => (int) $_SESSION["id_usuario"]
            ]);

            $idIncidente = (int) $conexion->lastInsertId();

            $sqlHistorial = "INSERT INTO historial_estados
                             (
                                 estado_actual,
                                 comentario,
                                 INCIDENTES_id_incidente
                             )
                             VALUES
                             (
                                 'ABIERTO',
                                 'Incidente registrado por ingesta de logs.',
                                 :id_incidente
                             )";

            $stmtHistorial = $conexion->prepare(
                $sqlHistorial
            );

            $stmtHistorial->execute([
                ":id_incidente" => $idIncidente
            ]);

            $conexion->commit();

            $mensaje = "Ingesta de logs procesada correctamente.";

            $idCliente = "";
            $idCategoria = "";
            $criticidad = "";
            $logsCrudos = "";

        } catch (Throwable $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            $error = "Error al procesar la ingesta.";
        }
    }
}

require_once "../includes/header.php";
?>

<div class="layout">
    <?php require_once "../includes/menu.php"; ?>

    <main class="contenido">

        <header class="barra-superior">
            <div class="usuario-barra">
                Usuario:

                <strong>
                    <?php echo htmlspecialchars(
                        $_SESSION["email"] ?? "Sin email",
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>
                </strong>
            </div>

            <div class="permisos-barra">
                Permisos actuales:

                <strong>
                    <?php echo (
                        (int) $_SESSION["rol_id"] === 1
                    )
                        ? "Administrador"
                        : "Analista SOC"; ?>
                </strong>
            </div>
        </header>

        <section class="panel">

            <h1>
                FORMULARIO 2: INGESTA ANALÍTICA DE INCIDENTES
                (BIG DATA)
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

            <form
                method="POST"
                class="formulario-ingesta"
            >

                <?= csrf_input(); ?>

                <label for="id_cliente">
                    Seleccione Cliente Afectado
                    (Multi-Inquilino):
                </label>

                <select
                    id="id_cliente"
                    name="id_cliente"
                    required
                >
                    <option value="">
                        Seleccione cliente
                    </option>

                    <?php foreach ($clientes as $cliente): ?>
                        <option
                            value="<?php echo (int) $cliente["id_cliente"]; ?>"
                            <?php echo (
                                (string) $idCliente
                                === (string) $cliente["id_cliente"]
                            ) ? "selected" : ""; ?>
                        >
                            <?php echo htmlspecialchars(
                                $cliente["razon_social"],
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="id_categoria">
                    Vector Técnico Detectado:
                </label>

                <select
                    id="id_categoria"
                    name="id_categoria"
                    required
                >
                    <option value="">
                        Seleccione vector
                    </option>

                    <?php foreach ($categorias as $categoria): ?>
                        <option
                            value="<?php echo (int) $categoria["id_categoria"]; ?>"
                            <?php echo (
                                (string) $idCategoria
                                === (string) $categoria["id_categoria"]
                            ) ? "selected" : ""; ?>
                        >
                            <?php echo htmlspecialchars(
                                $categoria["nombre_vector"],
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>
                    Nivel de Criticidad Evaluado:
                </label>

                <div class="radio-group">

                    <label>
                        <input
                            type="radio"
                            name="criticidad"
                            value="BAJA"
                            required
                            <?php echo (
                                $criticidad === "BAJA"
                            ) ? "checked" : ""; ?>
                        >
                        Baja
                    </label>

                    <label>
                        <input
                            type="radio"
                            name="criticidad"
                            value="MEDIA"
                            <?php echo (
                                $criticidad === "MEDIA"
                            ) ? "checked" : ""; ?>
                        >
                        Media
                    </label>

                    <label>
                        <input
                            type="radio"
                            name="criticidad"
                            value="ALTA"
                            <?php echo (
                                $criticidad === "ALTA"
                            ) ? "checked" : ""; ?>
                        >
                        Alta
                    </label>

                    <label>
                        <input
                            type="radio"
                            name="criticidad"
                            value="CRITICA"
                            <?php echo (
                                $criticidad === "CRITICA"
                            ) ? "checked" : ""; ?>
                        >
                        Crítica
                    </label>

                </div>

                <label for="logs_crudos">
                    Volcado Crudo Masivo de Logs
                    (Soporta archivos LONGTEXT):
                </label>

                <textarea
                    id="logs_crudos"
                    name="logs_crudos"
                    rows="8"
                    required
                    placeholder="2026-06-17T20:30:12Z core-fw01 ALERT SQLi DETECTED GET /index.php?id=1 UNION SELECT NULL,password_hash..."
                ><?php echo htmlspecialchars(
                    $logsCrudos,
                    ENT_QUOTES,
                    "UTF-8"
                ); ?></textarea>

                <div class="acciones-form">

                    <button
                        type="reset"
                        class="btn-secundario"
                    >
                        LIMPIAR CAMPOS
                    </button>

                    <button
                        type="submit"
                        class="btn"
                    >
                        PROCESAR INGESTA LOGS
                    </button>

                </div>

            </form>
        </section>

    </main>
</div>

<?php require_once "../includes/footer.php"; ?>