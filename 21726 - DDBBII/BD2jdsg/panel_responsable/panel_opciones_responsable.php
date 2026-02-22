<?php
// Si la sesión no está iniciada, la iniciamos para acceder a $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Usamos la variable de sesión para mostrar el usuario (si lo deseas)
$usuario_mostrar = $_SESSION['usuario'] ?? "Usuario";
?>

<!-- aquí definimos qué opciones tendrá el menú lateral del panel de ayuntamiento -->
<div class="panel-lateral">
    <div class="nombre-usuario"><?php echo htmlspecialchars($usuario_mostrar); ?></div>
    <div class="nombre-rol">Responsable</div>

    <div class="opcion-menu"><a href="/BD2XAMPPions/BD2jdsg/panel_responsable/principal_responsable.php">Inicio</a></div>
    <div class="opcion-menu"><a href="/BD2XAMPPions/BD2jdsg/panel_responsable/gestionar_tareas.php">Asignar tareas</a></div>
    <div class="opcion-menu"><a href="/BD2XAMPPions/BD2jdsg/panel_responsable/gestionar_colonias.php">Colonias felinas</a></div>
    <div class="opcion-menu"><a href="/BD2XAMPPions/BD2jdsg/panel_responsable/gestionar_visitas_incidencias.php">Visitas e incidencias</a></div>
    <div class="opcion-menu"><a href="/BD2XAMPPions/BD2jdsg/panel_responsable/gestionar_avistamientos.php">Avistamientos</a></div>
    <div class="opcion-menu"><a href="/BD2XAMPPions/BD2jdsg/panel_responsable/gestionar_retirada.php">Solicitar retirada</a></div>
    <div class="opcion-menu"><a href="/BD2XAMPPions/BD2jdsg/panel_responsable/visualizar_centros_veterinarios.php">Centros veterinarios</a></div>
    <div class="opcion-menu"><a href="/BD2XAMPPions/BD2jdsg/panel_responsable/gestionar_campanas.php">Campañas</a></div>
    
    <div class="cerrar-sesion"> <a href="/BD2XAMPPions/BD2imo/login_registro/logout.php">Cerrar sesión</a> </div>
</div>