<?php
session_start();

// 1. Verificamos si el usuario ha iniciado sesión
if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../../BD2imo/login_registro/login.html"); 
    exit();
}

// Recuperamos los datos esenciales de la sesión
$usuario = $_SESSION["id_usuario"];
$id_grupo_seleccionado = $_GET['id'] ?? null;
$id_usuario = $_SESSION["id_usuario"];  
$nombre_grupo = "Grupo desconocido"; 

// Conectar a MySQL
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions");

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

// Obtener información del grupo de trabajo al que pertenece el responsable
$consulta = "
    SELECT
    g.id_grupo,
    COUNT(v.id_voluntario) AS cantidad_miembros,
    g.nombre AS nombre_grupo,
    a.nombre AS nombre_ayuntamiento
FROM grupo_control_felino g
JOIN voluntario v ON v.id_grupo = g.id_grupo
JOIN ayuntamiento a ON g.id_ayuntamiento = a.id_ayuntamiento
WHERE g.id_grupo = (
    SELECT id_grupo FROM voluntario WHERE id_voluntario = '$id_usuario'
)
GROUP BY g.id_grupo, g.nombre, a.nombre
";

$resultado = mysqli_query($conexion, $consulta);

$resultado = mysqli_fetch_array($resultado);

$id_grupo_seleccionado = $resultado['id_grupo'];

// 3. Consulta Principal: Obtener voluntarios y verificar si son RESPONSABLES
// Usamos una subconsulta para determinar si el usuario tiene el privilegio de Responsable
$consulta_miembros = "
    SELECT v.id_voluntario, 
        u.nombre, 
        u.apellidos,
        g.nombre AS nombre_grupo
    FROM voluntario v
    JOIN usuario u ON v.id_voluntario = u.id_usuario
    JOIN grupo_control_felino g ON v.id_grupo = g.id_grupo
    WHERE v.id_grupo = '$id_grupo_seleccionado'
    AND NOT EXISTS (
        SELECT 1
        FROM puede_hacer ph
        JOIN privilegios p ON ph.id_privilegios = p.id_privilegios
        WHERE ph.id_usuario = u.id_usuario
        AND p.privilegiosResponsable = 1
)";

$resultado2 = mysqli_query($conexion, $consulta_miembros);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tareas</title>

    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>
    <div style="display: flex; flex-direction: row;">
        <?php include("panel_opciones_responsable.php"); ?>
        <div class="zona-contenido">

            <div class="contenedor-difuminado">
                <!-- Nombre del grupo de trabajo y botón para editar -->
                <h2 class="titulo-dashboard">Voluntarios de tu grupo de trabajo</h2>
                <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Acciones</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 4. Recorrer los resultados
                    if ($resultado2 && mysqli_num_rows($resultado2) > 0) {
                        while($registro = mysqli_fetch_array($resultado2)) {
                            $id_voluntario = $registro["id_voluntario"];

                    ?>
                <tr>
                    <td><?php echo htmlspecialchars($id_voluntario); ?></td>
                    <td><?php echo htmlspecialchars($registro["nombre"]); ?></td>
                    <td><?php echo htmlspecialchars($registro["apellidos"]); ?></td>
                    <td><button class="boton-mini" onclick="location.href='opciones_gest_tarea/anadir_tarea.php?id=<?php echo $id_voluntario; ?>&ayt=<?php echo $id_ayuntamiento; ?>'">Añadir tarea</button></td>

                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>No hay voluntarios asignados a este grupo.</td></tr>";
                    }
                    ?>

                </tr>
                </tbody>
                </table>
                <!-- Botón para visualizar las tareas encargadas por el responsable pendientes -->
                <div style="margin-top: 15px; text-align: center;">
                    <button class="boton-gestion" style="width: 200px;" onclick="location.href='opciones_gest_tarea/visualizar_tareas_pendientes.php'">Tareas asignadas pendientes</button>
                </div>
                <!-- Botón para visualizar las tareas encargadas por el responsable históricas -->
                <div style="margin-top: 15px; text-align: center;">
                    <button class="boton-gestion" style="width: 200px;" onclick="location.href='opciones_gest_tarea/visualizar_tareas_completadas.php'">Tareas completadas</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php mysqli_close($conexion); ?>