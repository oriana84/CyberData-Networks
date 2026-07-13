<?php
require_once "../config/bootstrap.php";
require_once "../config/session.php";
require_once "../includes/session_timeout.php";
require_once "../config/conexion.php";

if (!isset($_SESSION["id_usuario"])) { header("Location: /CyberData/login.php"); exit; }
if ((int) $_SESSION["rol_id"] !== 1) { header("Location: ../dashboard.php"); exit; }

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id || $id <= 0) { header("Location: listar.php?error=usuario_no_encontrado"); exit; }

$roles = $conexion->query("SELECT id_rol, nombre FROM rol ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
$clientes = $conexion->query("SELECT id_cliente, razon_social FROM cliente ORDER BY razon_social ASC")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conexion->prepare("SELECT u.id_usuario, u.nombre, u.email, u.estado, u.ROL_id_rol, uc.id_cliente FROM usuario u LEFT JOIN usuario_cliente uc ON uc.id_usuario = u.id_usuario WHERE u.id_usuario = :id LIMIT 1");
$stmt->execute([":id" => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$usuario) { header("Location: listar.php?error=usuario_no_encontrado"); exit; }

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_require_valid();
    $nombre = trim($_POST["nombre"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $rol = $_POST["rol"] ?? "";
    $clienteId = $_POST["cliente_id"] ?? "";
    $estado = $_POST["estado"] ?? "";
    $password = $_POST["password"] ?? "";

    if ((int) $id === (int) $_SESSION["id_usuario"]) {
        $estado = $usuario["estado"];
    }

    if ($nombre === "") $error = "Debe ingresar el nombre del usuario.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = "Debe ingresar un correo electrónico válido.";
    elseif (!ctype_digit((string) $rol)) $error = "Debe seleccionar un rol válido.";
    elseif (!in_array($estado, ["ACTIVO", "INACTIVO"], true)) $error = "Debe seleccionar un estado válido.";

    $rolId = (int) $rol;
    if ($error === "") {
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM rol WHERE id_rol = :rol");
        $stmt->execute([":rol" => $rolId]);
        if ((int) $stmt->fetchColumn() === 0) $error = "El rol seleccionado no existe.";
    }
    if ($error === "" && $rolId === 3) {
        if (!ctype_digit((string) $clienteId)) $error = "Debe seleccionar el cliente asociado.";
        else {
            $stmt = $conexion->prepare("SELECT COUNT(*) FROM cliente WHERE id_cliente = :id");
            $stmt->execute([":id" => (int) $clienteId]);
            if ((int) $stmt->fetchColumn() === 0) $error = "El cliente seleccionado no existe.";
        }
    }
    if ($error === "") {
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM usuario WHERE email = :email AND id_usuario <> :id");
        $stmt->execute([":email" => $email, ":id" => $id]);
        if ((int) $stmt->fetchColumn() > 0) $error = "El correo ingresado ya está en uso.";
    }

    if ($error === "") {
        try {
            $conexion->beginTransaction();
            $camposPassword = "";
            $params = [":nombre"=>$nombre, ":email"=>$email, ":estado"=>$estado, ":rol"=>$rolId, ":id"=>$id];
            if ($password !== "") {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                if ($hash === false) throw new RuntimeException("No fue posible procesar la contraseña.");
                $camposPassword = ", password_hash = :password_hash";
                $params[":password_hash"] = $hash;
            }
            $stmt = $conexion->prepare("UPDATE usuario SET nombre = :nombre, email = :email, estado = :estado, ROL_id_rol = :rol {$camposPassword} WHERE id_usuario = :id");
            $stmt->execute($params);

            if ($rolId === 3) {
                $stmt = $conexion->prepare("INSERT INTO usuario_cliente (id_usuario, id_cliente) VALUES (:usuario, :cliente) ON DUPLICATE KEY UPDATE id_cliente = VALUES(id_cliente)");
                $stmt->execute([":usuario"=>$id, ":cliente"=>(int)$clienteId]);
            } else {
                $stmt = $conexion->prepare("DELETE FROM usuario_cliente WHERE id_usuario = :usuario");
                $stmt->execute([":usuario"=>$id]);
            }
            $conexion->commit();
            header("Location: listar.php?mensaje=usuario_actualizado");
            exit;
        } catch (Throwable $e) {
            if ($conexion->inTransaction()) $conexion->rollBack();
            $error = $e instanceof RuntimeException ? $e->getMessage() : "No fue posible actualizar el usuario.";
        }
    }

    $usuario["nombre"] = $nombre;
    $usuario["email"] = $email;
    $usuario["ROL_id_rol"] = $rolId;
    $usuario["id_cliente"] = $rolId === 3 && ctype_digit((string)$clienteId) ? (int)$clienteId : null;
    $usuario["estado"] = $estado;
}
require_once "../includes/header.php";
?>
<div class="layout">
<?php require_once "../includes/menu.php"; ?>
<main class="contenido"><section class="panel">
<h1>Editar Usuario</h1>
<?php if ($error !== ""): ?><p class="mensaje-error"><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></p><?php endif; ?>
<form method="POST" class="formulario">
<?= csrf_input(); ?>
<label for="nombre">Nombre</label><input type="text" id="nombre" name="nombre" maxlength="100" required value="<?= htmlspecialchars($usuario["nombre"], ENT_QUOTES, "UTF-8") ?>">
<label for="email">Email</label><input type="email" id="email" name="email" maxlength="150" required value="<?= htmlspecialchars($usuario["email"], ENT_QUOTES, "UTF-8") ?>">
<label for="password">Nueva contraseña</label><input type="password" id="password" name="password" autocomplete="new-password" placeholder="Dejar vacío para mantener la actual">
<label for="rol">Rol</label><select id="rol" name="rol" required>
<?php foreach ($roles as $item): ?><option value="<?= (int)$item["id_rol"] ?>" <?= (int)$item["id_rol"] === (int)$usuario["ROL_id_rol"] ? "selected" : "" ?>><?= htmlspecialchars($item["nombre"], ENT_QUOTES, "UTF-8") ?></option><?php endforeach; ?>
</select>
<div id="grupo-cliente" <?= (int)$usuario["ROL_id_rol"] === 3 ? "" : "hidden" ?>>
<label for="cliente_id">Cliente asociado</label><select id="cliente_id" name="cliente_id" <?= (int)$usuario["ROL_id_rol"] === 3 ? "required" : "" ?>><option value="">Seleccione un cliente</option>
<?php foreach ($clientes as $cliente): ?><option value="<?= (int)$cliente["id_cliente"] ?>" <?= (int)$cliente["id_cliente"] === (int)($usuario["id_cliente"] ?? 0) ? "selected" : "" ?>><?= htmlspecialchars($cliente["razon_social"], ENT_QUOTES, "UTF-8") ?></option><?php endforeach; ?>
</select><small>El usuario cliente solo podrá visualizar los incidentes de esta empresa.</small></div>
<label for="estado">Estado</label>
<?php if ((int)$id === (int)$_SESSION["id_usuario"]): ?>
<select id="estado" name="estado" required disabled><option selected><?= htmlspecialchars($usuario["estado"], ENT_QUOTES, "UTF-8") ?></option></select><input type="hidden" name="estado" value="<?= htmlspecialchars($usuario["estado"], ENT_QUOTES, "UTF-8") ?>"><small>No puedes cambiar el estado de tu propio usuario.</small>
<?php else: ?><select id="estado" name="estado" required><option value="ACTIVO" <?= $usuario["estado"] === "ACTIVO" ? "selected" : "" ?>>ACTIVO</option><option value="INACTIVO" <?= $usuario["estado"] === "INACTIVO" ? "selected" : "" ?>>INACTIVO</option></select><?php endif; ?>
<div class="acciones-form"><button type="submit" class="btn">Guardar cambios</button><a href="listar.php" class="btn-secundario">Volver</a></div>
</form></section></main></div>
<script>
document.addEventListener("DOMContentLoaded", function(){const rol=document.getElementById("rol"),grupo=document.getElementById("grupo-cliente"),cliente=document.getElementById("cliente_id");function actualizar(){const esCliente=rol.value==="3";grupo.hidden=!esCliente;cliente.required=esCliente;if(!esCliente)cliente.value="";}rol.addEventListener("change",actualizar);actualizar();});
</script>
<?php require_once "../includes/footer.php"; ?>
