<?php
// Iniciar sesión
session_start();

// Seguridad: Verificar que hay un ayuntamiento en la sesión
if (!isset($_SESSION["id_ayuntamiento"])) {
    header("Location: ../../login_registro/login.php"); 
    exit();
}

// Recuperar datos de sesión y GET
$id_ayuntamiento = $_SESSION["id_ayuntamiento"];
$id_grupo_seleccionado = $_GET['id'] ?? '';
$nombre_grupo = "Grupo desconocido";

// 1. Establecer conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// 2. Obtener el nombre del grupo y verificar que pertenece al ayuntamiento actual (Seguridad)
if (!empty($id_grupo_seleccionado)) {
    $consulta_grupo = "SELECT nombre FROM grupo_control_felino 
                       WHERE id_grupo = '$id_grupo_seleccionado' 
                       AND id_ayuntamiento = '$id_ayuntamiento'";
    $res_grupo = mysqli_query($conexion, $consulta_grupo);
    
    if ($fila = mysqli_fetch_array($res_grupo)) {
        $nombre_grupo = $fila['nombre'];
    } else {
        // Si el grupo no existe o no es de este ayuntamiento, volvemos atrás
        header("Location: ../gestionar_grupos.php");
        exit();
    }
} else {
    header("Location: ../gestionar_grupos.php");
    exit();
}

// 3. Consulta Principal: Obtener voluntarios y verificar si son RESPONSABLES
// Usamos una subconsulta para determinar si el usuario tiene el privilegio de Responsable
// Lo hacemos porque más adelante mostraremos al responsable distinguido de los voluntarios
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

$resultado = mysqli_query($conexion, $consulta_miembros);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Miembros de <?php echo htmlspecialchars($nombre_grupo); ?></title>

    <link rel="stylesheet" href="../../estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../estilo/estilo_contenido.css">
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../panel_opciones.php"); ?>

    <!-- Contenido -->
    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Miembros del grupo: <?php echo htmlspecialchars($nombre_grupo); ?></h2>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Responsable</th> 
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
<?php
// 4. Recorrer los resultados
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while($registro = mysqli_fetch_array($resultado)) {
        $id_voluntario = $registro["id_voluntario"];
        
        // Determinar si es responsable (Si el conteo > 0, es Sí)
        $texto_responsable = ($registro['es_responsable'] > 0) ? "Sí" : "No";
        // Estilo visual para destacar a los responsables
        $estilo_responsable = ($registro['es_responsable'] > 0) ? "font-weight: bold; color: #2E7D32;" : "";
?>
                    <tr>
                        <td><?php echo htmlspecialchars($id_voluntario); ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["apellidos"]); ?></td>
                        <td style="<?php echo $estilo_responsable; ?>">
                            <?php echo $texto_responsable; ?>
                        </td>
                        
                        <td>
                            <div class="acciones-container">
                                <!-- Botón Detalles -->
                                <button class="boton-mini" onclick="location.href='detalles_usuario.php?id=<?php echo urlencode($id_voluntario); ?>&grupo=<?php echo urlencode($id_grupo_seleccionado); ?>'">Detalles</button>
                            </div>
                        </td>
                    </tr>
<?php
    } 
} else {
    echo "<tr><td colspan='6' style='text-align:center; padding:20px;'>No hay voluntarios asignados a este grupo.</td></tr>";
}
?>
                </tbody>
            </table>

            <!-- Botón para volver atrás -->
            <div style="margin-top: 30px; text-align: center;">
                <button class="boton-gestion" style="width: 200px;" onclick="location.href='../gestionar_grupos.php'">Volver a Grupos</button>
            </div>

<?php
// 5. Cerrar la conexión
mysqli_close($conexion); 
?>
        </div>
    </div>
</div>
</body>
</html>