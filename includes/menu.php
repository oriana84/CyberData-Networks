<aside class="sidebar">
    <div class="sidebar-cabecera">
        <div>
            <div class="marca-menu">CYBERDATA NETWORKS</div>
            <h2>Menú Lateral</h2>
        </div>
        <label class="menu-toggle-label" for="menu-toggle" aria-label="Abrir o cerrar menú">☰</label>
    </div>
    <input type="checkbox" id="menu-toggle" class="menu-toggle">
    <nav class="menu-navegacion">
        <a href="/CyberData/dashboard.php">🏠 Dashboard Inicio</a>

        <?php if ((int) ($_SESSION["rol_id"] ?? 0) === 1): ?>
            <a href="/CyberData/usuarios/listar.php">⚙️ Control Usuarios</a>
            <a href="/CyberData/clientes/listar.php">🏢 Gestión Clientes</a>
            <a href="/CyberData/categorias/listar.php">🗂️ Gestión Categorías</a>
        <?php endif; ?>

        <div class="titulo-submenu" aria-label="Centro de Incidentes">🛡️ Centro Incidentes</div>
        <div class="submenu">
            <?php if (in_array((int) ($_SESSION["rol_id"] ?? 0), [1, 2], true)): ?>
                <a href="/CyberData/incidentes/agregar.php">☁️ Ingesta Logs</a>
            <?php endif; ?>
            <a href="/CyberData/incidentes/listar.php">
                <?= (int) ($_SESSION["rol_id"] ?? 0) === 3 ? "🔍 Mis Incidentes" : "🔍 Filtrar Historial" ?>
            </a>
        </div>
        <a href="/CyberData/logout.php">↪️ Cerrar Sesión</a>
    </nav>
</aside>
