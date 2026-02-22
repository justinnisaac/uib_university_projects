<?php

session_start();

//Obtengo el id del voluntario
$id_voluntario = $_GET['id'] ?? null;
$id_ayuntamiento = $_GET['ayt'] ?? null;
$id_colonia = null;

if (!$id_voluntario) {
    echo "No se recibió el ID del voluntario";
    exit();
}

// Inicialización de variables para la interfaz
$mensaje = "";
$exito = false;
$datos_tarea = [
    'nombre' => '',
    'descripcion' => '',
    'completada' => '',
    'id_voluntario' => '',
    'id_colonia' => ''
];

// 2. Conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// 2. Consulta para obtener todos los ayuntamientos
$consulta_colonia = "SELECT id_colonia FROM colonia WHERE id_ayuntamiento = '$id_ayuntamiento'";
$resultado_colonias = mysqli_query($conexion, $consulta_colonia);

// 5. Cerrar la conexión (temporalmente, se reabre en la PARTE 2 si es necesario)
mysqli_close($conexion);

// Lógica del formulario
if (isset($_POST['guardar_cambios'])) {
    
    // 1. Recoger datos del formulario
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $completada = FALSE;
    $id_colonia = $_POST['id_colonia'];

    // 2. Conexión
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 
    
    // Revisamos que no exista esta tarea previamente
    $consulta_check = "
    SELECT 1
    FROM tarea
    WHERE nombre = '$nombre'
    AND id_voluntario = '$id_voluntario'
    AND id_colonia = '$id_colonia'
    LIMIT 1
    ";

    $result_check = mysqli_query($conexion, $consulta_check);

    if (mysqli_num_rows($result_check) > 0) {
        $mensaje = "Ya existe una tarea registrada con el mismo nombre para mismos IDs";
        $exito = false;
    } else {
        
        // 3. SENTENCIA INSERT
        $consulta_create = "INSERT INTO tarea (nombre, descripcion, id_voluntario, id_colonia) 
                            VALUES  ('$nombre', 
                                    '$descripcion', 
                                    '$id_voluntario', 
                                    '$id_colonia')";

        // 4. Ejecución
        if (mysqli_query($conexion, $consulta_create)) {
            $mensaje = "Tarea creada con éxito";
            $exito = true;
        } else {
            $mensaje = "Error al crear la tarea: " . mysqli_error($conexion);
            $exito = false;
        }
        
    }

    // 5. Cerrar la conexión (temporalmente, se reabre en la PARTE 2 si es necesario)
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
    <title>Añadir tarea</title>

    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>
<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../panel_opciones_responsable.php"); ?>

    <!-- Contenido -->
    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard" style="margin-bottom: 20px;">Información sobre la tarea</h2>
            
            <?php if ($mensaje): ?>
                <p class="mensaje-alerta <?php echo $exito ? 'mensaje-exito' : 'mensaje-error'; ?>">
                    <?php echo $mensaje; ?>
                </p>
            <?php endif; ?>

            <!-- Formulario de Edición -->
            <form method="POST" class="formulario-colonia">
                
                <!-- NOMBRE (VARCHAR(100)) -->
                <label for = "descripcion">Nombre de la tarea:</label>
                <input type="text" id="nombre" name="nombre" maxlength="100" 
                       value="" required>

                <!-- DESCRIPCION (VARCHAR(255)) -->
                <label for = "descripcion">Descripcion de la tarea:</label>
                <input type="text" id="descripcion" name="descripcion" maxlength="255" 
                       value="" required>

                <!-- ID_VOLUNTARIO (INT) - NO MODIFICABLE -->
                <label for = "id_voluntario">Código identificativo del voluntario:</label>
                <input type="text" id="id_voluntario" name="id_voluntario" maxlength="20" 
                       value="<?php echo $id_voluntario ?>" readonly
                       style="background-color: #eee; cursor: not-allowed;">

                <input type="hidden" name="id_voluntario" value="<?php echo htmlspecialchars($id_voluntario); ?>">
                
                <!-- Menú desplegable para seleccionar colonia -->
                <label for = "id_voluntario">Código identificativo de la colonia:</label>
                <select name="id_colonia" class="select-campo" required>
                    <option value="">Selecciona una colonia</option>
                    <?php
                    // 3. Generar las opciones del menú desplegable con los ayuntamientos obtenidos
                    if ($resultado_colonias) {
                        while ($fila = mysqli_fetch_array($resultado_colonias)) {
                            // Usamos id_ayuntamiento como valor y mostramos el nombre
                            echo '<option value="' . htmlspecialchars($fila['id_colonia']) . '">' . 
                                 htmlspecialchars($fila['id_colonia']) . 
                                 '</option>';
                        }
                    }
                    ?>
                </select>

                <!-- Botón: "Guardar cambios" -->
                <button type="submit" name="guardar_cambios" class="boton-gestion boton-confirmar">Asignar tarea</button>
            </form>
            <!-- Botón para volver atrás -->
            <div style="margin-top: 30px; text-align: center;">
                <button class="boton-gestion" onclick="location.href='../gestionar_tareas.php'">Volver al inicio</button></td>
            </div>
        </div>
    </div>
</div>
</body>
</html>