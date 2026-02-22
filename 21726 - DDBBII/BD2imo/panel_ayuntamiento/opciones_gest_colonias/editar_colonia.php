<?php
// Inicialización de variables para la interfaz
$mensaje = "";
$exito = false;
$id_colonia_a_editar = '';
$datos_colonia = [
    'id_colonia' => '',
    'nombre_colonia' => '',
    'coordenadas_GPS' => '',
    'descripción_ubicación' => '',
    'comentarios' => ''
];

// Lógica del formulario
if (isset($_POST['guardar_cambios'])) {
    
    // 1. Recoger datos del formulario
    $id_colonia_a_editar = $_POST['id_colonia'];
    $nombre = $_POST['nombre_colonia'];
    $coordenadas = $_POST['coordenadas_GPS'];
    $descripcion = $_POST['descripcion_ubicacion'];
    $comentarios = $_POST['comentarios'];

    // 2. Conexión
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 
    
    // Para manejar campos opcionales
    $descripcion_db = empty($descripcion) ? "NULL" : "'$descripcion'";
    $comentarios_db = empty($comentarios) ? "NULL" : "'$comentarios'";
    
    // 3. Comprobar que no exista otra colonia con las mismas coordenadas
    $consulta_check = "
        SELECT 1
        FROM colonia
        WHERE coordenadas_GPS = '$coordenadas'
          AND id_colonia <> '$id_colonia_a_editar'
        LIMIT 1
    ";
    $resultado_check = mysqli_query($conexion, $consulta_check);

    if (mysqli_num_rows($resultado_check) > 0) {
        // Hay otra colonia en esas coordenadas: cancelar actualización
        $mensaje = "Ya existe otra colonia registrada en esas coordenadas GPS. No se permiten duplicados en el mismo lugar.";
        $exito = false;
    } else {
        // 4. Crear la sentencia UPDATE y ejecutarla sólo si no hay duplicado
        $consulta_update = "UPDATE colonia 
                            SET nombre_colonia='$nombre', 
                                coordenadas_GPS='$coordenadas', 
                                descripción_ubicación=$descripcion_db, 
                                comentarios=$comentarios_db
                            WHERE id_colonia='$id_colonia_a_editar'";

        if (mysqli_query($conexion, $consulta_update)) {
            $mensaje = "Colonia '$nombre' actualizada con éxito.";
            $exito = true;
        } else {
            $mensaje = "Error al actualizar la colonia: " . mysqli_error($conexion);
            $exito = false;
        }
    }
    
    // 5. Cerrar la conexión (temporalmente, se reabre en la PARTE 2 si es necesario)
    mysqli_close($conexion);

} 
// Lógica de carga inicial de datos (Cuando no se ha enviado el formulario)
// O después de un UPDATE exitoso para recargar los datos
if (isset($_GET['id']) || isset($_POST['id_colonia'])) {
    
    // Obtener el ID de la colonia
    $id_colonia_a_editar = isset($_GET['id']) ? $_GET['id'] : $_POST['id_colonia'];
    
    // 1. Conexión
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 

    // 2. Crear la sentencia SELECT
    $consulta_select = "SELECT * FROM colonia WHERE id_colonia='$id_colonia_a_editar'";

    // 3. Ejecución del SELECT
    $resultado = mysqli_query($conexion, $consulta_select);
    
    if ($registro = mysqli_fetch_array($resultado)) {
        // Cargar los datos del registro
        $datos_colonia['id_colonia'] = $registro['id_colonia'];
        $datos_colonia['nombre_colonia'] = $registro['nombre_colonia'];
        $datos_colonia['coordenadas_GPS'] = $registro['coordenadas_GPS'];
        $datos_colonia['descripción_ubicación'] = $registro['descripción_ubicación'];
        $datos_colonia['comentarios'] = $registro['comentarios'];
        
    } else {
        // Mensaje si no se encuentra el registro
        $mensaje = "Error: Colonia con ID '$id_colonia_a_editar' no encontrada.";
        $exito = false;
    }

    // 4. Cerrar la conexión
    mysqli_close($conexion);
}


$usuario = "";
if (isset($_POST["usuario"])) {
    $usuario = $_POST["usuario"];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Editar Colonia</title>

    <link rel="stylesheet" href="../../estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../estilo/estilo_contenido.css">
</head>

<body>
<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../panel_opciones.php"); ?>

    <!-- Contenido -->
    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard" style="margin-bottom: 20px;">Editar información de la colonia</h2>
            
            <?php if ($mensaje): 
                $clase_alerta = $exito ? 'mensaje-exito' : 'mensaje-error';
            ?>
                <!-- Mostrar mensaje de éxito o error -->
                <p class="mensaje-alerta <?php echo $clase_alerta; ?>">
                    <?php echo $mensaje; ?>
                </p>
            <?php endif; ?>

            <!-- Formulario de Edición -->
            <form action="editar_colonia.php?id=<?php echo $datos_colonia['id_colonia']; ?>" method="POST" class="formulario-colonia">
                
                <!-- ID/CÓDIGO (VARCHAR(10)) - NO MODIFICABLE -->
                <label for="id_colonia">Código identificativo de la colonia:</label>
                <input type="text" id="id_colonia" name="id_colonia" maxlength="10" 
                       value="<?php echo htmlspecialchars($datos_colonia['id_colonia']); ?>" 
                       readonly 
                       style="background-color: #eee; cursor: not-allowed;">
                
                <input type="hidden" name="id_colonia" value="<?php echo htmlspecialchars($datos_colonia['id_colonia']); ?>">

                <!-- NOMBRE (VARCHAR(100)) -->
                <label for="nombre_colonia">Nombre de la colonia:</label>
                <input type="text" id="nombre_colonia" name="nombre_colonia" maxlength="100" 
                       value="<?php echo htmlspecialchars($datos_colonia['nombre_colonia']); ?>" 
                       required>
                
                <!-- COORDENADAS GPS (VARCHAR(100)) -->
                <label for="coordenadas_GPS">Coordenadas GPS:</label>
                <input type="text" id="coordenadas_GPS" name="coordenadas_GPS" maxlength="100" 
                       value="<?php echo htmlspecialchars($datos_colonia['coordenadas_GPS']); ?>" 
                       required>
                
                <!-- DESCRIPCIÓN (VARCHAR(255)) - Opcional -->
                <label for="descripcion_ubicacion">Descripción de la ubicación:</label>
                <input type="text" id="descripcion_ubicacion" name="descripcion_ubicacion" maxlength="255" 
                       value="<?php echo htmlspecialchars($datos_colonia['descripción_ubicación']); ?>">
                
                <!-- COMENTARIOS (VARCHAR(255)) - Opcional -->
                <label for="comentarios">Comentarios:</label>
                <input type="text" id="comentarios" name="comentarios" maxlength="255" 
                       value="<?php echo htmlspecialchars($datos_colonia['comentarios']); ?>">
                
                <!-- Botón: "Guardar cambios" -->
                <button type="submit" name="guardar_cambios" class="boton-gestion boton-confirmar">Guardar cambios</button>

                <!-- Botón Volver -->
                <button type="button" 
                        class="boton-gestion boton-confirmar" 
                        onclick="location.href='../gestionar_colonias.php'"
                        style="flex-grow: 1;">
                    Cancelar
                </button>
            </form>

        </div>
    </div>
</div>
</body>
</html>