<?php
session_start();

// Seguridad: Verificar sesión
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../login_registro/login.php");
    exit();
}

$id_cementerio = $_GET['id'] ?? '';
$nombre_cementerio = "Cementerio desconocido";

// Conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// 1. Obtener nombre del cementerio
if (!empty($id_cementerio)) {
    $q_cem = "SELECT nombre FROM cementerio WHERE id_cementerio = '$id_cementerio'";
    $res_cem = mysqli_query($conexion, $q_cem);
    if ($row = mysqli_fetch_array($res_cem)) {
        $nombre_cementerio = $row['nombre'];
    } else {
        header("Location: ../gestionar_cementerios.php");
        exit();
    }
} else {
    header("Location: ../gestionar_cementerios.php");
    exit();
}

// 2. Obtener lista de gatos en ese cementerio
$consulta_gatos = "
    SELECT g.id_gato, g.nombre
    FROM retirada r
    JOIN solicitud_retirada sr ON r.id_solicitud = sr.id_solicitud
    JOIN gato g ON sr.id_gato = g.id_gato
    WHERE r.id_cementerio = '$id_cementerio'
";
$resultado = mysqli_query($conexion, $consulta_gatos);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gatos en <?php echo htmlspecialchars($nombre_cementerio); ?></title>
    <link rel="stylesheet" href="../../estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../estilo/estilo_contenido.css">
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../../../BD2npb/panel_veterinario/panel_opciones_veterinario.php"); ?>

    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Gatos descansando en <?php echo htmlspecialchars($nombre_cementerio); ?></h2>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
<?php
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while($row = mysqli_fetch_array($resultado)) {
        $id_gato = $row['id_gato'];
        $nombre_gato = $row['nombre'];
?>
                    <tr>
                        <td><?php echo htmlspecialchars($id_gato); ?></td>
                        <td><?php echo htmlspecialchars($nombre_gato); ?></td>
                        <td>
                            <div class="acciones-container">
                                <!-- Botón Detalles -->
                                <button class="boton-mini" 
                                        onclick="location.href='ver_gato.php?id=<?php echo $id_gato; ?>&id_cementerio=<?php echo $id_cementerio; ?>'">
                                    Detalles
                                </button>
                            </div>
                        </td>
                    </tr>
<?php
    }
} else {
    echo "<tr><td colspan='3' style='text-align:center; padding:20px;'>No hay gatos registrados en este cementerio.</td></tr>";
}
?>
                </tbody>
            </table>

            <!-- Botón Volver -->
            <div style="margin-top: 30px; text-align: center;">
                <button class="boton-gestion" style="width: 250px; background-color: #555;" onclick="location.href='../gestionar_cementerios.php'">Volver al listado cementerios</button>
            </div>

        </div>
    </div>
</div>

<?php
mysqli_close($conexion);
?>

</body>
</html>