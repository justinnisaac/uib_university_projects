<?php
session_start();

// Seguridad: Verificar sesión de veterinario
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../login_registro/login.php");
    exit();
}

$id_solicitud_actual = "";
$datos_solicitud = [
    'id_solicitud' => '',
    'nombre_responsable' => '',
    'fecha_solicitud' => '',
    'comentarios' => '',
    'id_gato' => '',
    'id_colonia' => '',
    'aprobada' => ''
];
$mensaje_error = "";
$solicitud_encontrada = false;

// 1. Verificar si se ha pasado un ID
if (isset($_GET["id"])) {
    $id_solicitud_actual = $_GET["id"];
    
    // 2. Conexión
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 
    
    // 3. Consulta SELECT
    // Hacemos JOIN para sacar el nombre del responsable y SUBCONSULTA para la colonia
    $consulta_select = "
        SELECT s.id_solicitud, 
               s.fecha_solicitud, 
               s.comentarios, 
               s.id_gato, 
               s.aprobada,
               u.nombre AS nombre_responsable,
               u.apellidos AS apellidos_responsable,
               (
                   SELECT h.id_colonia 
                   FROM historial_colonia h 
                   WHERE h.id_gato = s.id_gato 
                   ORDER BY (h.fecha_salida IS NULL) DESC, h.fecha_ingreso DESC 
                   LIMIT 1
               ) AS id_colonia
        FROM solicitud_retirada s
        JOIN voluntario v ON s.id_responsable = v.id_voluntario
        JOIN usuario u ON v.id_voluntario = u.id_usuario
        WHERE s.id_solicitud = '$id_solicitud_actual'
    ";

    $resultado = mysqli_query($conexion, $consulta_select);

    if ($registro = mysqli_fetch_assoc($resultado)) {
        $datos_solicitud = $registro;
        $solicitud_encontrada = true;
    } else {
        $mensaje_error = "Error: Solicitud con ID '$id_solicitud_actual' no encontrada.";
    }

    mysqli_close($conexion);
    
} else {
    $mensaje_error = "Error: No se ha especificado el ID de la solicitud.";
}

// Los datos para mostrar en el formulario
$id = htmlspecialchars($datos_solicitud['id_solicitud']);
$responsable = htmlspecialchars($datos_solicitud['nombre_responsable'] . ' ' . ($datos_solicitud['apellidos_responsable'] ?? ''));
$fecha = htmlspecialchars($datos_solicitud['fecha_solicitud']);
$comentarios = htmlspecialchars($datos_solicitud['comentarios']);
$id_gato = htmlspecialchars($datos_solicitud['id_gato']);
$id_colonia = htmlspecialchars($datos_solicitud['id_colonia'] ?? '-'); // Si es NULL, ponemos '-'

// Convertir el valor almacenado a booleano y preparar texto/estilo
$aprobada = (int)($datos_solicitud['aprobada'] ?? 0) === 1;
$estado_texto = $aprobada ? 'Aprobada' : 'Pendiente';
$estado_color = $aprobada ? '#2e7d32' : '#e65100';

$titulo_pagina = $solicitud_encontrada ? "Detalles Solicitud: " . $id : "Detalles de la Solicitud";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo_pagina; ?></title>

    <link rel="stylesheet" href="../../estilo/estilo_panel.css"> 
    <link rel="stylesheet" href="../../estilo/estilo_contenido.css">
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../../../BD2npb/panel_veterinario/panel_opciones_veterinario.php"); ?>

    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Detalles de la solicitud #<?php echo $id; ?></h2>
            
            <?php if (!empty($mensaje_error)) : ?>
                <p class="mensaje-alerta mensaje-error" style="text-align: center; margin-bottom: 20px;">
                    <?php echo $mensaje_error; ?>
                </p>
            <?php endif; ?>

            <?php if ($solicitud_encontrada) : ?>
            
                <div class="formulario-colonia">
                    
                    <!-- ID SOLICITUD -->
                    <label for="id_solicitud">ID:</label>
                    <input type="text" id="id_solicitud" name="id_solicitud" value="<?php echo $id; ?>" 
                           maxlength="10" required readonly>
                    
                    <!-- RESPONSABLE -->
                    <label for="responsable">Responsable solicitante:</label>
                    <input type="text" id="responsable" name="responsable" value="<?php echo $responsable; ?>" 
                           maxlength="150" required readonly>
                    
                    <!-- FECHA -->
                    <label for="fecha">Fecha de solicitud:</label>
                    <input type="text" id="fecha" name="fecha" value="<?php echo $fecha; ?>"
                           maxlength="20" readonly>
                    
                    <!-- COMENTARIOS -->
                    <label for="comentarios">Comentarios de la solicitud:</label>
                    <input type="text" id="comentarios" name="comentarios" value="<?php echo $comentarios; ?>"
                           maxlength="255" readonly>
                           
                    <!-- ID GATO -->
                    <label for="id_gato">Gato afectado:</label>
                    <input type="text" id="id_gato" name="id_gato" value="<?php echo $id_gato; ?>"
                           maxlength="10" readonly>

                    <!-- ID COLONIA -->
                    <label for="id_colonia">Colonia de procedencia:</label>
                    <input type="text" id="id_colonia" name="id_colonia" value="<?php echo $id_colonia; ?>"
                           maxlength="10" readonly>

                          <!-- ESTADO -->
                          <label for="aprobada">Estado de la solicitud:</label>
                          <input type="text" id="aprobada" name="aprobada" value="<?php echo htmlspecialchars($estado_texto); ?>"
                              maxlength="50" readonly 
                              style="font-weight: bold; color: <?php echo $estado_color; ?>;">
                    
                    <!-- Botón Volver -->
                    <button type="button" 
                            class="boton-gestion boton-confirmar" 
                            onclick="location.href='../gestionar_solicitudes_retirada.php'">
                        Volver a solicitudes
                    </button>

                </div>
            
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>