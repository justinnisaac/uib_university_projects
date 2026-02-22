<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../BD2imo/login_registro/login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$id_ayuntamiento = null;
$nombre_ayuntamiento = null;

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

// Obtener el ayuntamiento del responsable
$consulta_ayto = "
    SELECT a.id_ayuntamiento, a.nombre
    FROM ayuntamiento a
    JOIN borsin_voluntarios b ON b.id_ayuntamiento = a.id_ayuntamiento
    JOIN voluntario v ON v.id_borsin = b.id_borsin
    WHERE v.id_voluntario = '$id_usuario'
";

$resultado_ayto = mysqli_query($conexion, $consulta_ayto);
$fila_ayto = mysqli_fetch_assoc($resultado_ayto);
$id_ayuntamiento = $fila_ayto["id_ayuntamiento"];
$nombre_ayuntamiento = $fila_ayto["nombre"];

// Obtener los centros veterinarios del mismo ayuntamiento
$consulta_centros = "
    SELECT cv.id_centro, cv.nombre, cv.direccion, cv.telefono, cv.email, m.nombre AS municipio
    FROM centro_veterinario cv
    JOIN municipio m ON cv.id_municipio = m.id_municipio
    JOIN ayuntamiento a ON m.id_municipio = a.id_municipio
    WHERE a.id_ayuntamiento = '$id_ayuntamiento'
";

$resultado_centros = mysqli_query($conexion, $consulta_centros);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Centros Veterinarios</title>
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>
<body>

<div style="display:flex; flex-direction:row;">

    <?php include("panel_opciones_responsable.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Centros veterinarios de <?php echo htmlspecialchars($nombre_ayuntamiento); ?></h2>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
<?php
if ($resultado_centros && mysqli_num_rows($resultado_centros) > 0) {
    while ($centro = mysqli_fetch_assoc($resultado_centros)) {
?>
                    <tr>
                        <td><?php echo htmlspecialchars($centro["id_centro"]); ?></td>
                        <td><?php echo htmlspecialchars($centro["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($centro["direccion"]); ?></td>
                        <td><?php echo htmlspecialchars($centro["telefono"]); ?></td>
                        <td><?php echo htmlspecialchars($centro["email"]); ?></td>
                    </tr>
<?php
    }
} else {
    echo "<tr><td colspan='6' style='text-align:center; padding:20px;'>No hay centros veterinarios asociados a tu ayuntamiento.</td></tr>";
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