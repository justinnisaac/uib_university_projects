<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../../BD2imo/login_registro/login.php");
    exit();
}

$id_visita = $_GET['id'] ?? null;
$fecha_visita = $_GET['fec'] ?? null;
$id_colonia = $_GET['col'] ?? null;
$id_gato = null;
$estado = null;

if (!$id_colonia) {
    echo "No se ha especificado la colonia.";
    exit();
}

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

$consulta = "
    SELECT 
        g.id_gato,
        g.nombre,
        g.id_estado,
        g.num_chip,
        g.descripcion_aspecto,
        g.url_foto
    FROM historial_colonia hc
    JOIN gato g ON hc.id_gato = g.id_gato
    WHERE hc.id_colonia = '$id_colonia'
      AND hc.fecha_ingreso <= '$fecha_visita'
      AND (hc.fecha_salida IS NULL OR hc.fecha_salida > '$fecha_visita') 
      AND g.id_estado != 4
";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Añadir incidencia</title>

    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display:flex; flex-direction:row;">

    <?php include("../panel_opciones_responsable.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">
                Gatos de la colonia <?php echo htmlspecialchars($id_colonia); ?> a fecha <?php echo htmlspecialchars($fecha_visita); ?>
            </h2>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID Gato</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Nº Chip</th>
                        <th>Descripción</th>
                        <th>Foto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>

<?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
<?php while ($gato = mysqli_fetch_assoc($resultado)):

    $estado = $gato["id_estado"];

    //Obtengo el nombre del estado con una consulta
    $consulta_select = "SELECT eg.estado
                        FROM estado_gato eg
                        WHERE eg.id_estado = '$estado'";
    $resultado_select = mysqli_query($conexion, $consulta_select);
    $fila_select = mysqli_fetch_array($resultado_select);
    $estado_gato = $fila_select["estado"];

    $id_gato = $gato["id_gato"]?>

                    <tr>
                        <td><?php echo htmlspecialchars($gato["id_gato"]); ?></td>
                        <td><?php echo htmlspecialchars($gato["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($estado_gato); ?></td>
                        <td><?php echo htmlspecialchars($gato["num_chip"] ?? "-"); ?></td>
                        <td><?php echo htmlspecialchars($gato["descripcion_aspecto"]); ?></td>
                        <td>
                            <?php if ($gato["url_foto"]): ?>
                                <img src="<?php echo htmlspecialchars($gato["url_foto"]); ?>" 
                                     style="width:80px; border-radius:8px;">
                            <?php else: ?>
                                Sin foto
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="acciones-container"> 
                                    <button class="boton-mini" onclick="location.href='formulario_anadir_incidencia.php?id=<?php echo $id_visita ?>&gat=<?php echo $id_gato ?>'">Añadir incidencia</button>     
                            </div>
                        </td>
                    </tr>
<?php endwhile; ?>
<?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding:20px;">
                            No había gatos en esta colonia.
                        </td>
                    </tr>
<?php endif; ?>

                </tbody>
            </table>

            <div style="margin-top: 20px; text-align:center;">
                <button class="boton-gestion" onclick="history.back()">Volver</button>
            </div>

        </div>
    </div>
</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>