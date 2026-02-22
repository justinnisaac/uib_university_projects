
<?php
session_start();

if (!isset($_SESSION["id_usuario"]) || !isset($_GET["id"])) {
    header("Location: ../../BD2imo/login_registro/login.php");
    exit();
}

$id_veterinario = $_SESSION["id_usuario"];
$id_intervencion = $_GET["id"];

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

$consulta_intervencion = "
    SELECT 
        iv.*,
        g.nombre AS nombre_gato,
        eg.estado AS estado_gato,
        c.nombre AS nombre_campana,
        tc.tipo AS tipo_campana,
        col.nombre_colonia
    FROM intervencion_veterinaria iv
    JOIN gato g ON iv.id_gato = g.id_gato
    JOIN estado_gato eg ON g.id_estado = eg.id_estado
    JOIN campana c ON iv.id_campana = c.id_campana
    JOIN tipo_campana tc ON c.id_tipo_campana = tc.id_tipo_campana
    JOIN colonia col ON c.id_colonia = col.id_colonia
    AND iv.id_intervencion = '$id_intervencion'
    AND EXISTS (
        SELECT 1
        FROM veterinario_accion va
        WHERE va.id_intervencion = iv.id_intervencion
        AND va.id_veterinario = '$id_veterinario'
    )

";

$resultado_intervencion = mysqli_query($conexion, $consulta_intervencion);

if (!$resultado_intervencion || mysqli_num_rows($resultado_intervencion) == 0) {
    mysqli_close($conexion);
    header("Location: ../intervenciones.php");
    exit();
}

$intervencion = mysqli_fetch_array($resultado_intervencion);

$consulta_veterinarios = "
    SELECT 
        u.nombre,
        u.apellidos,
        v.especialidad,
        cv.nombre as nombre_centro
    FROM veterinario_accion va
    JOIN veterinario v ON va.id_veterinario = v.id_veterinario
    JOIN usuario u ON v.id_veterinario = u.id_usuario
    JOIN centro_veterinario cv ON v.id_centro = cv.id_centro
    AND va.id_intervencion = '$id_intervencion'
    ORDER BY u.nombre, u.apellidos
";

$resultado_veterinarios = mysqli_query($conexion, $consulta_veterinarios);

mysqli_close($conexion);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ver intervención</title>
    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_contenido.css">
    <style>
        .contenedor-doble {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }
        
        .columna-izquierda {
            flex: 1;
        }
        
        .columna-derecha {
            flex: 1;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        
        /* Estilos para que los labels y valores estén en línea */
        .campo-veterinario div {
            display: flex;
            margin-bottom: 8px;
        }
        
        .campo-veterinario label {
            font-weight: bold;
            min-width: 100px;
            margin-right: 10px;
        }
        
        /* Separador entre veterinarios */
        .campo-veterinario {
            padding-bottom: 15px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .campo-veterinario:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
    </style>
</head>

<body>
<div style="display:flex;">
<?php include("../panel_opciones_veterinario.php"); ?>

<div class="zona-contenido">
<div class="contenedor-difuminado">

<h2 class="titulo-dashboard">Intervención: <?= $intervencion['id_intervencion'] ?></h2>

<div class="contenedor-doble">
    
    <!-- COLUMNA IZQUIERDA: Información original -->
    <div class="columna-izquierda">
        <div class="formulario-colonia">
            <label>Fecha:</label>
            <input type="text" value="<?php echo htmlspecialchars($intervencion['fecha']); ?>" readonly>
            
            <label>Gato:</label>
            <input type="text" value="<?php echo htmlspecialchars($intervencion['id_gato'] . ' - ' . $intervencion['nombre_gato']); ?>" readonly>
            
            <label>Estado gato:</label>
            <input type="text" value="<?php echo htmlspecialchars($intervencion['estado_gato']); ?>" readonly>
            
            <label>Campaña:</label>
            <input type="text" value="<?php echo htmlspecialchars($intervencion['nombre_campana']); ?>" readonly>
            
            <label>Colonia:</label>
            <input type="text" value="<?php echo htmlspecialchars($intervencion['nombre_colonia']); ?>" readonly>
            
            <label>Comentario:</label>
            <input type="text" value="<?php echo htmlspecialchars($intervencion['comentario']); ?>" readonly>
        </div>
    </div>
    
    <!-- COLUMNA DERECHA: Veterinarios -->
    <div class="columna-derecha">
        <h3 style="margin-top: 0; margin-bottom: 20px;">Veterinarios participantes</h3>
        
        <?php if ($resultado_veterinarios && mysqli_num_rows($resultado_veterinarios) > 0): ?>
            <?php 
            // Reiniciar el puntero del resultset
            mysqli_data_seek($resultado_veterinarios, 0);
            while ($vet = mysqli_fetch_array($resultado_veterinarios)): 
            ?>
                <div class="campo-veterinario">
                    <div>
                        <label>Nombre:</label> 
                        <span><?php echo htmlspecialchars($vet['nombre'] . ' ' . $vet['apellidos']); ?></span>
                    </div>
                    <div>
                        <label>Especialidad:</label> 
                        <span><?php echo htmlspecialchars($vet['especialidad']); ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No hay veterinarios registrados</p>
        <?php endif; ?>
    </div>
    
</div>

<!-- Botón de volver (debajo de ambas columnas) -->
<div style="margin-top: 30px; text-align: center;">
    <button type="button"
            class="boton-gestion"
            onclick="location.href='../vet_intervenciones.php'">
        Volver a intervenciones
    </button>
</div>

</div>
</div>
</div>
</body>
</html>