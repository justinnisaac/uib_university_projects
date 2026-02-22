<?php
session_start();

// Seguridad: Verificar sesión de veterinario
if (!isset($_SESSION["usuario"]) || !isset($_SESSION["id_municipio_vet"])) {
    header("Location: ../login_registro/login.php");
    exit();
}

$usuario = $_SESSION["usuario"];
$id_municipio_vet = $_SESSION["id_municipio_vet"];
$nombre_municipio = $_SESSION["nombre_municipio_vet"] ?? "tu municipio";

// Conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// Consulta para obtener cementerios y número de gatos descansando en cada uno
// Filtramos por el municipio del veterinario
// Usamos LEFT JOIN con 'retirada' para contar cuántos gatos hay en cada uno
$consulta = "
    SELECT c.id_cementerio, 
           c.nombre, 
           c.direccion, 
            COUNT(sr.id_gato) AS num_gatos_descansando
    FROM cementerio c
        LEFT JOIN retirada r ON c.id_cementerio = r.id_cementerio
        LEFT JOIN solicitud_retirada sr ON r.id_solicitud = sr.id_solicitud
    WHERE c.id_municipio = '$id_municipio_vet'
    GROUP BY c.id_cementerio, c.nombre, c.direccion
";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestión de Cementerios</title>
    
    <link rel="stylesheet" href="../estilo/estilo_panel.css">
    <link rel="stylesheet" href="../estilo/estilo_contenido.css">
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../../BD2npb/panel_veterinario/panel_opciones_veterinario.php"); ?>

    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <!-- Título -->
            <h2 class="titulo-dashboard">Cementerios de <?php echo htmlspecialchars($nombre_municipio); ?></h2>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Núm. gatos descansando</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
<?php
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while($row = mysqli_fetch_array($resultado)) {
        $id_cementerio = $row['id_cementerio'];
        $nombre = $row['nombre'];
        $direccion = $row['direccion'];
        $num_gatos = $row['num_gatos_descansando'];
?>
                    <tr>
                        <td><?php echo htmlspecialchars($id_cementerio); ?></td>
                        <td><?php echo htmlspecialchars($nombre); ?></td>
                        <td><?php echo htmlspecialchars($direccion); ?></td>
                        <td style="font-weight: bold; text-align: center;"><?php echo htmlspecialchars($num_gatos); ?></td>
                        <td>
                            <div class="acciones-container">
                                <?php if ($num_gatos > 0): ?>
                                    <!-- Botón Lista de Gatos: Solo visible si hay como mínimo un gato -->
                                    <button class="boton-mini" 
                                            onclick="location.href='opciones_gest_cementerios/lista_gatos_descansando.php?id=<?php echo $id_cementerio; ?>'">
                                        Lista de gatos
                                    </button>
                                <?php else: ?>
                                    <span style="color: #999; font-size: 14px; font-style: italic;">Sin registros</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
<?php
    }
} else {
    echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>No hay cementerios registrados en tu municipio.</td></tr>";
}
?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
mysqli_close($conexion);
?>

</body>
</html>