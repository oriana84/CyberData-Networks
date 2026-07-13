<?php
require_once "../config/bootstrap.php";
require_once "../includes/header.php";
require_once "../config/conexion.php";
if ((int) $_SESSION["rol_id"] !== 1) { header("Location: /CyberData/dashboard.php"); exit; }

$sql = "SELECT u.id_usuario, u.nombre, u.email, u.estado, r.nombre AS rol, c.razon_social AS cliente
        FROM usuario u
        INNER JOIN rol r ON r.id_rol = u.ROL_id_rol
        LEFT JOIN usuario_cliente uc ON uc.id_usuario = u.id_usuario
        LEFT JOIN cliente c ON c.id_cliente = uc.id_cliente
        ORDER BY u.id_usuario DESC";
$usuarios = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="layout"><?php require_once "../includes/menu.php"; ?><main class="contenido"><section class="panel">
<h1>Gestión de Usuarios</h1>
<?php if (($_GET["error"] ?? "") === "no_auto_inactivar"): ?><p class="mensaje-error">No puedes inactivar tu propio usuario administrador.</p><?php endif; ?>
<?php if (($_GET["error"] ?? "") === "usuario_no_encontrado"): ?><p class="mensaje-error">El usuario seleccionado no existe.</p><?php endif; ?>
<?php if (($_GET["mensaje"] ?? "") === "estado_actualizado"): ?><p class="mensaje-ok">El estado del usuario fue actualizado correctamente.</p><?php endif; ?>
<?php if (($_GET["mensaje"] ?? "") === "usuario_actualizado"): ?><p class="mensaje-ok">El usuario fue actualizado correctamente.</p><?php endif; ?>
<a class="btn" href="agregar.php">Crear usuario</a>
<table class="tabla"><thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Cliente asociado</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
<?php foreach ($usuarios as $u): ?><tr>
<td><?= (int)$u["id_usuario"] ?></td><td><?= htmlspecialchars($u["nombre"], ENT_QUOTES, "UTF-8") ?></td><td><?= htmlspecialchars($u["email"], ENT_QUOTES, "UTF-8") ?></td><td><?= htmlspecialchars($u["rol"], ENT_QUOTES, "UTF-8") ?></td><td><?= htmlspecialchars($u["cliente"] ?? "—", ENT_QUOTES, "UTF-8") ?></td><td><?= htmlspecialchars($u["estado"], ENT_QUOTES, "UTF-8") ?></td>
<td><a href="editar.php?id=<?= (int)$u["id_usuario"] ?>">Editar</a>
<?php if ((int)$u["id_usuario"] !== (int)$_SESSION["id_usuario"]): ?> | <form method="POST" action="eliminar.php" style="display:inline" onsubmit="return confirm('<?= $u["estado"] === "ACTIVO" ? "¿Deseas inactivar este usuario?" : "¿Deseas activar este usuario?" ?>');"><?= csrf_input(); ?><input type="hidden" name="id" value="<?= (int)$u["id_usuario"] ?>"><button type="submit" class="enlace-boton"><?= $u["estado"] === "ACTIVO" ? "Inactivar" : "Activar" ?></button></form><?php else: ?> | <span title="No puedes modificar tu propio estado">Estado protegido</span><?php endif; ?>
</td></tr><?php endforeach; ?>
<?php if (empty($usuarios)): ?><tr><td colspan="7">No existen usuarios registrados.</td></tr><?php endif; ?>
</tbody></table></section></main></div><?php require_once "../includes/footer.php"; ?>
