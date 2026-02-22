<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../../BD2imo/login_registro/login.php");
    exit();
}

$id_responsable = $_SESSION["id_usuario"];

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

$consulta = "
    SELECT
        sr.id_solicitud,
        sr.fecha_solicitud,
        sr.comentarios,
        sr.aprobada,
        g.id_gato,
        g.nombre AS nombre_gato,
        eg.estado AS estado_gato
    FROM solicitud_retirada sr
    JOIN gato g ON sr.id_gato = g.id_gato
    JOIN estado_gato eg ON g.id_estado = eg.id_estado
    JOIN voluntario v ON sr.id_responsable = v.id_voluntario
    WHERE v.id_voluntario = '$id_responsable'
    ORDER BY sr.fecha_solicitud DESC
";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mis solicitudes de retirada</title>

    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display:flex; flex-direction:row;">

    <?php include("../panel_opciones_responsable.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">
                Mis solicitudes de retirada
            </h2>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID Solicitud</th>
                        <th>Fecha</th>
                        <th>Estado solicitud</th>
                        <th>ID Gato</th>
                        <th>Gato</th>
                        <th>Comentarios</th>
                    </tr>
                </thead>
                <tbody>

<?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
<?php while ($fila = mysqli_fetch_assoc($resultado)): ?>

                    <tr>
                        <td><?php echo htmlspecialchars($fila["id_solicitud"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["fecha_solicitud"]); ?></td>
                        <td>
                            <?php echo $fila["aprobada"] ? "Aprobada" : "Pendiente"; ?>
                        </td>
                        <td><?php echo htmlspecialchars($fila["id_gato"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["nombre_gato"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["comentarios"]); ?></td>
                    </tr>

<?php endwhile; ?>
<?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:20px;">
                            No has registrado solicitudes de retirada.
                        </td>
                    </tr>
<?php endif; ?>

                </tbody>
            </table>

            <div style="margin-top: 20px; text-align:center;">
                <button class="boton-gestion"
                        onclick="location.href='../gestionar_retirada.php'">
                    Volver atrás
                </button>
            </div>

        </div>
    </div>
</div>

</body>
</html>

<?php
mysqli_close($conexion);
?>