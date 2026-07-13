<?php
require_once "../config/bootstrap.php";
require_once "../config/session.php";
require_once "../includes/session_timeout.php";
require_once "../config/conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: /CyberData/login.php");
    exit;
}
if ((int) $_SESSION["rol_id"] !== 1) {
    header("Location: ../dashboard.php");
    exit;
}

$roles = $conexion->query("SELECT id_rol, nombre FROM rol ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
$clientes = $conexion->query("SELECT id_cliente, razon_social FROM cliente WHERE estado_cliente = 'ACTIVO' ORDER BY razon_social ASC")->fetchAll(PDO::FETCH_ASSOC);

$mensaje = "";
$error = "";
$nombre = "";
$email = "";
$rolSeleccionado = "";
$clienteSeleccionado = "";
$estadoSeleccionado = "ACTIVO";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_require_valid();

    $nombre = trim($_POST["nombre"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $rolSeleccionado = $_POST["rol"] ?? "";
    $clienteSeleccionado = $_POST["cliente_id"] ?? "";
    $estadoSeleccionado = $_POST["estado"] ?? "";

    if ($nombre === "") {
        $error = "Debe ingresar el nombre del usuario.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Debe ingresar un correo electrónico válido.";
    } elseif ($password === "") {
        $error = "Debe ingresar una contraseña.";
    } elseif (!ctype_digit((string) $rolSeleccionado)) {
        $error = "Debe seleccionar un rol válido.";
    } elseif (!in_array($estadoSeleccionado, ["ACTIVO", "INACTIVO"], true)) {
        $error = "Debe seleccionar un estado válido.";
    }

    $rolId = (int) $rolSeleccionado;
    if ($error === "") {
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM rol WHERE id_rol = :rol");
        $stmt->execute([":rol" => $rolId]);
        if ((int) $stmt->fetchColumn() === 0) {
            $error = "El rol seleccionado no existe.";
        }
    }

    if ($error === "" && $rolId === 3) {
        if (!ctype_digit((string) $clienteSeleccionado)) {
            $error = "Debe seleccionar el cliente asociado.";
        } else {
            $stmt = $conexion->prepare("SELECT COUNT(*) FROM cliente WHERE id_cliente = :id AND estado_cliente = 'ACTIVO'");
            $stmt->execute([":id" => (int) $clienteSeleccionado]);
            if ((int) $stmt->fetchColumn() === 0) {
                $error = "El cliente seleccionado no existe o está inactivo.";
            }
        }
    }

    if ($error === "") {
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM usuario WHERE email = :email");
        $stmt->execute([":email" => $email]);
        if ((int) $stmt->fetchColumn() > 0) {
            $error = "El correo ingresado ya existe.";
        }
    }

    if ($error === "") {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            $error = "No fue posible procesar la contraseña.";
        } else {
            try {
                $conexion->beginTransaction();
                $stmt = $conexion->prepare("INSERT INTO usuario (nombre, email, password_hash, estado, ROL_id_rol) VALUES (:nombre, :email, :password_hash, :estado, :rol)");
                $stmt->execute([
                    ":nombre" => $nombre,
                    ":email" => $email,
                    ":password_hash" => $passwordHash,
                    ":estado" => $estadoSeleccionado,
                    ":rol" => $rolId
                ]);
                $idUsuario = (int) $conexion->lastInsertId();

                if ($rolId === 3) {
                    $stmtRelacion = $conexion->prepare("INSERT INTO usuario_cliente (id_usuario, id_cliente) VALUES (:usuario, :cliente)");
                    $stmtRelacion->execute([
                        ":usuario" => $idUsuario,
                        ":cliente" => (int) $clienteSeleccionado
                    ]);
                }

                $conexion->commit();
                $mensaje = "Usuario creado correctamente.";
                $nombre = $email = $rolSeleccionado = $clienteSeleccionado = "";
                $estadoSeleccionado = "ACTIVO";
            } catch (Throwable $e) {
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }
                $error = "No fue posible crear el usuario y su asociación.";
            }
        }
    }
}

require_once "../includes/header.php";
?>
<div class="layout">
    <?php require_once "../includes/menu.php"; ?>
    <main class="contenido">
        <section class="panel">
            <h1>Crear Usuario</h1>
            <?php if ($mensaje !== ""): ?><p class="mensaje-ok"><?= htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") ?></p><?php endif; ?>
            <?php if ($error !== ""): ?><p class="mensaje-error"><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></p><?php endif; ?>
            <form method="POST" class="formulario">
                <?= csrf_input(); ?>
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" maxlength="100" required value="<?= htmlspecialchars($nombre, ENT_QUOTES, "UTF-8") ?>">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" maxlength="150" required value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
                <label for="rol">Rol</label>
                <select id="rol" name="rol" required>
                    <option value="">Seleccione un rol</option>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?= (int) $rol["id_rol"] ?>" <?= (string) $rolSeleccionado === (string) $rol["id_rol"] ? "selected" : "" ?>><?= htmlspecialchars($rol["nombre"], ENT_QUOTES, "UTF-8") ?></option>
                    <?php endforeach; ?>
                </select>
                <div id="grupo-cliente" <?= (int) $rolSeleccionado === 3 ? "" : "hidden" ?>>
                    <label for="cliente_id">Cliente asociado</label>
                    <select id="cliente_id" name="cliente_id" <?= (int) $rolSeleccionado === 3 ? "required" : "" ?>>
                        <option value="">Seleccione un cliente</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= (int) $cliente["id_cliente"] ?>" <?= (string) $clienteSeleccionado === (string) $cliente["id_cliente"] ? "selected" : "" ?>><?= htmlspecialchars($cliente["razon_social"], ENT_QUOTES, "UTF-8") ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small>Este usuario solo podrá visualizar los incidentes de este cliente.</small>
                </div>
                <label for="estado">Estado</label>
                <select id="estado" name="estado" required>
                    <option value="ACTIVO" <?= $estadoSeleccionado === "ACTIVO" ? "selected" : "" ?>>ACTIVO</option>
                    <option value="INACTIVO" <?= $estadoSeleccionado === "INACTIVO" ? "selected" : "" ?>>INACTIVO</option>
                </select>
                <div class="acciones-form"><button type="submit" class="btn">Guardar Usuario</button><a href="listar.php" class="btn-secundario">Volver</a></div>
            </form>
        </section>
    </main>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const rol = document.getElementById("rol");
    const grupo = document.getElementById("grupo-cliente");
    const cliente = document.getElementById("cliente_id");
    function actualizar() {
        const esCliente = rol.value === "3";
        grupo.hidden = !esCliente;
        cliente.required = esCliente;
        if (!esCliente) cliente.value = "";
    }
    rol.addEventListener("change", actualizar);
    actualizar();
});
</script>
<?php require_once "../includes/footer.php"; ?>
