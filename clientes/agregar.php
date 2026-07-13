<?php
require_once "../config/bootstrap.php";
require_once "../includes/header.php";
require_once "../config/conexion.php";

if ($_SESSION["rol_id"] != 1) {
    header("Location: ../dashboard.php");
    exit;
}

$mensaje = "";
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

    $sqlExiste = "SELECT COUNT(*) FROM cliente WHERE rut_empresa = :rut";
    $stmtExiste = $conexion->prepare($sqlExiste);
    $stmtExiste->execute([":rut" => $rut]);

    if ($stmtExiste->fetchColumn() > 0) {
        $error = "Ya existe un cliente con ese RUT.";
    } else {

        $sql = "INSERT INTO cliente
                (rut_empresa, razon_social, email, telefono, estado_cliente)
                VALUES
                (:rut, :razon, :email, :telefono, :estado)";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ":rut" => $rut,
            ":razon" => $razon,
            ":email" => $email,
            ":telefono" => $telefono,
            ":estado" => $estado
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
            <h1>Agregar Cliente</h1>

            <?php if ($error): ?>
                <p class="mensaje-error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST" class="formulario">

                <?= csrf_input(); ?>

                <label>RUT Empresa</label>
                <input type="text" name="rut_empresa" required>

                <label>Razón Social</label>
                <input type="text" name="razon_social" required>

                <label>Email</label>
                <input type="email" name="email">

                <label>Teléfono</label>
                <input type="text" name="telefono">

                <label>Estado</label>
                <select name="estado_cliente" required>
                    <option value="ACTIVO">ACTIVO</option>
                    <option value="INACTIVO">INACTIVO</option>
                </select>

                <div class="acciones-form">
                    <button type="submit" class="btn">Guardar Cliente</button>
                    <a href="listar.php" class="btn-secundario">Volver</a>
                </div>

            </form>
        </section>
    </main>
</div>

<?php require_once "../includes/footer.php"; ?>