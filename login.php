<?php
require_once __DIR__ . "/config/bootstrap.php";
require_once __DIR__ . "/config/session.php";
require_once __DIR__ . "/config/conexion.php";

$error = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_validate($_POST["csrf_token"] ?? null)) {
        http_response_code(403);
        exit("Solicitud inválida o token CSRF incorrecto. Actualice la página e inténtelo nuevamente.");
    }

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $sql = "SELECT
                u.id_usuario,
                u.nombre,
                u.email,
                u.password_hash,
                u.estado,
                u.ROL_id_rol,
                r.nombre AS rol_nombre,
                uc.id_cliente,
                c.razon_social,
                c.estado_cliente
            FROM usuario u
            INNER JOIN rol r ON r.id_rol = u.ROL_id_rol
            LEFT JOIN usuario_cliente uc ON uc.id_usuario = u.id_usuario
            LEFT JOIN cliente c ON c.id_cliente = uc.id_cliente
            WHERE u.email = :email
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([":email" => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $error = "El usuario no existe.";
    } elseif ($usuario["estado"] !== "ACTIVO") {
        $error = "El usuario está inactivo. Contacte al administrador.";
    } elseif (!password_verify($password, $usuario["password_hash"])) {
        $error = "Contraseña incorrecta.";
    } elseif ((int) $usuario["ROL_id_rol"] === 3 && empty($usuario["id_cliente"])) {
        $error = "El usuario cliente no tiene una empresa asociada. Contacte al administrador.";
    } elseif ((int) $usuario["ROL_id_rol"] === 3 && $usuario["estado_cliente"] !== "ACTIVO") {
        $error = "La cuenta del cliente asociado está inactiva.";
    } else {
        session_regenerate_id(true);

        $_SESSION["id_usuario"] = (int) $usuario["id_usuario"];
        $_SESSION["nombre"] = $usuario["nombre"];
        $_SESSION["email"] = $usuario["email"];
        $_SESSION["rol_id"] = (int) $usuario["ROL_id_rol"];
        $_SESSION["rol_nombre"] = $usuario["rol_nombre"];
        $_SESSION["cliente_id"] = $usuario["id_cliente"] !== null
            ? (int) $usuario["id_cliente"]
            : null;
        $_SESSION["cliente_nombre"] = $usuario["razon_social"] ?? null;
        $_SESSION["ultima_actividad"] = time();
        $_SESSION["ultima_regeneracion"] = time();

        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CyberData - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/estilos.css?v=15">
   
</head>
<body class="auth-body">
<main class="auth-card">
    <h1 class="titulo-login">CyberData Networks</h1>
       <p class="descripcion-app">
        Plataforma Web de Analitica y Gestion de Incidentes Ciberseguridad.<br>
    </p>
    <?php if (($_GET["estado"] ?? "") === "timeout"): ?>
        <p style="color:#d97706; font-weight:bold;">Su sesión expiró por 15 minutos de inactividad. Inicie sesión nuevamente.</p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></p>
    <?php endif; ?>
    <form method="POST" class="auth-form">
        <?= csrf_input(); ?>
        <label for="email">Correo:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required>
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>
        <button type="submit">Ingresar</button>
    </form>
    <p class="auth-links"><a href="recuperar_password.php">¿Olvidó su contraseña?</a></p>
    <footer>

    <a href="politica_privacidad.php">
        Política de privacidad
    </a>
    |
    <a href="contacto.php">
        Contacto
    </a>
            <p>
            CyberData © 2026
           </p>
</footer>
</main>
</body>
</html>
