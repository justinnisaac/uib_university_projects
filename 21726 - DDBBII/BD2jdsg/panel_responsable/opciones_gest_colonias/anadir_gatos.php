<?php

session_start();

//Obtengo el id del voluntario
$id_colonia = $_GET['id'] ?? null;
$id_gato = null;

$fecha_ingreso = date("Y-m-d");

if (!$id_colonia) {
    echo "No se recibió el ID de la colonia";
    exit();
}

// Inicialización de variables para la interfaz
$mensaje = "";
$exito = false;
$estado_id = null;
$datos_tarea = [
    'nombre' => '',
    'num_chip' => '',
    'url_foto' => '',
    'descripcion_aspecto' => '',
    'estado' => ''
];

// Lógica del formulario
if (isset($_POST['guardar_cambios'])) {
    
    // 1. Recoger datos del formulario
    $nombre = $_POST['nombre'];
    $num_chip = $_POST['num_chip'];
    $url_foto = $_POST['url_foto'];
    $descripcion_aspecto = $_POST['descripcion'];
    $estado = $_POST['estado'];

    // 2. Conexión
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 
    
    //Obtengo el id del estado con una consulta
    $consulta_select = "SELECT eg.id_estado
                        FROM estado_gato eg
                        WHERE eg.estado = '$estado'";
    $resultado_select = mysqli_query($conexion, $consulta_select);
    $fila_select = mysqli_fetch_array($resultado_select);
    $estado_id = $fila_select["id_estado"];

    // Para manejar campos opcionales
    $num_chip_db = empty($num_chip) ? "NULL" : "'$num_chip'";
    $url_foto_db = empty($url_foto) ? "NULL" : "'$url_foto'";
    $descripcion_aspecto_db = empty($descripcion_aspecto) ? "NULL" : "'$descripcion_aspecto'";

    // 3. SENTENCIA INSERT
    $consulta_create = "INSERT INTO gato (nombre, num_chip, url_foto, descripcion_aspecto, id_estado) 
                        VALUES  ('$nombre',
                                $num_chip_db,
                                $url_foto_db,
                                $descripcion_aspecto_db, 
                                '$estado_id')";

    // 4. Ejecución
    if (mysqli_query($conexion, $consulta_create)) {
        $mensaje = "Gato añadido exitosamente";
        $exito = true;

        $id_gato = mysqli_insert_id($conexion);

        // 4. SENTENCIA INSERT DEL HISTORIAL
        $consulta_create_historial = "INSERT INTO historial_colonia (id_gato, id_colonia, fecha_ingreso, fecha_salida) 
                            VALUES  ('$id_gato',
                                    '$id_colonia',
                                    '$fecha_ingreso',
                                    NULL)";

        mysqli_query($conexion, $consulta_create_historial);
        
    } else {
        $mensaje = "Error al añadir el gato: " . mysqli_error($conexion);
        $exito = false;
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
    <title>Añadir gatos</title>

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

            <h2 class="titulo-dashboard" style="margin-bottom: 20px;">Información sobre el gato</h2>
            
            <?php if ($mensaje): ?>
                <p class="mensaje-alerta <?php echo $exito ? 'mensaje-exito' : 'mensaje-error'; ?>">
                    <?php echo $mensaje; ?>
                </p>
            <?php endif; ?>

            <!-- Formulario de Edición -->
            <form method="POST" class="formulario-colonia">

                <!-- ESTADO (ENUM ('Saludable','Enfermo','Herido')) -->
                <label for = "estado">Estado del gato:</label>
                <select name="estado" class="select-campo" required>
                    <option value="">Selecciona el estado del gato</option>

                    <?php
                    $tipos_estado = ['Saludable', 'Enfermo', 'Herido'];

                    foreach ($tipos_estado as $tipo_estado) {
                        echo '<option value="' . htmlspecialchars($tipo_estado) . '">' .
                            (htmlspecialchars($tipo_estado)) .
                            '</option>';
                    }
                    ?>
                </select>

                <!-- NOMBRE (VARCHAR(100)) -->
                <label for = "nombre">Nombre del gato:</label>
                <input type="text" id="nombre" name="nombre" maxlength="100" 
                       value="" required>

                <!-- NUM_CHIP (VARCHAR(20)) opcional -->
                <label for = "num_chip">Número de chip: (opcional)</label>
                <input type="text" id="num_chip" name="num_chip" maxlength="20" 
                       value="">

                <!-- URL_FOTO (VARCHAR(255)) opcional -->
                <label for = "url_foto">URL de la foto: (opcional)</label>
                <input type="text" id="url_foto" name="url_foto" maxlength="255" 
                       value="">

                <!-- DESCRICION_ASPECTO (VARCHAR(255)) opcional -->
                <label for = "descripcion">Descripción: (opcional)</label>
                <input type="text" id="descripcion" name="descripcion" maxlength="255" 
                       value="">

                <!-- ID_COLONIA (VARCHAR(255)) - NO MODIFICABLE -->
                <label for = "id_colonia">Código identificativo de la colonia:</label>
                <input type="text" id="id_colonia" name="id_colonia" maxlength="20" 
                       value="<?php echo $id_colonia ?>" readonly
                       style="background-color: #eee; cursor: not-allowed;">

                <input type="hidden" name="id_voluntario" value="<?php echo htmlspecialchars($id_colonia); ?>">

                <!-- Botón: "Guardar cambios" -->
                <button type="submit" name="guardar_cambios" class="boton-gestion boton-confirmar">Añadir gato</button>
            </form>
            <!-- Botón para volver atrás -->
            <div style="margin-top: 30px; text-align: center;">
                <button class="boton-gestion" onclick="location.href='../gestionar_colonias.php'">Volver atrás</button></td>
            </div>
        </div>
    </div>
</div>
</body>
</html>