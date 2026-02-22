<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../BD2imo/login_registro/login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$id_ayuntamiento = null;

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

//Obtengo la id del ayuntamiento
$consulta_ayto = "
    SELECT
        a.id_ayuntamiento,
        a.nombre
    FROM ayuntamiento a
    JOIN borsin_voluntarios b ON b.id_ayuntamiento = a.id_ayuntamiento
    JOIN voluntario v ON v.id_borsin = b.id_borsin
    WHERE v.id_voluntario = '$id_usuario'
";

$resultado_ayto = mysqli_query($conexion, $consulta_ayto);
$fila_ayto = mysqli_fetch_assoc($resultado_ayto);
$id_ayuntamiento = $fila_ayto["id_ayuntamiento"];

//Obtengo los avistamientos realizados en el ayuntamiento concreot
$consulta = "
    SELECT
        a.id_gato,
        a.id_colonia,
        a.fecha,
        a.comentarios
    FROM avistamiento a
    JOIN colonia c ON c.id_colonia = a.id_colonia
    WHERE c.id_ayuntamiento = '$id_ayuntamiento'
    ORDER BY a.fecha DESC
";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Avistamientos</title>

    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display:flex; flex-direction:row;">

    <?php include("panel_opciones_responsable.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Historial de avistamientos</h2>
            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID gato</th>
                        <th>ID colonia</th>
                        <th>Fecha</th>
                        <th>Comentarios</th>
                    </tr>
                </thead>
                <tbody>

<?php
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila["id_gato"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["id_colonia"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["fecha"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["comentarios"]); ?></td>
                    </tr>
<?php
    }
} else {
    echo "<tr>
            <td colspan='8' style='text-align:center; padding:20px;'>
                No hay visitas registradas.
            </td>
          </tr>";
}
?>
                </tbody>
            </table>
            <div style="margin-top: 30px; text-align: center;">
                <button class="boton-gestion" onclick="location.href='opciones_gest_avistamiento/anadir_avistamiento.php'">Añadir un avistamiento</button></td>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php mysqli_close($conexion); ?>