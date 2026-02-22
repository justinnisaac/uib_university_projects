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
    <div class="nombre-rol">Ayuntamiento</div>

    <div class="opcion-menu"><a href="/BD2XAMPPions/BD2imo/panel_ayuntamiento/principal_ayuntamiento.php">Inicio</a></div>
    <div class="opcion-menu"><a href="/BD2XAMPPions/BD2imo/panel_ayuntamiento/gestionar_colonias.php">Colonias de gatos</a></div>
    <div class="opcion-menu"><a href="/BD2XAMPPions/BD2imo/panel_ayuntamiento/gestionar_grupos.php">Grupos de trabajo</a></div>
    <div class="opcion-menu"><a href="/BD2XAMPPions/BD2imo/panel_ayuntamiento/gestionar_borsin.php">Bolsa de voluntarios</a></div>
    <div class="opcion-menu"><a href="/BD2XAMPPions/BD2imo/panel_ayuntamiento/gestionar_campanas.php">Campañas</a></div>
    
    <div class="cerrar-sesion"> <a href="/BD2XAMPPions/BD2imo/login_registro/logout.php">Cerrar sesión</a> </div>
</div>