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

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {
    header("Location: listar.php?error=id_invalido");
    exit;
}

$sql = "SELECT
            id_categoria,
            nombre_vector
        FROM categorias_amenaza
        WHERE id_categoria = :id
        LIMIT 1";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ":id" => $id
]);

$categoria = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categoria) {
    header(
        "Location: listar.php?error=categoria_no_encontrada"
    );
    exit;
}

$error = "";
$nombreVector = $categoria["nombre_vector"];

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
                      WHERE LOWER(nombre_vector) = LOWER(:nombre_vector)
                        AND id_categoria <> :id";

        $stmtExiste = $conexion->prepare($sqlExiste);

        $stmtExiste->execute([
            ":nombre_vector" => $nombreVector,
            ":id" => $id
        ]);

        if ((int) $stmtExiste->fetchColumn() > 0) {
            $error = "Ya existe otra categoría con ese nombre.";
        }
    }

    if ($error === "") {

        $sqlUpdate = "UPDATE categorias_amenaza
                      SET nombre_vector = :nombre_vector
                      WHERE id_categoria = :id";

        $stmtUpdate = $conexion->prepare($sqlUpdate);

        $stmtUpdate->execute([
            ":nombre_vector" => $nombreVector,
            ":id" => $id
        ]);

        header("Location: listar.php?mensaje=actualizada");
        exit;
    }
}

require_once "../includes/header.php";
?>

<div class="layout">

    <?php require_once "../includes/menu.php"; ?>

    <main class="contenido">

        <section class="panel">

            <h1>Editar Categoría de Amenaza</h1>

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

                <div class="acciones-form">

                    <button
                        type="submit"
                        class="btn"
                    >
                        Guardar cambios
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