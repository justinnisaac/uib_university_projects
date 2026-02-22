<?php
// Iniciar sesión
session_start();

// Seguridad: Verificar sesión del ayuntamiento
if (!isset($_SESSION["id_ayuntamiento"])) {
    header("Location: ../login_registro/login.php"); 
    exit();
}

// Recuperamos los datos de la sesión
$usuario = $_SESSION["usuario"] ?? "";
$id_ayuntamiento = $_SESSION["id_ayuntamiento"];
$nombre_municipio = $_SESSION["nombre_municipio"] ?? "su municipio";

// 1. Establecer la conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// 2. Obtener el id del municipio del Ayuntamiento actual
$consulta_id_muni = "SELECT id_municipio FROM ayuntamiento WHERE id_ayuntamiento = '$id_ayuntamiento'";
$res_muni = mysqli_query($conexion, $consulta_id_muni);
$id_municipio_actual = "";

if ($fila = mysqli_fetch_array($res_muni)) {
    $id_municipio_actual = $fila['id_municipio'];
}

// 3. Consultar todas las campañas que han ocurrido en este municipio
// Por ejemplo, si estamos en el Ayuntamiento de Palma y queremos ver las campañas,
// que no salgan las de Petra.
$consulta_campanas = "
    SELECT c.id_campana, 
           c.nombre, 
           c.id_centro_veterinario,
           cv.nombre AS nombre_centro,
           tc.id_tipo_campana,
           tc.tipo, 
           c.fechaInicio, 
           c.fechaFin, 
           c.tipoVacunacion
    FROM campana c
    JOIN tipo_campana tc ON c.id_tipo_campana = tc.id_tipo_campana  
    JOIN centro_veterinario cv ON c.id_centro_veterinario = cv.id_centro
    AND cv.id_municipio = '$id_municipio_actual'
";

$resultado = mysqli_query($conexion, $consulta_campanas);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestión de Campañas</title>

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
            <h2 class="titulo-dashboard">Campañas realizadas por centros veterinarios en <?php echo htmlspecialchars($nombre_municipio); ?></h2>
            
            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Vacunación</th>
                        <th>C.Veterinario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
<?php
// 4. Recorrer los resultados
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while($registro = mysqli_fetch_array($resultado)) {
        $id_campana = $registro["id_campana"];
        $id_centro = $registro["id_centro_veterinario"];
        // Formateo visual para tipo de vacunación (si es NULL o vacío ponemos un guion)
        $vacunacion = $registro["tipoVacunacion"];
        if (empty($vacunacion)) {
            $vacunacion = "-";
        }
?>
                    <tr>
                        <td><?php echo htmlspecialchars($id_campana); ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["tipo"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["fechaInicio"]); ?></td>
                        <td><?php echo htmlspecialchars($registro["fechaFin"]); ?></td>
                        <td><?php echo htmlspecialchars($vacunacion); ?></td>
                        <th><?php echo htmlspecialchars($registro["nombre_centro"]); ?></td>
                        <td>
                            <div class="acciones-container">
                                <!-- Botón Añadir participación -->
                                <?php if(is_null($registro["fechaFin"])): ?>
                                <button class="boton-mini" onclick="location.href='../../BD2jdsg/panel_ayuntamiento/anadir_participantes.php?id=<?php echo urlencode($id_campana); ?>&cve=<?php echo urlencode($id_centro); ?>'">Añadir participantes</button>
                                <?php endif; ?> 
                                <!-- Botón Más detalles -->
                                <button class="boton-mini" onclick="location.href='opciones_gest_campanas/detalles_campana.php?id=<?php echo urlencode($id_campana); ?>'">Detalles</button>
                                <!-- Botón Finalizar campaña -->
                                <?php if(is_null($registro["fechaFin"])): ?>
                                <button class="boton-mini" onclick="location.href='../../BD2jdsg/panel_ayuntamiento/confirmacion_finalizar_campana.php?id=<?php echo urlencode($id_campana); ?>&cve=<?php echo urlencode($id_centro); ?>'">Finalizar</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
<?php
    } 
} else {
    // Mensaje si no hay campañas en este municipio
    echo "<tr><td colspan='7' style='text-align:center; padding:20px;'>No hay campañas registradas por centros de este municipio.</td></tr>";
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