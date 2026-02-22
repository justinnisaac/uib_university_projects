<?php

session_start();

//Obtengo el id de la colonia a base de la anterior pantalla
$id_colonia = $_GET['id'] ?? null;

if (!$id_colonia) {
    echo "No se recibió el ID de la colonia.";
    exit();
}

//Obtengo el id del responsable
$id_responsable = (int) $_SESSION["id_usuario"];

// Inicialización de variables para la interfaz
$mensaje = "";
$exito = false;
$datos_visita = [
    'fecha_visita' => '',
    'comentarios' => '',
    'id_responsable' => '',
    'id_colonia' => ''
];

// Lógica del formulario
if (isset($_POST['guardar_cambios'])) {
    
    // 1. Recoger datos del formulario
    $fecha_visita = $_POST['fecha_visita'];
    $comentarios = $_POST['comentarios'];

    // 2. Conexión
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 
    
    // Para manejar campos opcionales
    $comentarios_db = empty($comentarios) ? "NULL" : "'$comentarios'";
    
    $consulta_check = "
    SELECT 1
    FROM visita
    WHERE fecha_visita = '$fecha_visita'
    AND id_colonia = '$id_colonia'
    AND id_responsable = '$id_responsable'
    LIMIT 1
    ";

    $result_check = mysqli_query($conexion, $consulta_check);

    if (mysqli_num_rows($result_check) > 0) {
        $mensaje = "Ya existe una visita registrada para esta colonia en esa fecha.";
        $exito = false;
    } else {
        
        // 3. SENTENCIA INSERT
        $consulta_create = "INSERT INTO visita (fecha_visita, comentarios, id_responsable, id_colonia) 
                            VALUES  ('$fecha_visita', 
                                    $comentarios_db, 
                                    '$id_responsable', 
                                    '$id_colonia')";

        // 4. Ejecución
        if (mysqli_query($conexion, $consulta_create)) {
            $mensaje = "Visita creada con éxito";
            $exito = true;
        } else {
            $mensaje = "Error al crear la visita: " . mysqli_error($conexion);
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
    <title>Añadir visita - formulario</title>

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

            <h2 class="titulo-dashboard" style="margin-bottom: 20px;">Información sobre la visita</h2>
            
            <?php if ($mensaje): ?>
                <p class="mensaje-alerta <?php echo $exito ? 'mensaje-exito' : 'mensaje-error'; ?>">
                    <?php echo $mensaje; ?>
                </p>
            <?php endif; ?>

            <!-- Formulario de Edición -->
            <form method="POST" class="formulario-colonia">
                
                <!-- FECHA (DATE) -->
                <label for = "fecha_visita">Fecha de la visita:</label>
                <input type="date" id="fecha_visita" name="fecha_visita"
                       value="" required>
                
                <!-- COMENTARIOS (VARCHAR(255)) - Opcional -->
                <label for = "comentarios">Comentarios sobre la visita:</label>
                <input type="text" id="comentarios" name="comentarios" maxlength="255" 
                       value="">

                <!-- ID_RESPONSABLE (VARCHAR(20)) - NO MODIFICABLE -->
                <label for = "id_responsable">Código identificativo del responsable:</label>
                <input type="text" id="id_responsable" name="id_responsable" maxlength="20" 
                       value="<?php echo $id_responsable ?>" readonly
                       style="background-color: #eee; cursor: not-allowed;">

                <input type="hidden" name="id_responsable" value="<?php echo htmlspecialchars($id_responsable); ?>">
                
                <!-- ID_COLONIA (VARCHAR(20)) - NO MODIFICABLE -->
                <label for = "id_colonia">Código identificativo de la colonia:</label>
                <input type="text" id="id_colonia" name="id_colonia" maxlength="20" 
                       value= "<?php echo $id_colonia ?>" readonly
                       style="background-color: #eee; cursor: not-allowed;">

                <input type="hidden" name="id_colonia" value="<?php echo htmlspecialchars($id_colonia); ?>">
                
                <!-- Botón: "Guardar cambios" -->
                <button type="submit" name="guardar_cambios" class="boton-gestion boton-confirmar">Confirmar datos</button>
            </form>
            <div style="margin-top: 30px; text-align: center;">
                <button class="boton-gestion" onclick="location.href='anadir_visita.php'">Volver atrás</button></td>
            </div>
        </div>
    </div>
</div>
</body>
</html>