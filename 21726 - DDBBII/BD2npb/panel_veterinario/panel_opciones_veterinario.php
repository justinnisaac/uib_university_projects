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
        Veterinario 
    </div>

    <div class="opcion-menu">
        <a href="/BD2XAMPPions/BD2npb/panel_veterinario/principal_veterinario.php">Inicio</a>
    </div>

    <div class="opcion-menu">
        <a href="/BD2XAMPPions/BD2npb/panel_veterinario/vet_campanas.php">Campañas asignadas</a>
    </div>

    <div class="opcion-menu">
        <a href="/BD2XAMPPions/BD2npb/panel_veterinario/vet_intervenciones.php">Intervenciones</a>
    </div>

    <div class="opcion-menu">
        <a href="/BD2XAMPPions/BD2imo/panel_veterinario/gestionar_solicitudes_retirada.php">Solicitudes de retirada</a>
    </div>

    <div class="opcion-menu">
        <a href="/BD2XAMPPions/BD2imo/panel_veterinario/gestionar_autopsias.php">Autopsias realizadas</a>
    </div>

    <div class="opcion-menu">
        <a href="/BD2XAMPPions/BD2imo/panel_veterinario/gestionar_cementerios.php">Cementerios</a>
    </div>

    <div class="cerrar-sesion">
        <a href="/BD2XAMPPions/BD2imo/login_registro/logout.php">Cerrar sesión</a>
    </div>


</div>

