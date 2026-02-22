
<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../../BD2imo/login_registro/login.php");
    exit();
}

$id_veterinario = $_SESSION["id_usuario"];

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

$consulta = "
    SELECT DISTINCT
        iv.id_intervencion,
        iv.fecha,
        iv.comentario,
        iv.id_gato,
        iv.id_campana
    FROM intervencion_veterinaria iv
    JOIN veterinario_accion va ON iv.id_intervencion = va.id_intervencion
    AND va.id_veterinario = '$id_veterinario'
";

$resultado = mysqli_query($conexion, $consulta);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Intervenciones</title>
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>
<div style="display:flex;">
<?php include("panel_opciones_veterinario.php"); ?>

<div class="zona-contenido">
<div class="contenedor-difuminado">

<h2 class="titulo-dashboard"> Mis intervenciones veterinarias</h2>

<button class="boton-gestion boton-crear"
        onclick="location.href='opciones_intervenciones/crear_intervencion.php'">
    Crear intervención
</button>

<table class="tabla-colonias">
<thead>
<tr>
    <th>ID</th>
    <th>Fecha</th>
    <th>Gato</th>
    <th>Campaña</th>
    <th>Acciones</th>
</tr>
</thead>
<tbody>

<?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
<?php while ($fila = mysqli_fetch_array($resultado)): ?>
<tr>
    <td><?= $fila["id_intervencion"] ?></td>
    <td><?= $fila["fecha"] ?></td>
    <td><?= $fila["id_gato"] ?></td>
    <td><?= $fila["id_campana"] ?></td>
    <td>
        <button class="boton-mini"
                onclick="location.href='opciones_intervenciones/ver_intervencion.php?id=<?= $fila["id_intervencion"] ?>'">
            Consultar
        </button>
        <button class="boton-mini"
                onclick="location.href='opciones_intervenciones/editar_intervencion.php?id=<?= $fila["id_intervencion"] ?>'">
            Editar
        </button>
        <button class="boton-mini"
                onclick="location.href='opciones_intervenciones/eliminar_intervencion.php?id=<?= $fila["id_intervencion"] ?>'">
            Eliminar
        </button>
    </td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="5" style="text-align:center;">No hay intervenciones</td></tr>
<?php endif; ?>

</tbody>
</table>

</div>
</div>
</div>
</body>
</html>
<?php mysqli_close($conexion); ?>
