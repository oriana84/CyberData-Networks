<?php
require_once __DIR__ . "/../config/bootstrap.php";

$sessionTimeout = max(300, (int) app_config("security.session_timeout_seconds", 900));

if (isset($_SESSION["id_usuario"])) {

    if (isset($_SESSION["ultima_actividad"])) {

        $inactividad = time() - $_SESSION["ultima_actividad"];

        if ($inactividad >= $sessionTimeout) {

            $_SESSION = [];

            if (ini_get("session.use_cookies")) {

                $params = session_get_cookie_params();

                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

            session_destroy();

            redirect_to("/login.php?estado=timeout");
            exit;
        }
    }

    $_SESSION["ultima_actividad"] = time();

    if (
        !isset($_SESSION["ultima_regeneracion"])
        ||
        time() - $_SESSION["ultima_regeneracion"] >= 300
    ) {

        session_regenerate_id(true);

        $_SESSION["ultima_regeneracion"] = time();
    }

}
