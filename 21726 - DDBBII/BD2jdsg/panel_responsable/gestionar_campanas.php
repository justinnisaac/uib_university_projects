<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../../BD2imo/login_registro/login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$id_campana = null;

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

$consulta = "
    SELECT 
        c.id_campana,
        c.nombre,
        c.id_tipo_campana,
        c.fechaInicio,
        c.fechaFin,
        c.tipoVacunacion,
        c.id_centro_veterinario,
        c.id_colonia
    FROM campana c
    WHERE c.id_responsable = '$id_usuario'
    ORDER BY c.id_campana ASC
";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Campañas</title>

    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display:flex; flex-direction:row;">

    <?php include("panel_opciones_responsable.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Campañas realizadas</h2>
            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID Campaña</th>
                        <th>Nombre</th>
                        <th>Tipo de campaña</th>
                        <th>Fecha de inicio</th>
                        <th>Fecha de fin</th>
                        <th>Tipo de vacunación</th>
                        <th>ID Centro Veterinario</th>
                        <th>ID Colonia</th>
                    </tr>
                </thead>
                <tbody>

<?php
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($fila = mysqli_fetch_assoc($resultado)) {

    $tipo_campana_id = $fila["id_tipo_campana"];

    //Obtengo el nombre del estado con una consulta
    $consulta_select = "SELECT tc.tipo
                        FROM tipo_campana tc
                        WHERE tc.id_tipo_campana = '$tipo_campana_id'";
    $resultado_select = mysqli_query($conexion, $consulta_select);
    $fila_select = mysqli_fetch_array($resultado_select);
    $tipo_campana = $fila_select["tipo"];
        
    $id_campana = $fila["id_campana"];
    $fechaFin = $fila["fechaFin"] ?? null;
?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila["id_campana"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($tipo_campana); ?></td>
                        <td><?php echo htmlspecialchars($fila["fechaInicio"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["fechaFin"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["tipoVacunacion"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["id_centro_veterinario"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["id_colonia"]); ?></td>
                    </tr>
<?php
    }
} else {
    echo "<tr>
            <td colspan='8' style='text-align:center; padding:20px;'>
                No hay campañas registradas.
            </td>
          </tr>";
}
?>
                </tbody>
            </table>
            <div style="margin-top: 30px; text-align: center;">
                <button class="boton-gestion" onclick="location.href='opciones_gest_campanas/anadir_campana.php'">Añadir nueva campaña</button></td>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php mysqli_close($conexion); ?>
