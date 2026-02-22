
<?php
// INICIAR SESIÓN
session_start();

// Seguridad: Verificar que hay un ayuntamiento en la sesión
if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../../BD2imo/login_registro/login.php");
    exit();
}

// Recuperar datos de sesión y GET
$id_usuario = $_SESSION["id_usuario"];
$id_grupo_seleccionado = $_GET['id'] ?? '';
$nombre_grupo = "Grupo desconocido";

// 1. Establecer conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// 2. Obtener el nombre del grupo 
if (!empty($id_grupo_seleccionado)) {
    $consulta_grupo = "SELECT nombre FROM grupo_control_felino 
                       WHERE id_grupo = '$id_grupo_seleccionado'";
    $res_grupo = mysqli_query($conexion, $consulta_grupo);
    
    if ($fila = mysqli_fetch_array($res_grupo)) {
        $nombre_grupo = $fila['nombre'];
    } else {
        // Si el grupo no existe o no es de este ayuntamiento, volvemos atrás
        header("Location: vol_grupo.php");
        exit();
    }
} else {
    header("Location: vol_grupo.php");
    exit();
}

// 3. Consulta Principal: Obtener voluntarios y verificar si son RESPONSABLES
// Usamos una subconsulta para determinar si el usuario tiene el privilegio de Responsable
$consulta_miembros = "
    SELECT v.id_voluntario, 
           u.nombre, 
           u.apellidos,
           (
               SELECT COUNT(*) 
               FROM puede_hacer ph
               JOIN privilegios p ON ph.id_privilegios = p.id_privilegios
               AND ph.id_usuario = u.id_usuario 
                 AND p.privilegiosResponsable = 1
           ) as es_responsable
    FROM voluntario v
    JOIN usuario u ON v.id_voluntario = u.id_usuario
    JOIN grupo_control_felino g ON v.id_grupo = g.id_grupo
    AND v.id_grupo = '$id_grupo_seleccionado'
";

$resultado = mysqli_query($conexion, $consulta_miembros);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Miembros de <?php echo htmlspecialchars($nombre_grupo); ?></title>

    <!-- CSS generales (Rutas desde la raíz) -->
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("panel_opciones_voluntario.php"); ?>

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
                        
                
                    </tr>
<?php
    } 
} else {
    echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>No hay voluntarios asignados a este grupo.</td></tr>";
}
?>
                </tbody>
            </table>

            <!-- Botón para volver atrás -->
            <div style="margin-top: 30px; text-align: center;">
                <button class="boton-gestion" style="width: 200px;" onclick="location.href='vol_grupo.php'">Volver a Grupo</button>
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