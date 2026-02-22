<?php
// Iniciar sesión
session_start();

// Seguridad: Verificar sesión del ayuntamiento
if (!isset($_SESSION["id_ayuntamiento"])) {
    header("Location: ../login_registro/login.php"); 
    exit();
}

$id_intervencion = $_GET['id_intervencion'] ?? '';
$id_campana = $_GET['id_campana'] ?? ''; // Necesario para el botón "Volver", mejora navegabilidad

// Variables
$veterinarios = [];
$mensaje_error = "";

// 1. Conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

if (!empty($id_intervencion)) {
    
    // Obtener todos los datos del usuario/veterinario que participó en esta intervención
    $q_vets = "
        SELECT v.id_veterinario, u.nombre, u.apellidos, u.telefono, u.email, especialidad
        FROM veterinario_accion va
        JOIN veterinario v ON va.id_veterinario = v.id_veterinario
        JOIN usuario u ON v.id_veterinario = u.id_usuario
        WHERE va.id_intervencion = '$id_intervencion'
    ";
    
    $resultado = mysqli_query($conexion, $q_vets);
    if ($resultado) {
        while ($row = mysqli_fetch_array($resultado)) {
            $veterinarios[] = $row;
        }
    } else {
        $mensaje_error = "Error al consultar la BBDD.";
    }

} else {
    $mensaje_error = "ID de intervención no especificado.";
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Veterinarios Intervención <?php echo htmlspecialchars($id_intervencion); ?></title>

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

            <h2 class="titulo-dashboard">Veterinarios implicados en la intervención #<?php echo htmlspecialchars($id_intervencion); ?></h2>
            
            <?php if (!empty($mensaje_error)): ?>
                <p class="mensaje-alerta mensaje-error"><?php echo $mensaje_error; ?></p>
            <?php endif; ?>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Especialidad</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($veterinarios) > 0): ?>
                        <?php foreach ($veterinarios as $vet): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($vet['id_veterinario']); ?></td>
                            <td><?php echo htmlspecialchars($vet['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($vet['apellidos']); ?></td>
                            <td><?php echo htmlspecialchars($vet['telefono']); ?></td>
                            <td><?php echo htmlspecialchars($vet['email']); ?></td>
                            <td><?php echo htmlspecialchars($vet['especialidad']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding: 20px;">No se encontraron veterinarios asociados a esta intervención.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            
            <div style="margin-top: 40px; text-align: center;">
                <button class="boton-gestion boton-confirmar" 
                        onclick="location.href='detalles_campana.php?id=<?php echo urlencode($id_campana); ?>'">
                    Volver a los detalles de la campaña
                </button>
            </div>
        </div>
    </div>
</div>

</body>
</html>