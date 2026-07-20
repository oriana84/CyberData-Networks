<?php
require_once "includes/header.php";
require_once "config/conexion.php";

$esCliente = (int) ($_SESSION["rol_id"] ?? 0) === 3;
$clienteId = (int) ($_SESSION["cliente_id"] ?? 0);
if ($esCliente && $clienteId <= 0) {
    http_response_code(403);
    exit("Usuario cliente sin empresa asociada.");
}

$filtro = $esCliente ? " WHERE i.CLIENTE_id_cliente = :cliente_id" : "";
$params = $esCliente ? [":cliente_id" => $clienteId] : [];

function consultarValor(PDO $conexion, string $sql, array $params = []): int {
    $stmt = $conexion->prepare($sql); $stmt->execute($params); return (int) $stmt->fetchColumn();
}
function consultarFilas(PDO $conexion, string $sql, array $params = []): array {
    $stmt = $conexion->prepare($sql); $stmt->execute($params); return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$totalIncidentes = consultarValor($conexion, "SELECT COUNT(*) FROM incidentes i{$filtro}", $params);
$altaCritica = consultarValor($conexion, "SELECT COUNT(*) FROM incidentes i{$filtro}" . ($esCliente ? " AND" : " WHERE") . " i.criticidad IN ('ALTA','CRITICA')", $params);

$sqlAbiertos = "SELECT COUNT(*) FROM (SELECT i.id_incidente, (SELECT he.estado_actual FROM historial_estados he WHERE he.INCIDENTES_id_incidente = i.id_incidente ORDER BY he.fecha_cambio DESC, he.id_historial DESC LIMIT 1) AS estado_actual FROM incidentes i{$filtro}) t WHERE t.estado_actual = 'ABIERTO'";
$incidentesAbiertos = consultarValor($conexion, $sqlAbiertos, $params);

$porCriticidad = consultarFilas($conexion, "SELECT i.criticidad, COUNT(*) total FROM incidentes i{$filtro} GROUP BY i.criticidad ORDER BY total DESC", $params);
$porEstado = consultarFilas($conexion, "SELECT estado_actual, COUNT(*) total FROM (SELECT i.id_incidente, (SELECT he.estado_actual FROM historial_estados he WHERE he.INCIDENTES_id_incidente = i.id_incidente ORDER BY he.fecha_cambio DESC, he.id_historial DESC LIMIT 1) AS estado_actual FROM incidentes i{$filtro}) t GROUP BY estado_actual", $params);

$totalClientes = $esCliente ? 1 : consultarValor($conexion, "SELECT COUNT(*) FROM cliente WHERE estado_cliente = 'ACTIVO'");
$topClientes = $esCliente ? [] : consultarFilas($conexion, "SELECT c.razon_social, COUNT(*) total FROM incidentes i INNER JOIN cliente c ON c.id_cliente = i.CLIENTE_id_cliente GROUP BY c.id_cliente, c.razon_social ORDER BY total DESC LIMIT 5");

$ultimosIncidentes = consultarFilas($conexion, "SELECT i.id_incidente, c.razon_social, ca.nombre_vector, i.criticidad, i.fecha_registro FROM incidentes i INNER JOIN cliente c ON c.id_cliente = i.CLIENTE_id_cliente INNER JOIN categorias_amenaza ca ON ca.id_categoria = i.CATEGORIAS_AMENAZA_id_categoria{$filtro} ORDER BY i.fecha_registro DESC LIMIT 5", $params);
?>
<div class="layout">
<?php require_once "includes/menu.php"; ?>
 
<main class="contenido">
<header class="barra-superior">
    <div class="usuario-barra">Usuario: <strong><?= htmlspecialchars($_SESSION["email"] ?? "Sin email", ENT_QUOTES, "UTF-8") ?>
        </strong>
    </div>
    <div class="permisos-barra">Permisos actuales: <strong><?= htmlspecialchars($rolTexto, ENT_QUOTES, "UTF-8") ?>
        </strong>
    </div>
    </header>
<section class="topbar">
    <div>
        <h1><?= $esCliente ? "Dashboard de Cliente" : "Dashboard Analítico" ?>
        </h1>
        <p><?= $esCliente ? "Visualización de incidentes asociados a " . htmlspecialchars($_SESSION["cliente_nombre"] ?? "su empresa", ENT_QUOTES, "UTF-8") . "." : "Indicadores en tiempo real para la gestión de incidentes de ciberseguridad." ?></p>
    </div>
    </section>
<section class="cards">
<article class="card"><span>Total incidentes</span><strong><?= $totalIncidentes ?></strong></article>
<article class="card"><span>Incidentes abiertos</span><strong><?= $incidentesAbiertos ?></strong></article>
<article class="card card-critica"><span>Alta/Crítica</span><strong><?= $altaCritica ?></strong></article>
<article class="card"><span><?= $esCliente ? "Cliente asociado" : "Clientes activos" ?></span><strong><?= $esCliente ? htmlspecialchars($_SESSION["cliente_nombre"] ?? "—", ENT_QUOTES, "UTF-8") : $totalClientes ?></strong></article>
</section>
<section class="grid-analitico grid-graficos">
<article class="panel panel-grafico">
    <h2>Incidentes por criticidad</h2>
    <p class="descripcion-grafico">Distribución porcentual de los incidentes visibles.</p>
    <div class="grafico-torta-contenedor">
        <div id="graficoCriticidad" class="grafico-torta" role="img" aria-label="Gráfico de incidentes por criticidad">
            <div class="grafico-torta-centro"><strong><?= $totalIncidentes ?></strong><span>Total</span>
            </div></div><div id="leyendaCriticidad" class="leyenda-grafico">
        </div>
    </div>
    </article>
<article class="panel panel-grafico">
    <h2>Incidentes por estado</h2>
    <p class="descripcion-grafico">Cantidad de incidentes según su estado actual.</p><div id="graficoEstado" class="grafico-barras" role="img" aria-label="Gráfico de barras de incidentes por estado"></div></article>
</section>
<section class="grid-analitico">
<?php if (!$esCliente): ?><article class="panel"><h2>Top clientes afectados</h2>
    <table class="tabla">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Total incidentes</th>
            </tr>
        </thead>
        <tbody><?php foreach ($topClientes as $row): ?><tr><td><?= htmlspecialchars($row["razon_social"], ENT_QUOTES, "UTF-8") ?></td><td><?= (int)$row["total"] ?></td>
            </tr><?php endforeach; ?></tbody>
    </table>
    </article><?php endif; ?>
<article class="panel">
    <h2>Últimos incidentes registrados</h2>
    <table class="tabla">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Vector</th>
                <th>Criticidad</th>
                <th>Fecha</th>
            </tr></thead>
        <tbody><?php foreach ($ultimosIncidentes as $row): ?><tr><td><?= (int)$row["id_incidente"] ?></td>
            <td><?= htmlspecialchars($row["razon_social"], ENT_QUOTES, "UTF-8") ?></td>
            <td><?= htmlspecialchars($row["nombre_vector"], ENT_QUOTES, "UTF-8") ?></td>
            <td><?= htmlspecialchars($row["criticidad"], ENT_QUOTES, "UTF-8") ?></td>
            <td><?= htmlspecialchars($row["fecha_registro"], ENT_QUOTES, "UTF-8") ?></td>
            </tr><?php endforeach; ?><?php if (empty($ultimosIncidentes)): ?><tr>
            <td colspan="5">No existen incidentes registrados.</td></tr><?php endif; ?></tbody>
    </table>
    </article>
</section>
</main>
</div>
<script>window.datosDashboard={criticidad:<?= json_encode($porCriticidad, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>,estado:<?= json_encode($porEstado, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>};</script>
<script src="<?= e(app_url('/js/dashboard-graficos.js')) ?>"></script>
<?php require_once "includes/footer.php"; ?>
