<?php
// INICIAR SESIÓN para acceder al contexto del Ayto (su id y nombre del municipio).
session_start();

// Seguridad: Si no hay ID de ayuntamiento en la sesión, el acceso es inválido.
if (!isset($_SESSION["id_ayuntamiento"])) {
    header("Location: ../login_registro/login.php"); 
    exit();
}

// Recuperamos los datos de la sesión para usarlos
$usuario = $_SESSION["usuario"] ?? "";
$id_ayuntamiento = $_SESSION["id_ayuntamiento"];
$nombre_ciudad = $_SESSION["nombre_municipio"] ?? "su municipio";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestionar Colonias</title>

    <link rel="stylesheet" href="../estilo/estilo_panel.css">
    <link rel="stylesheet" href="../estilo/estilo_contenido.css">
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel de opciones lateral -->
    <?php include("panel_opciones.php"); ?>

    <!-- Contenido -->
    <div class="zona-contenido">

        <div class="contenedor-difuminado">
            <h2 class="titulo-dashboard">Gestión de colonias felinas de <?php echo htmlspecialchars($nombre_ciudad); ?></h2>
            <button class="boton-gestion boton-crear" onclick="location.href='opciones_gest_colonias/crear_colonia.php'">Crear nueva colonia</button>
            
<?php
// 1. Establecer la conexión con el servidor
$conexion = mysqli_connect("localhost", "root", ""); 

// 2. Seleccionar la base de datos 'BD2XAMPPions'
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// 3. Definir la consulta FILTRADA por el ID de SESIÓN
$consulta = "SELECT id_colonia, nombre_colonia 
             FROM colonia 
             WHERE id_ayuntamiento = '$id_ayuntamiento'"; 

// 4. Ejecutar la consulta (combinar con parte de estilo)
$resultado = mysqli_query($conexion, $consulta);
?>
            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
<?php
// 5. Recorrer los resultados y generar una fila por cada colonia
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while($registro = mysqli_fetch_array($resultado)) {
        $id_colonia = $registro["id_colonia"];
?>
                    <tr>
                        <td><?php echo $id_colonia; ?></td>
                        <td><?php echo $registro["nombre_colonia"]; ?></td>
                        <td>
                            <div class="acciones-container">
                                <button class="boton-mini" onclick="location.href='opciones_gest_colonias/ver_colonia.php?id=<?php echo $id_colonia; ?>'">Detalles</button>
                                <button class="boton-mini" onclick="location.href='opciones_gest_colonias/editar_colonia.php?id=<?php echo $id_colonia; ?>'">Editar</button>
                            </div>
                        </td>
                    </tr>
<?php
    } 
} else {
    // Mensaje si no hay colonias para este ayuntamiento
    echo "<tr><td colspan='3' style='text-align:center; padding:20px;'>No se encontraron colonias asociadas a este ayuntamiento.</td></tr>";
}
?>
                </tbody>
            </table>

<?php
// 6. Cerrar la conexión
mysqli_close($conexion); 
?>

        </div>
    </div>
</div>
</body>
</html>