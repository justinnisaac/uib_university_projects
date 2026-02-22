
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
    g.id_grupo,
    COUNT(v.id_voluntario) AS cantidad_miembros,
    g.nombre AS nombre_grupo,
    a.nombre AS nombre_ayuntamiento
FROM grupo_control_felino g
JOIN voluntario v ON v.id_grupo = g.id_grupo
JOIN ayuntamiento a ON g.id_ayuntamiento = a.id_ayuntamiento
AND g.id_grupo = (
    SELECT id_grupo FROM voluntario WHERE id_voluntario = '$id_usuario'
)
GROUP BY g.id_grupo, g.nombre, a.nombre
";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mi grupo de control felino</title>

    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display:flex; flex-direction:row;">

    <?php include("panel_opciones_voluntario.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Mi grupo de control felino</h2>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID Grupo</th>
                        <th>Nombre del grupo</th>
                        <th>Cantidad de miembros</th>
                        <th>Ayuntamiento</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>

<?php
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($registro = mysqli_fetch_array($resultado)) {
?>
                    <tr>
                        <td><?php echo htmlspecialchars($registro["id_grupo"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre_grupo"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["cantidad_miembros"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre_ayuntamiento"]); ?></td>
                        <td>
                            <div class="acciones-container">
                                
                                <?php 
                                // Botón 1: Ver miembros (Sólo si hay al menos 1 miembro)
                                if ($registro["cantidad_miembros"] >= 1) { 
                                ?>
                                    <button class="boton-mini" onclick="location.href='ver_miembros_grupo.php?id=<?php echo urlencode($registro["id_grupo"]); ?>'">Ver miembros</button>
                                <?php 
                                } 
                                ?>

                            </div>
                        </td>
                    </tr>
<?php
    }
} else {
    echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>
            No perteneces a ningún grupo.
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
