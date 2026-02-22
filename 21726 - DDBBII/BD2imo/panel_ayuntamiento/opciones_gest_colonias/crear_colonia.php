<?php
// Iniciamos la sesión
session_start();

$mensaje = ""; // Variable para mostrar mensajes al usuario
$exito = false; // Bandera para determinar el color del mensaje

// Verificamos que el ayuntamiento esté logueado
if (!isset($_SESSION["id_ayuntamiento"])) {
    header("Location: ../../login_registro/login.php"); 
    exit();
}

// Obtenemos el ID del ayuntamiento para la FK
$id_ayuntamiento_fk = $_SESSION["id_ayuntamiento"];

// Lógica de inserción al recibir datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Recoger datos del formulario
    $nombre = $_POST['nombre_colonia'];
    $coordenadas = $_POST['coordenadas_GPS'];
    $descripcion = $_POST['descripcion_ubicacion'];
    $comentarios = $_POST['comentarios'];

    // Para manejar campos opcionales
    $descripcion_db = empty($descripcion) ? "NULL" : "'$descripcion'";
    $comentarios_db = empty($comentarios) ? "NULL" : "'$comentarios'";
    
    // 2. Conexión BBDD
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 

    // 3. Comprobar si ya existe una colonia en esas coordenadas
    $consulta_check = "
        SELECT 1
        FROM colonia
        WHERE coordenadas_GPS = '$coordenadas'
        LIMIT 1
    ";
    $resultado_check = mysqli_query($conexion, $consulta_check);

    if (mysqli_num_rows($resultado_check) > 0) {
        // Ya existe una colonia en esas coordenadas: no insertar
        $mensaje = "Ya existe una colonia registrada en esas coordenadas GPS. No se pueden duplicar colonias en el mismo lugar.";
        $exito = false;
    } else {
        // 4. Crear y ejecutar INSERT sólo si no hay duplicado
        $consulta_insert = "INSERT INTO colonia 
                            SET nombre_colonia='$nombre', 
                                coordenadas_GPS='$coordenadas', 
                                descripción_ubicación=$descripcion_db, 
                                comentarios=$comentarios_db,
                                id_ayuntamiento='$id_ayuntamiento_fk'";

        if (mysqli_query($conexion, $consulta_insert)) {
            $mensaje = "Colonia '$nombre' creada con éxito y asociada al Ayto. $id_ayuntamiento_fk.";
            $exito = true;
            // Limpiar variables del formulario si la inserción fue exitosa (ayuda visualmente)
            $nombre = $coordenadas = $descripcion = $comentarios = '';
        } else {
            $mensaje = "Error al crear la colonia: " . mysqli_error($conexion);
            $exito = false;
        }
    }
    // 5. Cerrar la conexión
    mysqli_close($conexion);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Crear Colonia</title>

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

            <h2 class="titulo-dashboard" style="margin-bottom: 20px;">Información de la nueva colonia</h2>
            
            <?php if ($mensaje): 
                $clase_alerta = $exito ? 'mensaje-exito' : 'mensaje-error';
            ?>
                <!-- Mostrar mensaje de éxito o error -->
                <p class="mensaje-alerta <?php echo $clase_alerta; ?>">
                    <?php echo $mensaje; ?>
                </p>
            <?php endif; ?>

            <!-- Formulario de Creación -->
            <form action="crear_colonia.php" method="POST" class="formulario-colonia">
                
                <!-- NOMBRE (VARCHAR(100)) -->
                <label for="nombre_colonia">Nombre de la colonia:</label>
                <input type="text" id="nombre_colonia" name="nombre_colonia" maxlength="100" placeholder="Ej: Gatos del Parque Central" required>
                
                <!-- COORDENADAS GPS (VARCHAR(100)) -->
                <label for="coordenadas_GPS">Coordenadas GPS:</label>
                <input type="text" id="coordenadas_GPS" name="coordenadas_GPS" maxlength="100" placeholder="Ej: 39.5712, 2.6491" required>
                
                <!-- DESCRIPCIÓN (VARCHAR(255)) - Opcional -->
                <label for="descripcion_ubicacion">Descripción de la ubicación:</label>
                <input type="text" id="descripcion_ubicacion" name="descripcion_ubicacion" maxlength="255" placeholder="Ej: Detrás de la biblioteca, junto al árbol grande">
                
                <!-- COMENTARIOS (VARCHAR(255)) - Opcional -->
                <label for="comentarios">Comentarios:</label>
                <input type="text" id="comentarios" name="comentarios" maxlength="255" placeholder="Ej: Se alimenta todos los días a las 18:00">
                
                <!-- Botón Confirmar -->
                <button type="submit" class="boton-gestion boton-confirmar">Confirmar</button>

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