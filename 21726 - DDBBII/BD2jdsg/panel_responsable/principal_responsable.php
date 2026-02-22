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
           g.nombre as nombre_grupo,
           (
               SELECT COUNT(*) 
               FROM puede_hacer ph
               JOIN privilegios p ON ph.id_privilegios = p.id_privilegios
               WHERE ph.id_usuario = u.id_usuario 
                 AND p.privilegiosResponsable = 1
           ) as es_responsable
    FROM voluntario v
    JOIN usuario u ON v.id_voluntario = u.id_usuario
    JOIN grupo_control_felino g ON v.id_grupo = g.id_grupo
    WHERE v.id_grupo = '$id_grupo_seleccionado'
";

$resultado2 = mysqli_query($conexion, $consulta_miembros);

// 4. Mostrar mi nombre en el panel de bienvenida, necesitaré dado 
// mi id_usuario, obtener mi nombre completo.
$consulta_obtener_mi_nombre = "
    SELECT nombre, apellidos
    FROM usuario
    WHERE id_usuario = '$usuario'
";
$res_mi_nombre = mysqli_query($conexion, $consulta_obtener_mi_nombre);
if ($fila_mi_nombre = mysqli_fetch_array($res_mi_nombre)) {
    $nombre_completo = $fila_mi_nombre['nombre'] . ' ' . $fila_mi_nombre['apellidos'];
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Panel Responsable - Inicio</title>

    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>
    <div style="display: flex; flex-direction: row;">
        <?php include("panel_opciones_responsable.php"); ?>
        <div class="zona-contenido">

            <div class="contenedor-difuminado">

                 <!-- ICONO CONFIGURACIÓN -->
                <div style="display:flex; justify-content:flex-end;">
                    <img src="../../BD2imo/estilo/ajustes.jpg"
                        alt="Perfil"
                        title="Ver perfil"
                        style="width:30px; cursor:pointer;"
                        onclick="location.href='ver_responsable.php'">
                </div>

                <!-- Nombre del responsable -->
                <h2 class="titulo-dashboard">Bienvenido, <?php echo htmlspecialchars($nombre_completo); ?></h2>
                <!-- Nombre del grupo de trabajo y botón para editar -->
                <h2 class="titulo-dashboard">Grupo de trabajo <?php echo htmlspecialchars($resultado["nombre_grupo"]); ?></h2>
                <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Responsable</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 4. Recorrer los resultados
                    if ($resultado2 && mysqli_num_rows($resultado2) > 0) {
                        while($registro = mysqli_fetch_array($resultado2)) {
                            $id_voluntario = $registro["id_voluntario"];

                            // Determinar si es responsable (Si el conteo > 0, es Sí)
                            $texto_responsable = ($registro['es_responsable'] > 0) ? "Sí" : "No";
                            // Estilo visual simple para destacar a los responsables
                            $estilo_responsable = ($registro['es_responsable'] > 0) ? "font-weight: bold; color: #2E7D32;" : "";
                    ?>
                <tr>
                    <td><?php echo htmlspecialchars($id_voluntario); ?></td>
                    <td><?php echo htmlspecialchars($registro["nombre"]); ?></td>
                    <td><?php echo htmlspecialchars($registro["apellidos"]); ?></td>

                    <!-- Columna Responsable -->
                    <td style="<?php echo $estilo_responsable; ?>">
                        <?php echo $texto_responsable; ?>
                    </td>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>No hay voluntarios asignados a este grupo.</td></tr>";
                    }
                    ?>

                </tr>
                </tbody>
                </table>
                <div style="margin-top: 30px; text-align: center;">
                    <button class="boton-gestion" onclick="location.href='gestionar_grupo.php'">Editar el grupo</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php mysqli_close($conexion); ?>
