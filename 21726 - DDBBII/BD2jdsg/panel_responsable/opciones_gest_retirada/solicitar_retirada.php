<?php

session_start();

//Obtengo el id del gato y de la visita
$id_gato = $_GET['id'] ?? null;
$id_colonia = $_GET['col'] ?? null;
$id_responsable = $_SESSION["id_usuario"];

if (!$id_gato || !$id_colonia) {
    echo "No se recibió el ID del gato o de la colonia.";
    exit();
}

// Inicialización de variables para la interfaz
$mensaje = "";
$exito = false;
$comentarios = "";
$estado = false;
$fecha_solicitud = date("Y-m-d");
$datos_visita = [
    'id_responsable' => '',
    'id_gato' => '',
    'fecha_solicitud' => '',
    'comentarios' => ''
];

// Lógica del formulario
if (isset($_POST['guardar_cambios'])) {
    
    // 1. Recoger datos del formulario
    $fecha_solicitud = $_POST['fecha_solicitud'];
    $comentarios = $_POST['comentarios'];

    // 2. Conexión
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 
    
    $consulta_check = "
    SELECT 1
    FROM solicitud_retirada
    WHERE id_gato = '$id_gato'
    LIMIT 1
    ";

    $result_check = mysqli_query($conexion, $consulta_check);

    if (mysqli_num_rows($result_check) > 0) {
        $mensaje = "Ya se solicitó la retirada de este gato" ;
        $exito = false;
    } else {
        
        // PRIMERO INSERTAMOS LA SOLICITUD DE RETIRADA
        // 3. SENTENCIA INSERT
        $consulta_create = "INSERT INTO solicitud_retirada (fecha_solicitud, comentarios, aprobada, id_gato, id_responsable) 
                            VALUES  ('$fecha_solicitud', 
                                    '$comentarios', 
                                    '$estado', 
                                    '$id_gato',
                                    '$id_responsable')";

        // 4. Ejecución
        if (mysqli_query($conexion, $consulta_create)) {
            $mensaje = "Solicitud enviada exitosamente";
            $exito = true;
        } else {
            $mensaje = "Error al enviar la solicitud: " . mysqli_error($conexion);
            $exito = false;
        }

        // LUEGO, ACTUALIZAMOS EL HISTORIAL DE COLONIAS Y EL ESTADO DEL GATO A DIFUNTO
        $consulta_update = "UPDATE gato
                            SET id_estado = 4
                            WHERE id_gato = '$id_gato'";

        mysqli_query($conexion, $consulta_update);

        //ACTUALIZAMOS EL HISTORIAL DE LA COLONIA EN AYUNTAMIENTO, ESTO
        //NO SIRVE PERO SE MANTIENE COMO COMENTARIO POR SI SE REUTILIZA
        /**$consulta_update_hc = "UPDATE historial_colonia
                            SET fecha_salida = '$fecha_solicitud'
                            WHERE id_gato = '$id_gato'
                            AND id_colonia = '$id_colonia'";

        mysqli_query($conexion, $consulta_update_hc);**/

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
    <title>Solicitar retirada</title>

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

            <h2 class="titulo-dashboard" style="margin-bottom: 20px;">Solicitud de retirada</h2>
            
            <?php if ($mensaje): ?>
                <p class="mensaje-alerta <?php echo $exito ? 'mensaje-exito' : 'mensaje-error'; ?>">
                    <?php echo $mensaje; ?>
                </p>
            <?php endif; ?>

            <!-- Formulario de Edición -->
            <form method="POST" class="formulario-colonia">
                
                <!-- FECHA DATE -->
                <label for = "fecha_solicitud">Fecha de la solicitud:</label>
                <input type="text" id="fecha_solicitud" name="fecha_solicitud" maxlength="255" 
                       value="<?php echo $fecha_solicitud ?>" readonly
                       style="background-color: #eee; cursor: not-allowed;">

                <input type="hidden" name="fecha_solicitud" value="<?php echo htmlspecialchars($fecha_solicitud); ?>">
                
                <!-- COMENTARIOS (VARCHAR(255)) -->
                <label for = "comentarios">Comentarios de la solicitud:</label>
                <input type="text" id="comentarios" name="comentarios" maxlength="255" 
                       value="" required>

                <!-- ID_RESPONSABLE (INT) - NO MODIFICABLE -->
                <label for = "id_responsable">Código identificativo del responsable:</label>
                <input type="text" id="id_responsable" name="id_responsable" maxlength="20" 
                       value="<?php echo $id_responsable ?>" readonly
                       style="background-color: #eee; cursor: not-allowed;">

                <input type="hidden" name="id_responsable" value="<?php echo htmlspecialchars($id_responsable); ?>">
                
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
                <button class="boton-gestion" onclick="location.href='../gestionar_retirada.php'">Volver atrás</button></td>
            </div>
        </div>
    </div>
</div>
</body>
</html>