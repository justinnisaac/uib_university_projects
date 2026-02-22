<?php

session_start();

$id_responsable = $_SESSION["id_usuario"];
$id_colonia = null;
$id_centro_veterinario = null;
$id_ayuntamiento = null;

// Inicialización de variables para la interfaz
$mensaje = "";
$exito = false;
$comentarios = "";
$datos_campana = [
    'nombre' => '',
    'tipo_campana' => '',
    'fechaInicio' => '',
    'tipoVacunacion' => ''
];

// 2. Conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// 2. CONSULTA PARA OBTENER EL AYUNTAMIENTO DEL QUE ES EL RESPONSABLE
$consulta_ayto = "SELECT a.id_ayuntamiento
                  FROM ayuntamiento a
                  JOIN borsin_voluntarios b ON b.id_ayuntamiento = a.id_ayuntamiento
                  JOIN voluntario v ON v.id_borsin = b.id_borsin
                  WHERE v.id_voluntario = '$id_responsable'";

$resultado_ayto = mysqli_query($conexion, $consulta_ayto);
$fila_ayto = mysqli_fetch_array($resultado_ayto);
$id_ayuntamiento = $fila_ayto["id_ayuntamiento"];

// 2. Consulta para obtener todas las colonias de este ayuntamiento
$consulta_colonia = "SELECT id_colonia FROM colonia WHERE id_ayuntamiento = '$id_ayuntamiento'";
$resultado_colonias = mysqli_query($conexion, $consulta_colonia);

// 2. Consulta para obtener todos los centros veterinarios de este ayuntamiento
$consulta_centro = "SELECT cv.id_centro
                    FROM centro_veterinario cv
                    JOIN ayuntamiento a ON a.id_municipio = cv.id_municipio
                    WHERE a.id_ayuntamiento = '$id_ayuntamiento'";

$resultado_centro = mysqli_query($conexion, $consulta_centro);

// 5. Cerrar la conexión (temporalmente, se reabre en la PARTE 2 si es necesario)
mysqli_close($conexion);

// Lógica del formulario
if (isset($_POST['guardar_cambios'])) {
    
    // 1. Recoger datos del formulario
    $id_centro_veterinario = $_POST['id_centro_veterinario'];
    $id_colonia = $_POST['id_colonia'];

    $nombre = $_POST['nombre'];
    $tipo_campana = $_POST['tipo_campana'];
    $fechaInicio = $_POST['fechaInicio'];
    $tipoVacunacion = $_POST['tipoVacunacion'];

    // 2. Conexión
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 
    
    //Obtengo el nombre del estado con una consulta
    $consulta_select = "SELECT tc.id_tipo_campana
                        FROM tipo_campana tc
                        WHERE tc.tipo = '$tipo_campana'";
    $resultado_select = mysqli_query($conexion, $consulta_select);
    $fila_select = mysqli_fetch_array($resultado_select);
    $tipo_campana_id = $fila_select["id_tipo_campana"];

    //Compruebo que esa colonia no tiene una campaña activa durante la fecha indicada
    $consulta_check = "
    SELECT 1
    FROM campana
    WHERE id_colonia = '$id_colonia'
    AND nombre = '$nombre'
    AND fechaFin IS NULL
    LIMIT 1
    ";

    $result_check = mysqli_query($conexion, $consulta_check);

    if (mysqli_num_rows($result_check) > 0) {
        $mensaje = "Ya existe una campaña con estas fechas de mismo nombre y tipo para esta colonia" ;
        $exito = false;
    } else {
        
        $tipoVacunacion_db = empty($tipoVacunacion) ? "NULL" : "'$tipoVacunacion'";

        // 3. SENTENCIA INSERT
        $consulta_create = "INSERT INTO campana (nombre, id_tipo_campana, fechaInicio, fechaFin, tipoVacunacion, 
                                                id_centro_veterinario, id_colonia, id_responsable) 
                            VALUES  ('$nombre', 
                                    '$tipo_campana_id', 
                                    '$fechaInicio', 
                                    NULL,
                                    $tipoVacunacion_db,
                                    '$id_centro_veterinario',
                                    '$id_colonia',
                                    '$id_responsable')";

        // 4. Ejecución
        if (mysqli_query($conexion, $consulta_create)) {
            $mensaje = "Campaña creada exitosamente";
            $exito = true;
        } else {
            $mensaje = "Error al crear la campaña: " . mysqli_error($conexion);
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
    <title>Añadir campaña</title>

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

            <h2 class="titulo-dashboard" style="margin-bottom: 20px;">Información sobre la campaña</h2>
            
            <?php if ($mensaje): ?>
                <p class="mensaje-alerta <?php echo $exito ? 'mensaje-exito' : 'mensaje-error'; ?>">
                    <?php echo $mensaje; ?>
                </p>
            <?php endif; ?>

            <!-- Formulario de Edición -->
            <form method="POST" class="formulario-colonia">
                
                <!-- NOMBRE (VARCHAR(100)) - OPCIONAL -->
                <label for = "nombre">Nombre de la campaña: </label>
                <input type="text" id="nombre" name="nombre" maxlength="100" 
                       value="" required>

                <!-- TIPO_CAMPANA (ENUM (DEMASIADO LARGO)) -->
                <label for = "tipo_campana">Tipo de campaña:</label>
                <select name="tipo_campana" class="select-campo" required>
                    <option value="">Selecciona el tipo de campaña</option>

                    <?php
                    $tipos_campana = ['Esterilización', 'Implantación de chips', 'Vacunación'];

                    foreach ($tipos_campana as $tipo_campana) {
                        echo '<option value="' . htmlspecialchars($tipo_campana) . '">' .
                            (htmlspecialchars($tipo_campana)) .
                            '</option>';
                    }
                    ?>
                </select>

                <!-- FECHAINICIO DATE -->
                <label for = "fechaInicio">Fecha de inicio de la campaña:</label>
                <input type="date" id="fechaInicio" name="fechaInicio" maxlength="255" 
                       value="" required>
                
                <!-- TIPO_VACUNACION (VARCHAR(100)) - OPCIONAL -->
                <label for = "tipoVacunacion">Tipo de vacunación: (opcional)</label>
                <input type="text" id="tipoVacunacion" name="tipoVacunacion" maxlength="100" 
                       value="">

                <!-- Menú desplegable para seleccionar colonia -->
                <label for = "id_colonia">Código identificativo de la colonia:</label>
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

                <!-- Menú desplegable para seleccionar centro veterinario -->
                <label for = "id_centro_veterinario">Código identificativo del centro veterinario:</label>
                <select name="id_centro_veterinario" class="select-campo" required>
                    <option value="">Selecciona un centro veterinario</option>
                    <?php
                    // 3. Generar las opciones del menú desplegable con los ayuntamientos obtenidos
                    if ($resultado_centro) {
                        while ($fila_centro = mysqli_fetch_array($resultado_centro)) {
                            // Usamos id_ayuntamiento como valor y mostramos el nombre
                            echo '<option value="' . htmlspecialchars($fila_centro['id_centro']) . '">' . 
                                 htmlspecialchars($fila_centro['id_centro']) . 
                                 '</option>';
                        }
                    }
                    ?>
                </select>
                
                <!-- Botón: "Guardar cambios" -->
                <button type="submit" name="guardar_cambios" class="boton-gestion boton-confirmar">Confirmar datos</button>
            </form>
            <div style="margin-top: 30px; text-align: center;">
                <button class="boton-gestion" onclick="location.href='../gestionar_campanas.php'">Volver atrás</button></td>
            </div>
        </div>
    </div>
</div>
</body>
</html>