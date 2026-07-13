<?php

define("SESSION_TIMEOUT", 900);

if (isset($_SESSION["id_usuario"])) {

    if (isset($_SESSION["ultima_actividad"])) {

        $inactividad = time() - $_SESSION["ultima_actividad"];

        if ($inactividad >= SESSION_TIMEOUT) {

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

            header("Location: /CyberData/login.php?estado=timeout");
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