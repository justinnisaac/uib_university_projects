<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../BD2imo/login_registro/login.php");
    exit();
}

$usuario = $_SESSION["usuario"];
$id_usuario = $_SESSION["id_usuario"];

// Conexión BBDD
$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

// --- 1. OBTENER MUNICIPIO (ID y NOMBRE) Y GUARDAR EN SESIÓN ---
$id_municipio_vet = "";
$nombre_municipio = "su municipio"; // Valor por defecto

// Verificamos si YA tenemos los datos en la sesión para ahorrar consultas
if (isset($_SESSION['id_municipio_vet']) && isset($_SESSION['nombre_municipio_vet'])) {
    $id_municipio_vet = $_SESSION['id_municipio_vet'];
    $nombre_municipio = $_SESSION['nombre_municipio_vet'];
} else {
    // Si no están en sesión, hacemos una consulta única con JOIN a municipio
    $consulta_datos_muni = "
        SELECT cv.id_municipio, m.nombre AS nombre_muni
        FROM veterinario v
        JOIN centro_veterinario cv ON v.id_centro = cv.id_centro
        JOIN municipio m ON cv.id_municipio = m.id_municipio
        AND v.id_veterinario = '$id_usuario'
    ";
    
    $res_muni = mysqli_query($conexion, $consulta_datos_muni);
    
    if ($fila = mysqli_fetch_array($res_muni)) {
        $id_municipio_vet = $fila['id_municipio'];
        $nombre_municipio = $fila['nombre_muni'];
        
        // ¡AQUÍ ESTÁ EL CAMBIO! Guardamos ambos en la sesión
        $_SESSION['id_municipio_vet'] = $id_municipio_vet;
        $_SESSION['nombre_municipio_vet'] = $nombre_municipio;
    }
}

// --- 2. CONTADORES ---
$num_campanas = 0;
$num_intervenciones = 0;
$num_solicitudes_pendientes = 0;

// Contar campañas
$consulta_campanas = "
    SELECT COUNT(p.id_campana) AS total
    FROM participacion p
    WHERE p.id_veterinario = '$id_usuario'
";
$res_campanas = mysqli_query($conexion, $consulta_campanas);
if ($fila = mysqli_fetch_array($res_campanas)) {
    $num_campanas = $fila["total"];
}

// Contar intervenciones
$consulta_intervenciones = "
    SELECT COUNT(va.id_intervencion) AS total
    FROM veterinario_accion va
    WHERE va.id_veterinario = '$id_usuario'
";
$res_intervenciones = mysqli_query($conexion, $consulta_intervenciones);
if ($fila = mysqli_fetch_array($res_intervenciones)) {
    $num_intervenciones = $fila["total"];
}

// --- 3. CONTAR SOLICITUDES PENDIENTES EN EL MUNICIPIO ---
if (!empty($id_municipio_vet)) {
    $consulta_pendientes = "
        SELECT COUNT(s.id_solicitud) AS total
        FROM solicitud_retirada s
        JOIN voluntario v ON s.id_responsable = v.id_voluntario
        JOIN borsin_voluntarios b ON v.id_borsin = b.id_borsin
        JOIN ayuntamiento a ON b.id_ayuntamiento = a.id_ayuntamiento
        AND a.id_municipio = '$id_municipio_vet' 
          AND s.aprobada = FALSE
    ";
    $res_pendientes = mysqli_query($conexion, $consulta_pendientes);
    if ($fila = mysqli_fetch_array($res_pendientes)) {
        $num_solicitudes_pendientes = $fila["total"];
    }
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Panel Veterinario</title>

    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <?php include("panel_opciones_veterinario.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">
            
             <!-- ICONO CONFIGURACIÓN -->
            <div style="display:flex; justify-content:flex-end;">
                <img src="../../BD2imo/estilo/ajustes.jpg"
                    alt="Perfil"
                    title="Ver perfil"
                    style="width:30px; cursor:pointer;"
                    onclick="location.href='ver_veterinario.php'">
            </div>

            <h2 class="titulo-dashboard">
                Panel del veterinario <?php echo htmlspecialchars($usuario); ?>
            </h2>

            <div class="grid-estadisticas">

                <div class="caja-estado">
                    <div class="numero-grande"><?php echo $num_campanas; ?></div>
                    <div class="texto-caja">Campañas asignadas</div>
                    <button class="boton-gestion"
                            onclick="location.href='vet_campanas.php'">
                        Ver campañas
                    </button>
                </div>
                
                <div class="caja-estado">
                    <div class="numero-grande"><?php echo $num_intervenciones; ?></div>
                    <div class="texto-caja">Intervenciones realizadas</div>
                    <button class="boton-gestion"
                            onclick="location.href='vet_intervenciones.php'">
                        Ver intervenciones
                    </button>
                </div>

                <!-- CAJA SOLICITUDES -->
                <div class="caja-estado">
                    <!-- Número dinámico de pendientes -->
                    <div class="numero-grande" style="<?php echo ($num_solicitudes_pendientes > 0) ? 'color: #e65100;' : ''; ?>">
                        <?php echo $num_solicitudes_pendientes; ?>
                    </div> 
                    
                    <!-- Texto dinámico con el nombre del municipio (ahora desde sesión) -->
                    <div class="texto-caja" style="font-size: 16px;">
                        Solicitudes de retirada pendientes en tu municipio (<?php echo htmlspecialchars($nombre_municipio); ?>)
                    </div>
                    
                    <button class="boton-gestion"
                            onclick="location.href='../../BD2imo/panel_veterinario/gestionar_solicitudes_retirada.php'">
                        Ver solicitudes
                    </button>
                </div>

            </div>

        </div>
    </div>
</div>

</body>
</html>