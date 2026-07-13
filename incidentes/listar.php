<?php
require_once "../config/bootstrap.php";
require_once "../includes/header.php";
require_once "../config/conexion.php";

$esCliente = (int) ($_SESSION["rol_id"] ?? 0) === 3;
$clienteSesion = (int) ($_SESSION["cliente_id"] ?? 0);
if ($esCliente && $clienteSesion <= 0) {
    http_response_code(403);
    exit("Usuario cliente sin empresa asociada.");
}

$sqlClientes = "SELECT id_cliente, razon_social
                FROM cliente
                ORDER BY razon_social ASC";

$stmtClientes = $conexion->query($sqlClientes);
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

$sqlCategorias = "SELECT id_categoria, nombre_vector
                  FROM categorias_amenaza
                  ORDER BY nombre_vector ASC";

$stmtCategorias = $conexion->query(
    $sqlCategorias
);

$categorias = $stmtCategorias->fetchAll(
    PDO::FETCH_ASSOC
);

$where = [];
$params = [];

$clienteFiltro = $esCliente ? (string) $clienteSesion : ($_GET["cliente"] ?? "");
$categoriaFiltro = $_GET["categoria"] ?? "";
$criticidadFiltro = $_GET["criticidad"] ?? "";
$estadoFiltro = $_GET["estado"] ?? "";
$fechaDesde = $_GET["fecha_desde"] ?? "";
$fechaHasta = $_GET["fecha_hasta"] ?? "";

$criticidadesPermitidas = [
    "BAJA",
    "MEDIA",
    "ALTA",
    "CRITICA"
];

$estadosPermitidos = [
    "ABIERTO",
    "EN_PROCESO",
    "CERRADO"
];

if ($esCliente) {
    $where[] = "i.CLIENTE_id_cliente = :cliente_sesion";
    $params[":cliente_sesion"] = $clienteSesion;
} elseif (
    $clienteFiltro !== ""
    && ctype_digit((string) $clienteFiltro)
) {
    $where[] = "i.CLIENTE_id_cliente = :cliente";
    $params[":cliente"] = (int) $clienteFiltro;
}

if (
    $categoriaFiltro !== ""
    && ctype_digit((string) $categoriaFiltro)
) {
    $where[] = "
        i.CATEGORIAS_AMENAZA_id_categoria = :categoria
    ";

    $params[":categoria"] = (int) $categoriaFiltro;
}

if (
    in_array(
        $criticidadFiltro,
        $criticidadesPermitidas,
        true
    )
) {
    $where[] = "i.criticidad = :criticidad";
    $params[":criticidad"] = $criticidadFiltro;
}

if (
    in_array(
        $estadoFiltro,
        $estadosPermitidos,
        true
    )
) {
    $where[] = "h.estado_actual = :estado";
    $params[":estado"] = $estadoFiltro;
}

if (
    $fechaDesde !== ""
    && validarFechaIncidente($fechaDesde)
) {
    $where[] = "DATE(i.fecha_registro) >= :fecha_desde";
    $params[":fecha_desde"] = $fechaDesde;
}

if (
    $fechaHasta !== ""
    && validarFechaIncidente($fechaHasta)
) {
    $where[] = "DATE(i.fecha_registro) <= :fecha_hasta";
    $params[":fecha_hasta"] = $fechaHasta;
}

$sql = "SELECT
            i.id_incidente,
            i.criticidad,
            i.fecha_registro,
            c.razon_social,
            ca.nombre_vector,
            u.email AS usuario_registro,
            (
                SELECT he.estado_actual
                FROM historial_estados he
                WHERE he.INCIDENTES_id_incidente =
                      i.id_incidente
                ORDER BY
                    he.fecha_cambio DESC,
                    he.id_historial DESC
                LIMIT 1
            ) AS estado_actual
        FROM incidentes i
        INNER JOIN cliente c
            ON c.id_cliente = i.CLIENTE_id_cliente
        INNER JOIN categorias_amenaza ca
            ON ca.id_categoria =
               i.CATEGORIAS_AMENAZA_id_categoria
        INNER JOIN usuario u
            ON u.id_usuario = i.USUARIO_id_usuario
        LEFT JOIN historial_estados h
            ON h.INCIDENTES_id_incidente =
               i.id_incidente";

if (!empty($where)) {
    $sql .= " WHERE " . implode(
        " AND ",
        $where
    );
}

$sql .= " GROUP BY
              i.id_incidente,
              i.criticidad,
              i.fecha_registro,
              c.razon_social,
              ca.nombre_vector,
              u.email
          ORDER BY i.fecha_registro DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);

