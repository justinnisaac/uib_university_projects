<?php
session_start();

// Seguridad: Verificar sesión de veterinario
if (!isset($_SESSION["usuario"]) || !isset($_SESSION["id_usuario"])) {
    header("Location: ../login_registro/login.php");
    exit();
}

$usuario_login = $_SESSION["usuario"]; // Nombre de usuario (ej: vetPetra1)
$id_veterinario = $_SESSION["id_usuario"]; // ID numérico

// Conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// Obtener el nombre real de la persona (lo mostraremos en el título)
$nombre_persona = $usuario_login; // Valor por defecto (fallback)
$consulta_nombre = "SELECT nombre, apellidos FROM usuario WHERE id_usuario = '$id_veterinario'";
$res_nombre = mysqli_query($conexion, $consulta_nombre);

if ($fila_nombre = mysqli_fetch_array($res_nombre)) {
    $nombre_persona = $fila_nombre['nombre'] . " " . $fila_nombre['apellidos'];
}

// Consulta de historial de autopsias
$consulta = "
    SELECT r.id_retirada, 
           sr.id_gato, 
           r.comentarios_autopsia, 
           c.nombre AS nombre_cementerio,
           (
               SELECT h.id_colonia 
               FROM historial_colonia h 
               WHERE h.id_gato = sr.id_gato 
               ORDER BY h.fecha_salida DESC 
               LIMIT 1
           ) AS ultima_colonia
    FROM retirada r
    JOIN solicitud_retirada sr ON r.id_solicitud = sr.id_solicitud
    JOIN cementerio c ON r.id_cementerio = c.id_cementerio
    WHERE r.id_veterinario = '$id_veterinario'
";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Historial de Autopsias</title>
    
    <link rel="stylesheet" href="../estilo/estilo_panel.css">
    <link rel="stylesheet" href="../estilo/estilo_contenido.css">
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../../BD2npb/panel_veterinario/panel_opciones_veterinario.php"); ?>

    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <!-- Título personalizado con el nombre de la persona -->
            <h2 class="titulo-dashboard" style="font-size: 28px;">
                Historial de autopsias realizadas por <?php echo htmlspecialchars($nombre_persona); ?>, veterinario #<?php echo htmlspecialchars($id_veterinario); ?>
            </h2>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID Autopsia</th>
                        <th>ID Gato</th>
                        <th>Última Colonia</th>
                        <th>Comentarios Autopsia</th>
                        <th>Cementerio Destino</th>
                    </tr>
                </thead>

                <tbody>
<?php
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while($row = mysqli_fetch_array($resultado)) {
        $id_retirada = $row['id_retirada'];
        $id_gato = $row['id_gato'];
        $ultima_colonia = $row['ultima_colonia'] ?? 'Desconocida';
        $comentarios = $row['comentarios_autopsia'];
        $cementerio = $row['nombre_cementerio'];
?>
                    <tr>
                        <td><?php echo htmlspecialchars($id_retirada); ?></td>
                        <td><?php echo htmlspecialchars($id_gato); ?></td>
                        <td><?php echo htmlspecialchars($ultima_colonia); ?></td>
                        <td><?php echo htmlspecialchars($comentarios); ?></td>
                        <td><?php echo htmlspecialchars($cementerio); ?></td>
                    </tr>
<?php
    }
} else {
    echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>Aún no has realizado ninguna autopsia o retirada.</td></tr>";
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