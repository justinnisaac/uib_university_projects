<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../BD2imo/login_registro/login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$id_colonia = null;
$id_ayuntamiento = null;
$nombre_ayuntamiento = null;
$cantidad_gatos = null;

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

//CONSULTA PARA OBTENER NOMBRE E IDENTIFICADOR DEL AYUNTAMIENTO
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
$nombre_ayuntamiento = $fila_ayto["nombre"];

//CONSULTA PARA OBTENER EL NUMERO DE GATOS TOTAL ENTRE LAS COLONIAS
$consulta_gatos = "
    SELECT 
        COUNT(DISTINCT g.id_gato) AS total_gatos
    FROM colonia c
    JOIN historial_colonia hc ON hc.id_colonia = c.id_colonia
    JOIN gato g ON g.id_gato = hc.id_gato
    WHERE c.id_ayuntamiento = '$id_ayuntamiento'
    AND hc.fecha_salida IS NULL
    AND g.id_estado != 4
";

$resultado_gatos = mysqli_query($conexion, $consulta_gatos);
$fila_gatos = mysqli_fetch_assoc($resultado_gatos);
$cantidad_gatos = $fila_gatos["total_gatos"];

//CONSULTA PARA OBTENER LOS DATOS DE LAS COLONIAS
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
    WHERE v.id_voluntario = '$id_usuario'
";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Colonias</title>

    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display:flex; flex-direction:row;">

    <?php include("panel_opciones_responsable.php"); ?>

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
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>

<?php
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($registro = mysqli_fetch_array($resultado)) {

        $id_colonia = $registro["id_colonia"];
?>
                    <tr>
                        <td><?php echo htmlspecialchars($registro["id_colonia"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre_colonia"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["coordenadas_GPS"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["descripción_ubicación"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["comentarios"]); ?></td>
                        <td>
                            <div class="acciones-container">     
                                <button class="boton-mini" onclick="location.href='opciones_gest_colonias/anadir_gatos.php?id=<?php echo $id_colonia; ?>'">Añadir gato</button>
                                <button class="boton-mini" onclick="location.href='opciones_gest_colonias/visualizar_gatos.php?id=<?php echo $id_colonia; ?>'">Ver gatos</button>
                            </div>
                        </td>
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

            <h2 class="titulo-dashboard">Nº de gatos en <?php echo $nombre_ayuntamiento ?>: <?php echo $cantidad_gatos ?></h2>

        </div>
    </div>
</div>

</body>
</html>

<?php mysqli_close($conexion); ?>