<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../BD2imo/login_registro/login.php");
    exit();
}

$id_voluntario = $_SESSION["id_usuario"];

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

$consulta = "
    SELECT 
        t.id_tarea,
        t.nombre,
        t.descripcion,
        t.completada,
        c.nombre_colonia,
        a.nombre AS nombre_ayuntamiento
    FROM tarea t
    JOIN colonia c ON t.id_colonia = c.id_colonia
    JOIN ayuntamiento a ON c.id_ayuntamiento = a.id_ayuntamiento
    AND t.id_voluntario = '$id_voluntario'
";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mis tareas</title>

    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display:flex; flex-direction:row;">

    <?php include("panel_opciones_voluntario.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Mis tareas</h2>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tarea</th>
                        <th>Descripción</th>
                        <th>Colonia</th>
                        <th>Ayuntamiento</th>
                        <th>Completada</th>
                    </tr>
                </thead>
                <tbody>

<?php
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($registro = mysqli_fetch_array($resultado)) {
?>
                    <tr>
                        <td><?php echo $registro["id_tarea"]; ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["descripcion"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre_colonia"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre_ayuntamiento"]); ?></td>
                        <td style="text-align:center;">
                            <?php if (!$registro["completada"]) : ?>
                                <form method="POST" action="vol_marcar_tarea.php">
                                    <input type="hidden" name="id_tarea"
                                           value="<?php echo $registro["id_tarea"]; ?>">
                                    <input type="checkbox" onchange="this.form.submit()">
                                </form>
                            <?php else : ?>
                                ✔
                            <?php endif; ?>
                        </td>
                    </tr>
<?php
    }
} else {
    echo "<tr>
            <td colspan='6' style='text-align:center; padding:20px;'>
                No tienes tareas asignadas.
            </td>
          </tr>";
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
