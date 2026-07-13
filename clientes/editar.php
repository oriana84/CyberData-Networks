<?php
require_once "../config/bootstrap.php";
require_once "../includes/header.php";
require_once "../config/conexion.php";

if ($_SESSION["rol_id"] != 1) {
    header("Location: ../dashboard.php");
    exit;
}

$id = $_GET["id"] ?? null;

if (!$id) {
    header("Location: listar.php");
    exit;
}

$sql = "SELECT * FROM cliente WHERE id_cliente = :id";
$stmt = $conexion->prepare($sql);
$stmt->execute([":id" => $id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    header("Location: listar.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!csrf_validate($_POST["csrf_token"] ?? null)) {
        http_response_code(403);
        exit("Solicitud inválida o token CSRF incorrecto. Actualice la página e inténtelo nuevamente.");
    }


    $rut = trim($_POST["rut_empresa"]);
    $razon = trim($_POST["razon_social"]);
    $email = trim($_POST["email"]);
    $telefono = trim($_POST["telefono"]);
    $estado = $_POST["estado_cliente"];

    $sqlExiste = "SELECT COUNT(*) FROM cliente
                  WHERE rut_empresa = :rut
                  AND id_cliente <> :id";

    $stmtExiste = $conexion->prepare($sqlExiste);
    $stmtExiste->execute([
        ":rut" => $rut,
        ":id" => $id
    ]);

    if ($stmtExiste->fetchColumn() > 0) {
        $error = "Ya existe otro cliente con ese RUT.";
    } else {

        $sqlUpdate = "UPDATE cliente
                      SET rut_empresa = :rut,
                          razon_social = :razon,
                          email = :email,
                          telefono = :telefono,
                          estado_cliente = :estado
                      WHERE id_cliente = :id";

        $stmtUpdate = $conexion->prepare($sqlUpdate);
        $stmtUpdate->execute([
            ":rut" => $rut,
            ":razon" => $razon,
            ":email" => $email,
            ":telefono" => $telefono,
            ":estado" => $estado,
            ":id" => $id
        ]);

        header("Location: listar.php");
        exit;
    }
}
?>

<div class="layout">
    <?php require_once "../includes/menu.php"; ?>

    <main class="contenido">
        <section class="panel">
            <h1>Editar Cliente</h1>

            <?php if ($error): ?>
                <p class="mensaje-error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST" class="formulario">

                <?= csrf_input(); ?>

                <label>RUT Empresa</label>
                <input type="text" name="rut_empresa" required
                       value="<?php echo htmlspecialchars($cliente["rut_empresa"]); ?>">

                <label>Razón Social</label>
                <input type="text" name="razon_social" required
                       value="<?php echo htmlspecialchars($cliente["razon_social"]); ?>">

                <label>Email</label>
                <input type="email" name="email"
                       value="<?php echo htmlspecialchars($cliente["email"]); ?>">

                <label>Teléfono</label>
                <input type="text" name="telefono"
                       value="<?php echo htmlspecialchars($cliente["telefono"]); ?>">

                <label>Estado</label>
                <select name="estado_cliente" required>
                    <option value="ACTIVO" <?php echo ($cliente["estado_cliente"] == "ACTIVO") ? "selected" : ""; ?>>
                        ACTIVO
                    </option>
                    <option value="INACTIVO" <?php echo ($cliente["estado_cliente"] == "INACTIVO") ? "selected" : ""; ?>>
                        INACTIVO
                    </option>
                </select>

                <div class="acciones-form">
                    <button type="submit" class="btn">Guardar Cambios</button>
                    <a href="listar.php" class="btn-secundario">Volver</a>
                </div>

            </form>
        </section>
    </main>
</div>

<?php require_once "../includes/footer.php"; ?>