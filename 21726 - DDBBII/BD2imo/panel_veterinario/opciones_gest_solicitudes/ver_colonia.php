<?php
session_start();

// Seguridad: Verificar sesión de veterinario
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../login_registro/login.php");
    exit();
}

$id_colonia_actual = "";
$datos_colonia = [
    'id_colonia' => '',
    'nombre_colonia' => '',
    'coordenadas_GPS' => '',
    'descripción_ubicación' => '', 
    'comentarios' => ''
];
$mensaje_error = "";
$colonia_encontrada = false;

// 1. Verificar si se ha pasado un ID
if (isset($_GET["id"])) {
    $id_colonia_actual = $_GET["id"];
    
    // 2. Conexión
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 
    
    // 3. Consulta SELECT
    $consulta_select = "SELECT id_colonia, nombre_colonia, coordenadas_GPS, descripción_ubicación, comentarios 
                        FROM colonia 
                        WHERE id_colonia = '$id_colonia_actual'";

    $resultado = mysqli_query($conexion, $consulta_select);

    if ($registro = mysqli_fetch_assoc($resultado)) {
        $datos_colonia = $registro;
        $colonia_encontrada = true;
    } else {
        $mensaje_error = "Error: Colonia con ID '$id_colonia_actual' no encontrada.";
    }

    mysqli_close($conexion);
    
} else {
    $mensaje_error = "Error: No se ha especificado el ID de la colonia.";
}

// Datos que se mostrarán en el formulario
$id = htmlspecialchars($datos_colonia['id_colonia']);
$nombre = htmlspecialchars($datos_colonia['nombre_colonia']);
$coordenadas = htmlspecialchars($datos_colonia['coordenadas_GPS']);
$descripcion = htmlspecialchars($datos_colonia['descripción_ubicación'] ?? '');
$comentarios = htmlspecialchars($datos_colonia['comentarios'] ?? '');

$titulo_pagina = $colonia_encontrada ? "Detalles de: " . $nombre : "Detalles de la Colonia";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo_pagina; ?></title>
    <link rel="stylesheet" href="../../estilo/estilo_panel.css"> 
    <link rel="stylesheet" href="../../estilo/estilo_contenido.css">
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../../../BD2npb/panel_veterinario/panel_opciones_veterinario.php"); ?>

    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Visualizar <?php echo $nombre; ?> #<?php echo $id; ?></h2>
            
            <?php if (!empty($mensaje_error)) : ?>
                <p class="mensaje-alerta mensaje-error" style="text-align: center; margin-bottom: 20px;">
                    <?php echo $mensaje_error; ?>
                </p>
            <?php endif; ?>

            <?php if ($colonia_encontrada) : ?>
            
                <div class="formulario-colonia">
                    
                    <label for="id_colonia">Código identificativo:</label>
                    <input type="text" id="id_colonia" name="id_colonia" value="<?php echo $id; ?>" 
                           maxlength="10" required readonly>
                    
                    <label for="nombre_colonia">Nombre de la colonia:</label>
                    <input type="text" id="nombre_colonia" name="nombre_colonia" value="<?php echo $nombre; ?>" 
                           maxlength="100" required readonly>
                    
                    <label for="coordenadas_GPS">Coordenadas GPS:</label>
                    <input type="text" id="coordenadas_GPS" name="coordenadas_GPS" value="<?php echo $coordenadas; ?>"
                           maxlength="100" required readonly>
                    
                    <label for="descripcion_ubicacion">Descripción de la ubicación:</label>
                    <input type="text" id="descripcion_ubicacion" name="descripcion_ubicacion" value="<?php echo $descripcion; ?>"
                           maxlength="255" readonly>
                    
                    <label for="comentarios">Comentarios:</label>
                    <input type="text" id="comentarios" name="comentarios" value="<?php echo $comentarios; ?>"
                           maxlength="255" readonly>
                    
                    <button type="button" 
                            class="boton-gestion boton-confirmar" 
                            onclick="location.href='../gestionar_solicitudes_retirada.php'">
                        Volver a solicitudes
                    </button>

                </div>
            
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>