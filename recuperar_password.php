<?php
session_start();
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/includes/email.php';

$mensaje = '';
$error = '';
$enlaceDesarrollo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        $error = 'La solicitud no es válida. Recargue la página e intente nuevamente.';
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Ingrese una dirección de correo válida.';
        } else {
            $stmt = $conexion->prepare(
                "SELECT id_usuario, nombre, email
                 FROM usuario
                 WHERE email = :email AND estado = 'ACTIVO'
                 LIMIT 1"
            );
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch();

            // Respuesta genérica para impedir enumeración de cuentas.
            $mensaje = 'Si el correo pertenece a una cuenta activa, recibirá un enlace de recuperación válido por 15 minutos.';

            if ($usuario) {
                $tokenPublico = bin2hex(random_bytes(16)); // 32 caracteres
                $tokenHash = hash('sha256', $tokenPublico); // 64 caracteres en BD
                $minutos = (int) app_config('security.password_reset_minutes', 15);
                $expira = (new DateTimeImmutable("+{$minutos} minutes"))->format('Y-m-d H:i:s');

                $update = $conexion->prepare(
                    'UPDATE usuario
                     SET token_recovery = :token, token_expira = :expira
                     WHERE id_usuario = :id'
                );
                $update->execute([
                    ':token' => $tokenHash,
                    ':expira' => $expira,
                    ':id' => $usuario['id_usuario'],
                ]);

                $url = app_base_url() . '/restablecer_password.php?token=' . urlencode($tokenPublico);
                $nombreSeguro = htmlspecialchars((string) $usuario['nombre'], ENT_QUOTES, 'UTF-8');
                $urlSeguro = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

                $html = <<<HTML
<!doctype html>
<html lang="es">
<body style="font-family:Arial,sans-serif;color:#1f2937;line-height:1.5">
  <div style="max-width:620px;margin:auto;border:1px solid #dbe3ee;border-radius:10px;padding:28px">
    <h2 style="color:#0f3d6e">Recuperación de contraseña - CyberData Networks</h2>
    <p>Hola {$nombreSeguro},</p>
    <p>Se solicitó restablecer la contraseña de su cuenta.</p>
    <p style="margin:28px 0">
      <a href="{$urlSeguro}" style="background:#0f3d6e;color:#fff;text-decoration:none;padding:12px 18px;border-radius:6px">Restablecer contraseña</a>
    </p>
    <p>El enlace expira en {$minutos} minutos y solo puede utilizarse una vez.</p>
    <p>Si usted no realizó esta solicitud, ignore este mensaje.</p>
  </div>
</body>
</html>
HTML;

                $texto = "Hola {$usuario['nombre']}.\n\n"
                    . "Use el siguiente enlace para restablecer su contraseña:\n{$url}\n\n"
                    . "El enlace expira en {$minutos} minutos. Si no realizó esta solicitud, ignore este mensaje.";

                $enviado = smtp_send(
                    (string) $usuario['email'],
                    'Recuperación de contraseña - CyberData Networks',
                    $html,
                    $texto
                );
                
                if (!$enviado && app_config('app.environment') === 'development') {
                    $enlaceDesarrollo = $url;
                }
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
    <title>Recuperar contraseña - CyberData</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="auth-body">
<main class="auth-card">
    <h1>Recuperar contraseña</h1>
    <p>Ingrese el correo asociado a su cuenta.</p>

    <?php if ($mensaje): ?>
        <div class="alerta exito"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alerta error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($enlaceDesarrollo): ?>
        <div class="alerta aviso">
            <strong>Modo desarrollo:</strong> SMTP está deshabilitado. Use este enlace para probar:<br>
            <a href="<?= htmlspecialchars($enlaceDesarrollo, ENT_QUOTES, 'UTF-8') ?>">Abrir recuperación</a>
        </div>
    <?php endif; ?>

    <form method="post" class="auth-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" maxlength="100" autocomplete="email" required>

        <button type="submit">Enviar enlace</button>
    </form>

    <p class="auth-links"><a href="login.php">Volver al inicio de sesión</a></p>
</main>
</body>
</html>
