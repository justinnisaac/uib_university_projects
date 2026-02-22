<?php
// INICIAR SESIÓN
session_start();

// Seguridad: Si no hay ID de ayuntamiento en la sesión, el acceso es inválido.
if (!isset($_SESSION["id_ayuntamiento"])) {
    // Redirigir a la página de login
    header("Location: ../login_registro/login.php"); 
    exit();
}

// Recuperamos los datos de la sesión
$usuario = $_SESSION["usuario"] ?? "";
$id_ayuntamiento = $_SESSION["id_ayuntamiento"];
$nombre_ciudad = $_SESSION["nombre_municipio"] ?? "su municipio"; 
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestión del Borsín</title>

    <link rel="stylesheet" href="../estilo/estilo_panel.css">
    <link rel="stylesheet" href="../estilo/estilo_contenido.css">
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("panel_opciones.php"); ?>

    <!-- Contenido -->
    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <!-- Título  -->
            <h2 class="titulo-dashboard">Gestión del bolsín de <?php echo htmlspecialchars($nombre_ciudad); ?></h2>

<?php
// 1. Establecer la conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// --- ESTADÍSTICAS DEL BORSÍN ---

// A. Contar voluntarios CON grupo asignado
$consulta_con_grupo = "
    SELECT COUNT(v.id_voluntario) as total
    FROM voluntario v
    JOIN borsin_voluntarios b ON v.id_borsin = b.id_borsin
    WHERE b.id_ayuntamiento = '$id_ayuntamiento' 
      AND v.id_grupo IS NOT NULL
";
$res_con = mysqli_query($conexion, $consulta_con_grupo);
$num_con_grupo = mysqli_fetch_array($res_con)['total'];

// B. Contar voluntarios SIN grupo asignado (id_grupo IS NULL)
$consulta_sin_grupo = "
    SELECT COUNT(v.id_voluntario) as total
    FROM voluntario v
    JOIN borsin_voluntarios b ON v.id_borsin = b.id_borsin
    WHERE b.id_ayuntamiento = '$id_ayuntamiento' 
      AND v.id_grupo IS NULL
";
$res_sin = mysqli_query($conexion, $consulta_sin_grupo);
$num_sin_grupo = mysqli_fetch_array($res_sin)['total'];
?>

            <!-- Tabla de Estadísticas -->
            <table class="tabla-colonias" style="margin-bottom: 40px;">
                <thead>
                    <tr>
                        <th>Nº de voluntarios con grupo</th>
                        <th>Nº de voluntarios sin grupo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: center; font-weight: bold; font-size: 20px;"><?php echo $num_con_grupo; ?></td>
                        <td style="text-align: center; font-weight: bold; font-size: 20px;"><?php echo $num_sin_grupo; ?></td>
                    </tr>
                </tbody>
            </table>

<?php
// 2. Definir la consulta SELECT con JOINS y COALESCE para la lista completa
$consulta = "
    SELECT v.id_voluntario, 
           u.nombre, 
           u.apellidos, 
           COALESCE(g.nombre, '-') as nombre_grupo
    FROM voluntario v
    JOIN usuario u ON v.id_voluntario = u.id_usuario
    JOIN borsin_voluntarios b ON v.id_borsin = b.id_borsin
    LEFT JOIN grupo_control_felino g ON v.id_grupo = g.id_grupo
    WHERE b.id_ayuntamiento = '$id_ayuntamiento'
"; 

// 3. Ejecutar la consulta
$resultado = mysqli_query($conexion, $consulta);
?>

            <!-- Reutilizamos la clase .tabla-colonias para mantener el mismo estilo visual -->
            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Grupo de Trabajo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
<?php
// 4. Recorrer los resultados
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while($registro = mysqli_fetch_array($resultado)) {
        $id_voluntario = $registro["id_voluntario"];
?>
                    <tr>
                        <td><?php echo htmlspecialchars($id_voluntario); ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["apellidos"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre_grupo"]); ?></td>
                        <td>
                            <div class="acciones-container">
                                <button class="boton-mini" onclick="location.href='opciones_gest_borsin/detalles_usuario.php?id=<?php echo urlencode($id_voluntario); ?>'">Detalles</button>
                            </div>
                        </td>
                    </tr>
<?php
    } 
} else {
    // Mensaje si no hay voluntarios en el borsín de este ayuntamiento
    echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>No hay voluntarios registrados en el borsín de este ayuntamiento.</td></tr>";
}
?>
                </tbody>
            </table>

<?php
// 5. Cerrar la conexión
mysqli_close($conexion); 
?>

        </div>
    </div>
</div>
</body>
</html>