<?php

session_start();

//Obtengo el id del gato y de la visita
$id_visita = $_GET['id'] ?? null;
$id_gato = $_GET['gat'] ?? null;
$fecha_visita=null;

if (!$id_visita || !$id_gato) {
    echo "No se recibió el ID de la visita o del gato.";
    exit();
}

// Inicialización de variables para la interfaz
$mensaje = "";
$exito = false;
$estado = "";
$datos_visita = [
    'tipoIncidencia' => '',
    'descripcion' => '',
    'id_visita' => '',
    'id_gato' => ''
];

// Lógica del formulario
if (isset($_POST['guardar_cambios'])) {
    
    // 1. Recoger datos del formulario
    $tipoIncidencia = $_POST['tipoIncidencia'];
    $descripcion = $_POST['descripcion'];
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

    //Obtengo el id de la incidencia con una consulta
    $consulta_select_inc = "SELECT it.id_tipo_incidencia
                        FROM incidencia_tipo it
                        WHERE it.tipo_incidencia = '$tipoIncidencia'";
    $resultado_select_inc = mysqli_query($conexion, $consulta_select_inc);
    $fila_select_inc = mysqli_fetch_array($resultado_select_inc);
    $tipoIncidencia_id = $fila_select_inc["id_tipo_incidencia"];

    $consulta_check = "
    SELECT 1
    FROM incidencia
    WHERE id_visita = '$id_visita'
    AND id_gato = '$id_gato'
    LIMIT 1
    ";

    $result_check = mysqli_query($conexion, $consulta_check);

    if (mysqli_num_rows($result_check) > 0) {
        $mensaje = "Ya existe una incidencia registrada para este gato durante esta visita.";
        $exito = false;
    } else {
        
        // 3. SENTENCIA INSERT
        $consulta_create = "INSERT INTO incidencia (id_tipo_incidencia, descripcion, id_visita, id_gato) 
                            VALUES  ('$tipoIncidencia_id', 
                                    '$descripcion', 
                                    '$id_visita', 
                                    '$id_gato')";

        // 4. Ejecución
        if (mysqli_query($conexion, $consulta_create)) {
            $mensaje = "Incidencia creada con éxito";
            $exito = true;
        } else {
            $mensaje = "Error al crear la incidencia: " . mysqli_error($conexion);
            $exito = false;
        }

        // OBTENGO LA FECHA DE VISITA DE LA INCIDENCIA
        $consulta_fecha =  "SELECT
                                v.fecha_visita
                            FROM visita v
                            WHERE v.id_visita = '$id_visita'";

        $resultado_fecha = mysqli_query($conexion, $consulta_fecha); 
        $fecha_v = mysqli_fetch_array($resultado_fecha);
        $fecha_visita = $fecha_v["fecha_visita"];      

        // COMPRUEBO QUE NO HUBIESEN INCIDENCIAS FUTURAS QUE HAYAN OCURRIDO
        // PARA NO SOBREESCRIBIR LOS FUTUROS ESTADOS DEL GATO
        $consulta_incidencias = "SELECT 1
                                 FROM incidencia i
                                 JOIN visita v ON v.id_visita = i.id_visita
                                 WHERE i.id_gato = '$id_gato'
                                 AND v.fecha_visita > '$fecha_visita'";

        $resultado_incidencias = mysqli_query($conexion, $consulta_incidencias);
                                 
        if(!mysqli_num_rows($resultado_incidencias) > 0) {
            // ACTUALIZO EL ESTADO DEL GATO AL QUE SE HAYA INDICADO
            $consulta_update = "UPDATE gato
                                SET id_estado = '$estado_id'
                                WHERE id_gato = '$id_gato'";

            mysqli_query($conexion, $consulta_update);
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
    <title>Añadir incidencia - Formulario</title>

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

            <h2 class="titulo-dashboard" style="margin-bottom: 20px;">Información sobre la incidencia</h2>
            
            <?php if ($mensaje): ?>
                <p class="mensaje-alerta <?php echo $exito ? 'mensaje-exito' : 'mensaje-error'; ?>">
                    <?php echo $mensaje; ?>
                </p>
            <?php endif; ?>

            <!-- Formulario de Edición -->
            <form method="POST" class="formulario-colonia">
                
                <!-- TIPOINCIDENCIA (ENUM ('salud', 'comportamiento', 'otro')) -->
                <label for = "tipoIncidencia">Tipo de incidencia:</label>
                <select name="tipoIncidencia" class="select-campo" required>
                    <option value="">Selecciona el tipo de incidencia</option>

                    <?php
                    $tipos_incidencia = ['salud', 'comportamiento', 'otro'];

                    foreach ($tipos_incidencia as $tipo) {
                        echo '<option value="' . htmlspecialchars($tipo) . '">' .
                            (htmlspecialchars($tipo)) .
                            '</option>';
                    }
                    ?>
                </select>

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
                
                <!-- DESCRIPCION (VARCHAR(255)) -->
                <label for = "descripcion">Descripcion de la incidencia:</label>
                <input type="text" id="descripcion" name="descripcion" maxlength="255" 
                       value="" required>

                <!-- ID_VISITA (INT) - NO MODIFICABLE -->
                <label for = "id_visita">Código identificativo de la visita:</label>
                <input type="text" id="id_visita" name="id_visita" maxlength="20" 
                       value="<?php echo $id_visita ?>" readonly
                       style="background-color: #eee; cursor: not-allowed;">

                <input type="hidden" name="id_visita" value="<?php echo htmlspecialchars($id_visita); ?>">
                
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
                <button class="boton-gestion" onclick="location.href='../gestionar_visitas_incidencias.php'">Volver al inicio</button></td>
            </div>
        </div>
    </div>
</div>
</body>
</html>