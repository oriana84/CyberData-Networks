<?php
session_start();
require_once __DIR__ . '/config/conexion.php';

$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$mensaje = '';
$error = '';
$tokenValido = false;
$usuarioId = null;

if (preg_match('/^[a-f0-9]{32}$/', $token)) {
    $tokenHash = hash('sha256', $token);
    $stmt = $conexion->prepare(
        "SELECT id_usuario
         FROM usuario
         WHERE token_recovery = :token
           AND token_expira IS NOT NULL
           AND token_expira >= NOW()
           AND estado = 'ACTIVO'
         LIMIT 1"
    );
    $stmt->execute([':token' => $tokenHash]);
    $registro = $stmt->fetch();

    if ($registro) {
        $tokenValido = true;
        $usuarioId = (int) $registro['id_usuario'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        $error = 'La solicitud no es válida. Recargue la página e intente nuevamente.';
    } elseif (!$tokenValido || !$usuarioId) {
        $error = 'El enlace es inválido, ya fue utilizado o ha expirado.';
    } else {
        $password = (string) ($_POST['password'] ?? '');
        $confirmacion = (string) ($_POST['password_confirmacion'] ?? '');

        if (strlen($password) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif (!preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/\d/', $password)) {
            $error = 'La contraseña debe incluir mayúscula, minúscula y número.';
        } elseif ($password !== $confirmacion) {
            $error = 'Las contraseñas no coinciden.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $update = $conexion->prepare(
                'UPDATE usuario
                 SET password_hash = :password_hash,
                     token_recovery = NULL,
                     token_expira = NULL
                 WHERE id_usuario = :id
                   AND token_recovery = :token'
            );
            $update->execute([
                ':password_hash' => $passwordHash,
                ':id' => $usuarioId,
                ':token' => hash('sha256', $token),
            ]);

            if ($update->rowCount() === 1) {
                $mensaje = 'La contraseña fue actualizada correctamente. Ya puede iniciar sesión.';
                $tokenValido = false;
            } else {
                $error = 'No fue posible actualizar la contraseña. Solicite un nuevo enlace.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña - CyberData</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="auth-body">
<main class="auth-card">
    <h1>Nueva contraseña</h1>

    <?php if ($mensaje): ?>
        <div class="alerta exito"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
        <p class="auth-links"><a href="login.php">Ir al inicio de sesión</a></p>
    <?php elseif (!$tokenValido): ?>
        <div class="alerta error">El enlace es inválido, ya fue utilizado o ha expirado.</div>
        <p class="auth-links"><a href="recuperar_password.php">Solicitar un nuevo enlace</a></p>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alerta error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" class="auth-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

            <label for="password">Nueva contraseña</label>
            <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required>
            <small>Mínimo 8 caracteres, con mayúscula, minúscula y número.</small>

            <label for="password_confirmacion">Confirmar contraseña</label>
            <input type="password" id="password_confirmacion" name="password_confirmacion" minlength="8" autocomplete="new-password" required>

            <button type="submit">Actualizar contraseña</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
