<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../../BD2imo/login_registro/login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$id_visita = $_GET['id'] ?? null;
$fecha_visita = $_GET['fecha'] ?? null;

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

$consulta = "
    SELECT
        i.id_tipo_incidencia,
        i.descripcion AS descripcion_incidencia,
        i.id_visita,
        i.id_gato,
        g.nombre AS nombre_gato,
        c.id_colonia,
        c.nombre_colonia
    FROM incidencia i
    JOIN visita v ON i.id_visita = v.id_visita AND v.id_visita = '$id_visita'
    JOIN colonia c ON v.id_colonia = c.id_colonia
    JOIN gato g ON i.id_gato = g.id_gato
    WHERE v.id_responsable = '$id_usuario'
    ORDER BY v.fecha_visita DESC
";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Visualizar incidencias</title>

    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display:flex; flex-direction:row;">

    <?php include("../panel_opciones_responsable.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Incidencias durante la visita <?php echo htmlspecialchars($id_visita) ?> fecha <?php echo htmlspecialchars($fecha_visita) ?></h2>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Colonia</th>
                        <th>ID Colonia</th>
                        <th>Gato</th>
                        <th>ID Gato</th>
                    </tr>
                </thead>
                <tbody>

<?php
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($registro = mysqli_fetch_assoc($resultado)) {

        $tipoIncidencia_id = $registro["id_tipo_incidencia"];

        //Obtengo el nombre del estado con una consulta
        $consulta_select = "SELECT it.tipo_incidencia
                        FROM incidencia_tipo it
                        WHERE it.id_tipo_incidencia = '$tipoIncidencia_id'";
        $resultado_select = mysqli_query($conexion, $consulta_select);
        $fila_select = mysqli_fetch_array($resultado_select);
        $tipoIncidencia = $fila_select["tipo_incidencia"];
?>
                    <tr>
                        <td><?php echo htmlspecialchars($tipoIncidencia); ?></td>
                        <td><?php echo htmlspecialchars($registro["descripcion_incidencia"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre_colonia"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["id_colonia"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre_gato"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["id_gato"]); ?></td>
                    </tr>
<?php
    }
} else {
    echo "<tr>
            <td colspan='7' style='text-align:center; padding:20px;'>
                No hay incidencias registradas.
            </td>
          </tr>";
}
?>

                </tbody>
            </table>
            <div style="margin-top: 30px; text-align: center;">
                <button class="boton-gestion" onclick="location.href='../gestionar_visitas_incidencias.php'">Volver atrás</button>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php mysqli_close($conexion); ?>