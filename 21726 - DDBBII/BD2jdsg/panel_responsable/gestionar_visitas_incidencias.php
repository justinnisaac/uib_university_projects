<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../../BD2imo/login_registro/login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$id_visita = null;
$fecha_visita = null;

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

$consulta = "
    SELECT 
        v.id_visita,
        v.fecha_visita,
        v.comentarios,
        c.id_colonia,
        c.nombre_colonia,
        a.nombre AS nombre_ayuntamiento,
        u.nombre AS nombre_responsable,
        u.apellidos AS apellidos_responsable
    FROM visita v
    JOIN colonia c ON v.id_colonia = c.id_colonia
    JOIN ayuntamiento a ON c.id_ayuntamiento = a.id_ayuntamiento
    JOIN voluntario vol ON v.id_responsable = vol.id_voluntario
    JOIN usuario u ON vol.id_voluntario = u.id_usuario
    JOIN borsin_voluntarios b ON b.id_ayuntamiento = a.id_ayuntamiento
    JOIN voluntario v2 ON v2.id_borsin = b.id_borsin
    WHERE v2.id_voluntario = '$id_usuario'
    ORDER BY v.fecha_visita DESC
";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Visitas e incidencias</title>

    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display:flex; flex-direction:row;">

    <?php include("panel_opciones_responsable.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Visitas realizadas</h2>
            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID Visita</th>
                        <th>Fecha</th>
                        <th>Colonia</th>
                        <th>ID Colonia</th>
                        <th>Responsable</th>
                        <th>Ayuntamiento</th>
                        <th>Comentarios</th>
                        <th>Incidencias</th>
                    </tr>
                </thead>
                <tbody>

<?php
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($fila = mysqli_fetch_assoc($resultado)) {

    $id_visita = $fila["id_visita"];
    $id_colonia = $fila["id_colonia"];
    $fecha_visita = $fila["fecha_visita"];
?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila["id_visita"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["fecha_visita"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["nombre_colonia"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["id_colonia"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["nombre_responsable"] . " " . $fila["apellidos_responsable"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["nombre_ayuntamiento"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["comentarios"]); ?></td>
                        <td>
                            <div class="acciones-container"> 
                                <button class="boton-mini" onclick="location.href='opciones_gest_incidencia/anadir_incidencia.php?id=<?php echo $id_visita ?>&fec=<?php echo $fecha_visita ?>&col=<?php echo $id_colonia ?>'">Añadir</button>
                                <button class="boton-mini" onclick="location.href='opciones_gest_incidencia/visualizar_incidencias.php?id=<?php echo $id_visita ?>&fecha=<?php echo $fecha_visita ?>'">Visualizar</button>
                            </div>
                        </td>
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
                <button class="boton-gestion" onclick="location.href='opciones_gest_visita/anadir_visita.php'">Añadir una visita</button></td>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php mysqli_close($conexion); ?>