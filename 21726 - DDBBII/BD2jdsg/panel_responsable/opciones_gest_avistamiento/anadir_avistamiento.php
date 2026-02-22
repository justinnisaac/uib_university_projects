<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../../BD2imo/login_registro/login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$id_gato = null;
$id_colonia = null;

$id_ayuntamiento = null;
$nombre_ayuntamiento = null;

// Conexión a la bbdd
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

$consulta = "
    SELECT 
        g.id_gato,
        c.id_colonia,
        g.nombre,
        g.id_estado,
        g.num_chip,
        g.descripcion_aspecto,
        g.url_foto
    FROM gato g
    JOIN historial_colonia hc ON hc.id_gato = g.id_gato
    JOIN colonia c ON c.id_colonia = hc.id_colonia
    WHERE c.id_ayuntamiento = '$id_ayuntamiento'
    AND g.id_estado != 4
    AND hc.fecha_salida IS NULL
    ORDER BY id_gato ASC
";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Añadir avistamiento</title>

    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display:flex; flex-direction:row;">

    <?php include("../panel_opciones_responsable.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">
                Gatos de <?php echo htmlspecialchars($nombre_ayuntamiento); ?>
            </h2>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID Gato</th>
                        <th>ID Colonia</th>
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

    $id_gato = $gato["id_gato"];
    $id_colonia = $gato["id_colonia"]?>

                    <tr>
                        <td><?php echo htmlspecialchars($gato["id_gato"]); ?></td>
                        <td><?php echo htmlspecialchars($gato["id_colonia"]); ?></td>
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
                        <td><button class="boton-mini" onclick="location.href='formulario_anadir_avistamiento.php?id=<?php echo $id_gato ?>&ayt=<?php echo $id_ayuntamiento ?>'">Avistamiento</button></td>
                    </tr>
<?php endwhile; ?>
<?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding:20px;">
                            No hay gatos en este ayuntamiento.
                        </td>
                    </tr>
<?php endif; ?>

                </tbody>
            </table>

            <div style="margin-top: 20px; text-align:center;">
                <button class="boton-gestion" onclick="location.href='../gestionar_avistamientos.php'">Volver atrás</button>
            </div>

        </div>
    </div>
</div>

</body>
</html>

<?php
mysqli_close($conexion);
?>