$incidentes = $stmt->fetchAll(
    PDO::FETCH_ASSOC
);

function validarFechaIncidente(string $fecha): bool
{
    $objetoFecha = DateTime::createFromFormat(
        "Y-m-d",
        $fecha
    );

    return $objetoFecha !== false
        && $objetoFecha->format("Y-m-d") === $fecha;
}
?>

<div class="layout">
    <?php require_once "../includes/menu.php"; ?>

    <main class="contenido">

        <section class="panel">

            <h1>
                Filtrar Historial de Incidentes
            </h1>

            <form
                method="GET"
                class="formulario-filtros"
            >

                <?php if ($esCliente): ?>
                    <div class="campo-solo-lectura">
                        <strong>Cliente:</strong>
                        <?= htmlspecialchars($_SESSION["cliente_nombre"] ?? "Cliente asociado", ENT_QUOTES, "UTF-8") ?>
                    </div>
                <?php else: ?>
                    <select name="cliente">
                        <option value="">Todos los clientes</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?php echo (int) $cliente["id_cliente"]; ?>" <?php echo ((string) $clienteFiltro === (string) $cliente["id_cliente"]) ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($cliente["razon_social"], ENT_QUOTES, "UTF-8"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <select name="categoria">
                    <option value="">
                        Todos los vectores
                    </option>

                    <?php foreach ($categorias as $categoria): ?>
                        <option
                            value="<?php echo (int) $categoria["id_categoria"]; ?>"
                            <?php echo (
                                (string) $categoriaFiltro
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

                <select name="criticidad">

                    <option value="">
                        Todas las criticidades
                    </option>

                    <option
                        value="BAJA"
                        <?php echo (
                            $criticidadFiltro === "BAJA"
                        ) ? "selected" : ""; ?>
                    >
                        BAJA
                    </option>

                    <option
                        value="MEDIA"
                        <?php echo (
                            $criticidadFiltro === "MEDIA"
                        ) ? "selected" : ""; ?>
                    >
                        MEDIA
                    </option>

                    <option
                        value="ALTA"
                        <?php echo (
                            $criticidadFiltro === "ALTA"
                        ) ? "selected" : ""; ?>
                    >
                        ALTA
                    </option>

                    <option
                        value="CRITICA"
                        <?php echo (
                            $criticidadFiltro === "CRITICA"
                        ) ? "selected" : ""; ?>
                    >
                        CRITICA
                    </option>

                </select>

                <select name="estado">

                    <option value="">
                        Todos los estados
                    </option>

                    <option
                        value="ABIERTO"
                        <?php echo (
                            $estadoFiltro === "ABIERTO"
                        ) ? "selected" : ""; ?>
                    >
                        ABIERTO
                    </option>

                    <option
                        value="EN_PROCESO"
                        <?php echo (
                            $estadoFiltro === "EN_PROCESO"
                        ) ? "selected" : ""; ?>
                    >
                        EN PROCESO
                    </option>

                    <option
                        value="CERRADO"
                        <?php echo (
                            $estadoFiltro === "CERRADO"
                        ) ? "selected" : ""; ?>
                    >
                        CERRADO
                    </option>

                </select>

                <input
                    type="date"
                    name="fecha_desde"
                    value="<?php echo htmlspecialchars(
                        $fechaDesde,
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>"
                >

                <input
                    type="date"
                    name="fecha_hasta"
                    value="<?php echo htmlspecialchars(
                        $fechaHasta,
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>"
                >

                <div class="acciones-form">

                    <button
                        type="submit"
                        class="btn"
                    >
                        Filtrar
                    </button>

                    <a
                        href="listar.php"
                        class="btn-secundario"
                    >
                        Limpiar
                    </a>

                </div>

            </form>
        </section>

        <section class="panel">

            <h2>Resultados</h2>

            <table class="tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Vector</th>
                        <th>Criticidad</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Detalle</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($incidentes) === 0): ?>
                        <tr>
                            <td colspan="8">
                                No existen incidentes para los filtros seleccionados.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($incidentes as $i): ?>
                        <tr>
                            <td>
                                <?php echo (int) $i["id_incidente"]; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $i["razon_social"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $i["nombre_vector"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $i["criticidad"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $i["estado_actual"]
                                        ?? "SIN ESTADO",
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $i["fecha_registro"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $i["usuario_registro"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>
                            </td>

                            <td>
                                <a href="historial.php?id=<?php
                                    echo (int) $i["id_incidente"];
                                ?>">
                                    Ver historial
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </section>

    </main>
</div>

<?php require_once "../includes/footer.php"; ?>