
<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../BD2imo/login_registro/login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];


// Conexión
$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

// Consulta campañas del veterinario
$consulta = "
    SELECT 
    c.id_campana,
    c.nombre AS nombre_campana,
    tc.tipo AS tipo_campana,
    c.fechaInicio,
    c.fechaFin,
    c.tipoVacunacion,
    col.id_colonia,
    col.nombre_colonia
FROM participacion p
JOIN campana c ON p.id_campana = c.id_campana
JOIN tipo_campana tc ON c.id_tipo_campana = tc.id_tipo_campana
JOIN colonia col ON c.id_colonia = col.id_colonia
AND p.id_veterinario = '$id_usuario'

";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Campañas asignadas</title>

    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <?php include("panel_opciones_veterinario.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Campañas asignadas</h2>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Colonia</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Vacunación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php  
                    if ($resultado && mysqli_num_rows($resultado) > 0) {
                        while ($registro = mysqli_fetch_array($resultado)) {
                            $id_campana = $registro["id_campana"];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($id_campana); ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre_campana"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["tipo_campana"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre_colonia"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["fechaInicio"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["fechaFin"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["tipoVacunacion"]); ?></td>
                        
                        <td style="text-align:center;">
                            <?php if (is_null($registro["fechaFin"])) { ?>
                                <a style="display: inline-block; 
                                    padding: 8px 20px; 
                                    background-color: #000000; 
                                    color: white; 
                                    text-decoration: none; 
                                    border-radius: 6px; 
                                    font-size: 14px;
                                    border: none;
                                    font-weight: 500;
                                    white-space: nowrap;"
                                    href="opciones_campanas/ver_gatos_colonia.php?id=<?php echo $registro['id_colonia']; ?>">
                                    Ver gatos 
                                </a>
                            <?php } else {
                                    echo "<span style='color: #aaa;'>No hay acciones disponibles.</span>";
                                } ?>
                        </td>
                    </tr>
                    <?php
                        } 
                    } else {
                        // Mensaje si no hay campañas
                        echo "<tr><td colspan='8' style='text-align:center; padding:20px;'>No hay campañas asignadas.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

        </div>
    </div>
</div>

</body>
</html>

<?php mysqli_close($conexion); ?>