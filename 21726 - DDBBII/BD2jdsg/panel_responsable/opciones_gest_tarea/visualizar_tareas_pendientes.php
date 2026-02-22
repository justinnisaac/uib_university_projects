<?php
session_start();

// 1. Comprobar sesión
if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../../BD2imo/login_registro/login.html"); 
    exit();
}

$id_responsable = $_SESSION["id_usuario"];

// 2. Conexión
$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

// 3. Obtener tareas pendientes del grupo del responsable
$consulta = "
    SELECT 
        t.id_tarea,
        t.nombre AS nombre_tarea,
        t.descripcion,
        u.nombre AS nombre_voluntario,
        u.apellidos AS apellidos_voluntario,
        c.nombre_colonia,
        c.id_colonia
    FROM tarea t
    JOIN voluntario v ON t.id_voluntario = v.id_voluntario
    JOIN usuario u ON v.id_voluntario = u.id_usuario
    JOIN colonia c ON t.id_colonia = c.id_colonia
    WHERE t.completada = FALSE
    AND v.id_grupo = (
        SELECT id_grupo 
        FROM voluntario 
        WHERE id_voluntario = '$id_responsable'
    )
    ORDER BY t.id_tarea DESC
";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tareas pendientes</title>

    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>
<div style="display: flex; flex-direction: row;">

    <?php include("../panel_opciones_responsable.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Tareas pendientes asignadas</h2>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tarea</th>
                        <th>Descripción</th>
                        <th>Voluntario</th>
                        <th>Colonia</th>
                    </tr>
                </thead>
                <tbody>

                <?php
                if ($resultado && mysqli_num_rows($resultado) > 0) {
                    while ($fila = mysqli_fetch_assoc($resultado)) {
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila["id_tarea"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["nombre_tarea"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["descripcion"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["nombre_voluntario"] . " " . $fila["apellidos_voluntario"]); ?></td>
                        <td><?php echo htmlspecialchars($fila["id_colonia"]); ?></td>
                    </tr>
                <?php
                    }
                } else {
                    echo "
                    <tr>
                        <td colspan='6' style='text-align:center; padding:20px;'>
                            No hay tareas pendientes asignadas.
                        </td>
                    </tr>";
                }
                ?>

                </tbody>
            </table>
            <!-- Botón para volver atrás -->
            <div style="margin-top: 15px; text-align: center;">
                <button class="boton-gestion" style="width: 200px;" onclick="location.href='../gestionar_tareas.php'">Volver atrás</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<?php mysqli_close($conexion); ?>