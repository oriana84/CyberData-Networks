<?php
require_once "../config/bootstrap.php";
require_once "../includes/header.php";
require_once "../config/conexion.php";

if ((int) ($_SESSION["rol_id"] ?? 0) !== 1) {
    header("Location: ../dashboard.php");
    exit;
}

$sql = "SELECT
            ca.id_categoria,
            ca.nombre_vector,
            COUNT(i.id_incidente) AS total_incidentes
        FROM categorias_amenaza ca
        LEFT JOIN incidentes i
            ON i.CATEGORIAS_AMENAZA_id_categoria = ca.id_categoria
        GROUP BY
            ca.id_categoria,
            ca.nombre_vector
        ORDER BY ca.id_categoria DESC";

$stmt = $conexion->query($sql);
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensaje = $_GET["mensaje"] ?? "";
$error = $_GET["error"] ?? "";
?>

<div class="layout">

    <?php require_once "../includes/menu.php"; ?>

    <main class="contenido">

        <section class="panel">

            <h1>Gestión de Categorías de Amenaza</h1>

            <?php if ($mensaje === "creada"): ?>
                <p class="mensaje-ok">
                    La categoría fue creada correctamente.
                </p>
            <?php endif; ?>

            <?php if ($mensaje === "actualizada"): ?>
                <p class="mensaje-ok">
                    La categoría fue actualizada correctamente.
                </p>
            <?php endif; ?>

            <?php if ($mensaje === "eliminada"): ?>
                <p class="mensaje-ok">
                    La categoría fue eliminada correctamente.
                </p>
            <?php endif; ?>

            <?php if ($error === "categoria_no_encontrada"): ?>
                <p class="mensaje-error">
                    La categoría seleccionada no existe.
                </p>
            <?php endif; ?>

            <?php if ($error === "categoria_en_uso"): ?>
                <p class="mensaje-error">
                    No es posible eliminar la categoría porque está asociada
                    a uno o más incidentes.
                </p>
            <?php endif; ?>

            <?php if ($error === "id_invalido"): ?>
                <p class="mensaje-error">
                    El identificador de la categoría no es válido.
                </p>
            <?php endif; ?>

            <a class="btn" href="agregar.php">
                Crear categoría
            </a>

            <table class="tabla">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del vector</th>
                        <th>Incidentes asociados</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($categorias) === 0): ?>
                        <tr>
                            <td colspan="4">
                                No existen categorías registradas.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($categorias as $categoria): ?>
                        <tr>

                            <td>
                                <?php echo (int) $categoria["id_categoria"]; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(
                                    $categoria["nombre_vector"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>
                            </td>

                            <td>
                                <?php echo (int) $categoria["total_incidentes"]; ?>
                            </td>

                            <td>

                                <a href="editar.php?id=<?php
                                    echo (int) $categoria["id_categoria"];
                                ?>">
                                    Editar
                                </a>

                                |

                                <?php if (
                                    (int) $categoria["total_incidentes"] === 0
                                ): ?>

                                    <form
                                        method="POST"
                                        action="eliminar.php"
                                        style="display: inline;"
                                        onsubmit="return confirm(
                                            '¿Deseas eliminar esta categoría?'
                                        );"
                                    >
                                        <?= csrf_input(); ?>

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?php
                                                echo (int) $categoria[
                                                    "id_categoria"
                                                ];
                                            ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="enlace-boton"
                                        >
                                            Eliminar
                                        </button>
                                    </form>

                                <?php else: ?>

                                    <span
                                        title="La categoría está asociada a incidentes"
                                    >
                                        En uso
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>
                    <?php endforeach; ?>

                </tbody>

            </table>

        </section>

    </main>

</div>

<?php require_once "../includes/footer.php"; ?>