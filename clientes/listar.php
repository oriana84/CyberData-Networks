<?php
require_once "../config/bootstrap.php";
require_once "../includes/header.php";
require_once "../config/conexion.php";

if ($_SESSION["rol_id"] != 1) {
    header("Location: ../dashboard.php");
    exit;
}

$sql = "SELECT * FROM cliente ORDER BY id_cliente DESC";
$stmt = $conexion->query($sql);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="layout">
    <?php require_once "../includes/menu.php"; ?>

    <main class="contenido">
        <section class="panel">
            <h1>Gestión de Clientes</h1>

            <a class="btn" href="agregar.php">Agregar cliente</a>

            <table class="tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>RUT</th>
                        <th>Razón Social</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($clientes as $c): ?>
                        <tr>
                            <td><?php echo $c["id_cliente"]; ?></td>
                            <td><?php echo htmlspecialchars($c["rut_empresa"]); ?></td>
                            <td><?php echo htmlspecialchars($c["razon_social"]); ?></td>
                            <td><?php echo htmlspecialchars($c["email"]); ?></td>
                            <td><?php echo htmlspecialchars($c["telefono"]); ?></td>
                            <td><?php echo htmlspecialchars($c["estado_cliente"]); ?></td>
                            <td>
                                <a href="editar.php?id=<?php echo $c["id_cliente"]; ?>">Editar</a>
                                |

                                <form method="POST"
                                      action="eliminar.php"
                                      style="display:inline;"
                                      onsubmit="return confirm('<?php echo ($c["estado_cliente"] == "ACTIVO") ? "¿Deseas inactivar este cliente?" : "¿Deseas activar este cliente?"; ?>');">

                                    <?= csrf_input(); ?>

                                    <input type="hidden"
                                           name="id"
                                           value="<?php echo (int) $c["id_cliente"]; ?>">

                                    <button type="submit" class="btn-enlace">
                                        <?php echo ($c["estado_cliente"] == "ACTIVO") ? "Inactivar" : "Activar"; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </section>
    </main>
</div>

<?php require_once "../includes/footer.php"; ?>