<?php
require_once "../config/bootstrap.php";
require_once "../config/session.php";
require_once "../includes/session_timeout.php";
require_once "../config/conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: /CyberData/login.php");
    exit;
}

if ((int) ($_SESSION["rol_id"] ?? 0) !== 1) {
    header("Location: ../dashboard.php");
    exit;
}

$error = "";
$nombreVector = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    csrf_require_valid();

    $nombreVector = trim(
        $_POST["nombre_vector"] ?? ""
    );

    if ($nombreVector === "") {
        $error = "Debe ingresar el nombre del vector de amenaza.";
    } elseif (mb_strlen($nombreVector) > 45) {
        $error = "El nombre del vector no puede superar los 45 caracteres.";
    }

    if ($error === "") {

        $sqlExiste = "SELECT COUNT(*)
                      FROM categorias_amenaza
                      WHERE LOWER(nombre_vector) = LOWER(:nombre_vector)";

        $stmtExiste = $conexion->prepare($sqlExiste);

        $stmtExiste->execute([
            ":nombre_vector" => $nombreVector
        ]);

        if ((int) $stmtExiste->fetchColumn() > 0) {
            $error = "Ya existe una categoría con ese nombre.";
        }
    }

    if ($error === "") {

        $sql = "INSERT INTO categorias_amenaza
                (
                    nombre_vector
                )
                VALUES
                (
                    :nombre_vector
                )";

        $stmt = $conexion->prepare($sql);

        $stmt->execute([
            ":nombre_vector" => $nombreVector
        ]);

        header("Location: listar.php?mensaje=creada");
        exit;
    }
}

require_once "../includes/header.php";
?>

<div class="layout">

    <?php require_once "../includes/menu.php"; ?>

    <main class="contenido">

        <section class="panel">

            <h1>Crear Categoría de Amenaza</h1>

            <?php if ($error !== ""): ?>
                <p class="mensaje-error">
                    <?php echo htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>
                </p>
            <?php endif; ?>

            <form
                method="POST"
                class="formulario"
            >

                <?= csrf_input(); ?>

                <label for="nombre_vector">
                    Nombre del vector
                </label>

                <input
                    type="text"
                    id="nombre_vector"
                    name="nombre_vector"
                    maxlength="45"
                    required
                    autofocus
                    value="<?php echo htmlspecialchars(
                        $nombreVector,
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>"
                >

                <small>
                    Ejemplo: Malware, Phishing, Ransomware o SQL Injection.
                </small>

                <div class="acciones-form">

                    <button
                        type="submit"
                        class="btn"
                    >
                        Guardar categoría
                    </button>

                    <a
                        href="listar.php"
                        class="btn-secundario"
                    >
                        Volver
                    </a>

                </div>

            </form>

        </section>

    </main>

</div>

<?php require_once "../includes/footer.php"; ?>