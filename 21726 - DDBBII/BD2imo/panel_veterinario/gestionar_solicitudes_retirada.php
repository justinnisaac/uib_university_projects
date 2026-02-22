<?php
session_start();

// Seguridad: Verificar sesión de veterinario
if (!isset($_SESSION["usuario"]) || !isset($_SESSION["id_municipio_vet"])) {
    header("Location: ../login_registro/login.php");
    exit();
}

$id_municipio_vet = $_SESSION["id_municipio_vet"];
$nombre_municipio = $_SESSION["nombre_municipio_vet"] ?? "tu municipio";

// Conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// Consulta de solicitudes de retirada filtradas por municipio del veterinario
// Esto ocurre porque queremos imponer que, si por ejemplo el veterinario trabaja
// en un centro de Palma, que sólo aparezcan solicitudes de retirada de grupos de Palma
$consulta = "
    SELECT s.id_solicitud, 
           s.fecha_solicitud, 
           s.id_gato, 
           s.id_responsable, 
           s.comentarios, 
           s.aprobada,
           (
               SELECT h.id_colonia 
               FROM historial_colonia h 
               WHERE h.id_gato = s.id_gato 
               ORDER BY (h.fecha_salida IS NULL) DESC, h.fecha_ingreso DESC 
               LIMIT 1
           ) AS id_colonia
    FROM solicitud_retirada s
    JOIN voluntario v ON s.id_responsable = v.id_voluntario
    JOIN borsin_voluntarios b ON v.id_borsin = b.id_borsin
    JOIN ayuntamiento a ON b.id_ayuntamiento = a.id_ayuntamiento
    WHERE a.id_municipio = '$id_municipio_vet'
";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Solicitudes de Retirada</title>
    
    <link rel="stylesheet" href="../estilo/estilo_panel.css">
    <link rel="stylesheet" href="../estilo/estilo_contenido.css">
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../../BD2npb/panel_veterinario/panel_opciones_veterinario.php"); ?>

    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <!-- Título -->
            <h2 class="titulo-dashboard">Solicitudes de retiradas de gatos en <?php echo htmlspecialchars($nombre_municipio); ?></h2>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID Solicitud</th>
                        <th>ID Gato</th>
                        <th>ID Colonia</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
<?php
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while($row = mysqli_fetch_array($resultado)) {
        $id_solicitud = $row['id_solicitud'];
        $aprobada = (int)($row['aprobada'] ?? 0) === 1; // booleano
        $id_gato = $row['id_gato'];
        $id_colonia = $row['id_colonia'] ?? '-'; // Valor por defecto si no se encuentra

        // Texto y estilo visual del estado en base al booleano
        $estado_texto = $aprobada ? 'Aprobada' : 'Pendiente';
        $estilo_estado = $aprobada
            ? "color: #2e7d32; font-weight: bold;"   // verde
            : "color: #e65100; font-weight: bold;";  // naranja
?>
                    <tr>
                        <td><?php echo htmlspecialchars($id_solicitud); ?></td>
                        <td><?php echo htmlspecialchars($id_gato); ?></td>
                        <td><?php echo htmlspecialchars($id_colonia); ?></td>
                        <td style="<?php echo $estilo_estado; ?>"><?php echo htmlspecialchars($estado_texto); ?></td>
                        <td>
                            <div class="acciones-container">
                                
                                <!-- Botón Detalles Gato -->
                                <button class="boton-mini" 
                                        onclick="location.href='opciones_gest_solicitudes/ver_gato.php?id=<?php echo $id_gato; ?>'">
                                    Detalles gato
                                </button>

                                <!-- Botón Detalles Colonia -->
                                <?php if ($id_colonia !== '-'): ?>
                                    <button class="boton-mini" 
                                            onclick="location.href='opciones_gest_solicitudes/ver_colonia.php?id=<?php echo $id_colonia; ?>'">
                                        Detalles colonia
                                    </button>
                                <?php endif; ?>

                                <!-- Botón Detalles Solicitud (permite ver más detalles interesantes sobre la solicitud)-->
                                <button class="boton-mini" 
                                        onclick="location.href='opciones_gest_solicitudes/detalles_solicitud.php?id=<?php echo $id_solicitud; ?>'">
                                    Detalles solicitud
                                </button>

                                <!-- Botón Aprobar: Solo visible si está pendiente la solicitud -->
                                <?php if (!$aprobada): ?>
                                    <button class="boton-mini" 
                                            style="background-color: #2e7d32;"
                                            onclick="location.href='opciones_gest_solicitudes/realizar_retirada.php?id=<?php echo $id_solicitud; ?>'">
                                        Aprobar
                                    </button>
                                <?php endif; ?>

                            </div>
                        </td>
                    </tr>
<?php
    }
} else {
    echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>No hay solicitudes de retirada registradas en tu municipio.</td></tr>";
}
?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
mysqli_close($conexion);
?>

</body>
</html>