
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_mostrar = $_SESSION['usuario'] ?? "Usuario";
?>

<div class="panel-lateral">

    <div class="nombre-usuario">
        <?php echo htmlspecialchars($usuario_mostrar); ?>
    </div>

    <div class="nombre-rol">
        Voluntario
    </div>

    <div class="opcion-menu"><a href="principal_voluntario.php">Inicio</a></div>
    <div class="opcion-menu"><a href="vol_grupo.php">Mi grupo</a></div>
    <div class="opcion-menu"><a href="vol_colonias.php">Ver colonias asociadas a mi ayuntamiento</a></div>
    <div class="opcion-menu"><a href="vol_tareas.php">Ver mis tareas</a></div>

    <div class="cerrar-sesion">
        <a href="../../BD2imo/login_registro/logout.php">Cerrar sesión</a>
    </div>

</div>

    