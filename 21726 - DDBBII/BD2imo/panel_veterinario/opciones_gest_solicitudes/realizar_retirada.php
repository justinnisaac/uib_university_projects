<?php
session_start();

// Seguridad: Verificar sesión de veterinario
if (!isset($_SESSION["usuario"]) || !isset($_SESSION["id_municipio_vet"])) {
    header("Location: ../../login_registro/login.php");
    exit();
}

$id_municipio_vet = $_SESSION["id_municipio_vet"];
$nombre_municipio = $_SESSION["nombre_municipio_vet"] ?? "tu municipio";
$id_solicitud = $_GET['id'] ?? '';

// Variables para la interfaz
$nombre_gato = "";
$id_gato = "";
$id_responsable = ""; // Necesario para la tabla retirada
$error = "";

// 1. Conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// 2. Obtener datos de la solicitud y del gato
if (!empty($id_solicitud)) {
    $consulta_datos = "
        SELECT s.id_solicitud, s.id_gato, s.id_responsable, g.nombre AS nombre_gato
        FROM solicitud_retirada s
        JOIN gato g ON s.id_gato = g.id_gato
        WHERE s.id_solicitud = '$id_solicitud'
    ";
    $res_datos = mysqli_query($conexion, $consulta_datos);
    
    if ($fila = mysqli_fetch_array($res_datos)) {
        $nombre_gato = $fila['nombre_gato'];
        $id_gato = $fila['id_gato'];
        $id_responsable = $fila['id_responsable'];
    } else {
        $error = "Solicitud no encontrada.";
    }
} else {
    $error = "ID de solicitud no especificado.";
}

// 3. Obtener cementerios del municipio del veterinario
$cementerios = [];
$consulta_cementerios = "SELECT id_cementerio, nombre FROM cementerio WHERE id_municipio = '$id_municipio_vet'";
$res_cementerios = mysqli_query($conexion, $consulta_cementerios);

while ($row = mysqli_fetch_array($res_cementerios)) {
    $cementerios[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Realizar Retirada</title>
    <link rel="stylesheet" href="../../estilo/estilo_panel.css"> 
    <link rel="stylesheet" href="../../estilo/estilo_contenido.css">
    <style>
        .select-estilo {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 12px;
            border: 1px solid #ccc;
            font-size: 17px;
            background-color: white;
            color: #333;
        }
    </style>
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../../../BD2npb/panel_veterinario/panel_opciones_veterinario.php"); ?>

    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <?php if ($error): ?>
                <h2 class="titulo-dashboard">Error</h2>
                <p class="mensaje-alerta mensaje-error"><?php echo $error; ?></p>
                <div style="text-align:center; margin-top:20px;">
                    <button class="boton-gestion" onclick="location.href='../gestionar_solicitudes_retirada.php'">Volver</button>
                </div>
            <?php else: ?>

                <!-- Título -->
                <h2 class="titulo-dashboard" style="font-size: 28px;">
                    Proceder con la retirada de <?php echo htmlspecialchars($nombre_gato); ?>, gato #<?php echo htmlspecialchars($id_gato); ?>
                </h2>

                <div class="formulario-colonia">
                    <form action="procesar_retirada.php" method="POST">
                        
                        <!-- Campos Ocultos necesarios para procesar -->
                        <input type="hidden" name="id_solicitud" value="<?php echo htmlspecialchars($id_solicitud); ?>">
                        <input type="hidden" name="id_gato" value="<?php echo htmlspecialchars($id_gato); ?>">
                        <input type="hidden" name="id_responsable" value="<?php echo htmlspecialchars($id_responsable); ?>">

                        <!-- COMENTARIOS AUTOPSIA -->
                        <label for="comentarios_autopsia">Comentarios sobre la autopsia:</label>
                        <input type="text" id="comentarios_autopsia" name="comentarios_autopsia" 
                               placeholder="Escribe aquí los detalles de la autopsia..." required>

                        <!-- SELECTOR CEMENTERIO -->
                        <label for="id_cementerio">Selecciona un cementerio donde destinar los restos:</label>
                        <select name="id_cementerio" id="id_cementerio" class="select-estilo" required>
                            <option value="">Selecciona un cementerio de <?php echo htmlspecialchars($nombre_municipio); ?></option>
                            <?php foreach ($cementerios as $c): ?>
                                <option value="<?php echo htmlspecialchars($c['id_cementerio']); ?>">
                                    <?php echo htmlspecialchars($c['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Botones -->
                        <button type="submit" class="boton-gestion boton-confirmar">Confirmar retirada</button>
                        
                        <button type="button" 
                                class="boton-gestion" 
                                style="background-color: #555; margin-top: 15px;" 
                                onclick="location.href='../gestionar_solicitudes_retirada.php'">
                            Cancelar
                        </button>

                    </form>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php
mysqli_close($conexion);
?>

</body>
</html>