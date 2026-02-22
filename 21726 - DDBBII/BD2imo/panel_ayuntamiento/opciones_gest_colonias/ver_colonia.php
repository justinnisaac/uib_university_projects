<?php

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

// Lógica para obtener los datos de la colonia desde la BBDD

// 1. Verificar si se ha pasado un ID
if (isset($_GET["id"])) {
    $id_colonia_actual = $_GET["id"];
    
    // 2. Conexión a la BBDD
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 
    
    // 3. Crear la sentencia SELECT
    // Usamos el campo id_colonia para buscar el registro específico
    $consulta_select = "SELECT id_colonia, nombre_colonia, coordenadas_GPS, descripción_ubicación, comentarios 
                        FROM colonia 
                        WHERE id_colonia = '$id_colonia_actual'";

    // 4. Ejecutar la consulta
    $resultado = mysqli_query($conexion, $consulta_select);

    // 5. Verificar y obtener los resultados
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $datos_colonia = mysqli_fetch_assoc($resultado);
        $colonia_encontrada = true;
    } else {
        $mensaje_error = "Error: No se encontró la colonia con el ID: " . htmlspecialchars($id_colonia_actual);
    }
    
    // 6. Cerrar la conexión
    mysqli_close($conexion);
    
} else {
    $mensaje_error = "Error: No se ha especificado el ID de la colonia a visualizar.";
}

// Variables de la colonia, con valores predeterminados o los obtenidos de la BBDD
$id = htmlspecialchars($datos_colonia['id_colonia']);
$nombre = htmlspecialchars($datos_colonia['nombre_colonia']);
$coordenadas = htmlspecialchars($datos_colonia['coordenadas_GPS']);
$descripcion = htmlspecialchars($datos_colonia['descripción_ubicación']);
$comentarios = htmlspecialchars($datos_colonia['comentarios']);

$usuario = "";
if (isset($_POST["usuario"])) {
    $usuario = $_POST["usuario"];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ver Colonia: <?php echo $id; ?></title>

    <link rel="stylesheet" href="../../estilo/estilo_panel.css"> 
    <link rel="stylesheet" href="../../estilo/estilo_contenido.css">
    
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../panel_opciones.php"); ?>

    <!-- Contenido principal -->
    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Visualizar Colonia #<?php echo $id; ?></h2>

            <!-- Mensajes de Error (si el ID no se encuentra) -->
            <?php if (!empty($mensaje_error)) : ?>
                <p class="mensaje-alerta mensaje-error" style="text-align: center; margin-bottom: 20px;">
                    <?php echo $mensaje_error; ?>
                </p>
            <?php endif; ?>

            <!-- Formulario (solo para visualización, nada de editarr) -->
            <?php if ($colonia_encontrada) : ?>
            
                <div class="formulario-colonia">
                    
                    <!-- ID DE LA COLONIA (VARCHAR(20)) -->
                    <label for="id_colonia">Código de la colonia:</label>
                    <input type="text" id="id_colonia" name="id_colonia" value="<?php echo $id; ?>" 
                           maxlength="20" required readonly>
                    
                    <!-- NOMBRE (VARCHAR(100)) -->
                    <label for="nombre_colonia">Nombre de la colonia:</label>
                    <input type="text" id="nombre_colonia" name="nombre_colonia" value="<?php echo $nombre; ?>" 
                           maxlength="100" required readonly>
                    
                    <!-- COORDENADAS GPS (VARCHAR(100)) -->
                    <label for="coordenadas_GPS">Coordenadas GPS:</label>
                    <input type="text" id="coordenadas_GPS" name="coordenadas_GPS" value="<?php echo $coordenadas; ?>"
                           maxlength="100" required readonly>
                    
                    <!-- DESCRIPCIÓN (VARCHAR(255)) - Opcional -->
                    <label for="descripcion_ubicacion">Descripción de la ubicación:</label>
                    <input type="text" id="descripcion_ubicacion" name="descripcion_ubicacion" value="<?php echo $descripcion; ?>"
                           maxlength="255" readonly>
                    
                    <!-- COMENTARIOS (VARCHAR(255)) - Opcional -->
                    <label for="comentarios">Comentarios:</label>
                    <input type="text" id="comentarios" name="comentarios" value="<?php echo $comentarios; ?>"
                           maxlength="255" readonly>
                           
                    <!-- Contenedor para los dos botones de acción -->
                    <div style="display: flex; gap: 20px; margin-top: 30px;">
                        
                        <!-- Botón: Ver Gatos -->
                        <button type="button" 
                                class="boton-gestion boton-confirmar" 
                                onclick="location.href='ver_todos_gatos.php?id=<?php echo $id; ?>'"
                                style="flex-grow: 1;">
                            Ver los gatos de esta colonia
                        </button>

                        <!-- Botón Volver -->
                        <button type="button" 
                                class="boton-gestion boton-confirmar" 
                                onclick="location.href='../gestionar_colonias.php'"
                                style="flex-grow: 1;">
                            Volver al listado de colonias
                        </button>

                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>