
<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../BD2imo/login_registro/login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

$consulta = "
    SELECT 
        c.id_colonia,
        c.nombre_colonia,
        c.coordenadas_GPS,
        c.descripción_ubicación,
        c.comentarios,
        a.nombre AS nombre_ayuntamiento
    FROM voluntario v
    JOIN borsin_voluntarios b ON v.id_borsin = b.id_borsin
    JOIN ayuntamiento a ON b.id_ayuntamiento = a.id_ayuntamiento
    JOIN colonia c ON c.id_ayuntamiento = a.id_ayuntamiento
    AND v.id_voluntario = '$id_usuario'
";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Colonias del ayuntamiento</title>

    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display:flex; flex-direction:row;">

    <?php include("panel_opciones_voluntario.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Colonias asociadas a mi ayuntamiento</h2>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Coordenadas</th>
                        <th>Descripción</th>
                        <th>Comentarios</th>
                        <th>Ayuntamiento</th>
                    </tr>
                </thead>
                <tbody>

<?php
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($registro = mysqli_fetch_array($resultado)) {
?>
                    <tr>
                        <td><?php echo htmlspecialchars($registro["id_colonia"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre_colonia"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["coordenadas_GPS"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["descripción_ubicación"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["comentarios"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre_ayuntamiento"]); ?></td>
                    </tr>
<?php
    }
} else {
    echo "<tr><td colspan='6' style='text-align:center; padding:20px;'>
            No hay colonias asociadas a tu ayuntamiento.
          </td></tr>";
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
