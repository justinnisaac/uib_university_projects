<?php

session_start();

//Obtengo el id del gato y de la visita
$id_gato = $_GET['id'] ?? null;
$id_colonia = null;
$id_ayuntamiento = $_GET['ayt'] ?? null;

if (!$id_gato || !$id_ayuntamiento) {
    echo "No se recibió el ID del gato o del ayuntamiento";
    exit();
}

// Inicialización de variables para la interfaz
$mensaje = "";
$exito = false;
$comentarios = "";
$datos_visita = [
    'id_colonia' => '',
    'id_gato' => '',
    'fecha' => '',
    'comentarios' => ''
];

// 2. Conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// 2. Consulta para obtener todas las colonias de este ayuntamiento
$consulta_colonia = "SELECT id_colonia FROM colonia WHERE id_ayuntamiento = '$id_ayuntamiento'";
$resultado_colonias = mysqli_query($conexion, $consulta_colonia);

// 5. Cerrar la conexión (temporalmente, se reabre en la PARTE 2 si es necesario)
mysqli_close($conexion);

// Lógica del formulario
if (isset($_POST['guardar_cambios'])) {
    
    // 1. Recoger datos del formulario
    $fecha = $_POST['fecha'];
    $comentarios = $_POST['comentarios'];
    $id_colonia = $_POST['id_colonia'];

    // 2. Conexión
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 
    
    $consulta_check = "
    SELECT 1
    FROM avistamiento
    WHERE id_colonia = '$id_colonia'
    AND id_gato = '$id_gato'
    AND fecha >= '$fecha'
    LIMIT 1
    ";

    $result_check = mysqli_query($conexion, $consulta_check);

    if (mysqli_num_rows($result_check) > 0) {
        $mensaje = "Ya existe un avistamiento registrado posterior para este gato en esta colonia" ;
        $exito = false;
    } else {
        
        // 3. SENTENCIA INSERT
        // ESTA SENTENCIA INSERT TIENE ENLAZADO UN TRIGGER DE LA BBDD
        $consulta_create = "INSERT INTO avistamiento (id_colonia, id_gato, fecha, comentarios) 
                            VALUES  ('$id_colonia', 
                                    '$id_gato', 
                                    '$fecha', 
                                    '$comentarios')";

        // 4. Ejecución
        if (mysqli_query($conexion, $consulta_create)) {
            $mensaje = "Avistamiento creado con éxito";
            $exito = true;
        } else {
            $mensaje = "Error al crear el avistamiento: " . mysqli_error($conexion);
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
    <title>Añadir avistamiento - Formulario</title>

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

            <h2 class="titulo-dashboard" style="margin-bottom: 20px;">Información sobre el avistamiento</h2>
            
            <?php if ($mensaje): ?>
                <p class="mensaje-alerta <?php echo $exito ? 'mensaje-exito' : 'mensaje-error'; ?>">
                    <?php echo $mensaje; ?>
                </p>
            <?php endif; ?>

            <!-- Formulario de Edición -->
            <form method="POST" class="formulario-colonia">
                
                <!-- FECHA DATE -->
                <label for = "fecha">Fecha del avistamiento:</label>
                <input type="date" id="fecha" name="fecha" maxlength="255" 
                       value="" required>
                
                <!-- COMENTARIOS (VARCHAR(255)) -->
                <label for = "comentarios">Comentarios del avistamiento:</label>
                <input type="text" id="comentarios" name="comentarios" maxlength="255" 
                       value="" required>

                <!-- Menú desplegable para seleccionar colonia -->
                <label for = "id_colonia">Código identificativo de la colonia:</label>
                <select name="id_colonia" class="select-campo" required>
                    <option value="">Selecciona una colonia</option>
                    <?php
                    // 3. Generar las opciones del menú desplegable con las colonias obtenidas
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
                
                <!-- ID_GATO (VARCHAR(20)) - NO MODIFICABLE -->
                <label for = "id_gato">Código identificativo del gato:</label>
                <input type="text" id="id_gato" name="id_gato" maxlength="20" 
                       value= "<?php echo $id_gato ?>" readonly
                       style="background-color: #eee; cursor: not-allowed;">

                <input type="hidden" name="id_gato" value="<?php echo htmlspecialchars($id_gato); ?>">
                
                <!-- Botón: "Guardar cambios" -->
                <button type="submit" name="guardar_cambios" class="boton-gestion boton-confirmar">Confirmar datos</button>
            </form>
            <div style="margin-top: 30px; text-align: center;">
                <button class="boton-gestion" onclick="location.href='anadir_avistamiento.php'">Volver atrás</button></td>
            </div>
        </div>
    </div>
</div>
</body>
</html>