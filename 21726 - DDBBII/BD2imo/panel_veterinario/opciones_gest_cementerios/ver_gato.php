<?php
session_start();

// Seguridad: Verificar sesión de veterinario
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../login_registro/login.php");
    exit();
}

// Recibimos el ID del gato y el ID del cementerio (para saber volver)
$id_gato_actual = $_GET["id"] ?? '';
$id_cementerio = $_GET['id_cementerio'] ?? '';

$datos_gato = [
    'id_gato' => '',
    'nombre' => '',
    'num_chip' => '',
    'url_foto' => '',
    'descripcion_aspecto' => '',
    'estado' => ''
];
$mensaje_error = "";
$gato_encontrado = false;

// 1. Conexión y Consulta
if (!empty($id_gato_actual)) {
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 
    
    $consulta_select = "SELECT g.id_gato, g.nombre, g.num_chip, g.url_foto, g.descripcion_aspecto, eg.estado
                        FROM gato AS g
                        JOIN estado_gato AS eg ON eg.id_estado = g.id_estado AND g.id_gato = '$id_gato_actual'";

    $resultado = mysqli_query($conexion, $consulta_select);

    if ($registro = mysqli_fetch_assoc($resultado)) {
        $datos_gato = $registro;
        $gato_encontrado = true;
    } else {
        $mensaje_error = "Error: Gato con ID '$id_gato_actual' no encontrado.";
    }
    mysqli_close($conexion);
} else {
    $mensaje_error = "Error: No se ha especificado el ID del gato.";
}

// Datos para mostrar en el formulario
$id = htmlspecialchars($datos_gato['id_gato']);
$nombre = htmlspecialchars($datos_gato['nombre']);
$chip = htmlspecialchars($datos_gato['num_chip'] ?? ''); 
$foto = htmlspecialchars($datos_gato['url_foto'] ?? '');
$descripcion = htmlspecialchars($datos_gato['descripcion_aspecto'] ?? '');
$estado = htmlspecialchars($datos_gato['estado']);

$titulo_pagina = $gato_encontrado ? "Detalles de: " . $nombre : "Detalles del Gato";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo_pagina; ?></title>
    <link rel="stylesheet" href="../../estilo/estilo_panel.css"> 
    <link rel="stylesheet" href="../../estilo/estilo_contenido.css">
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../../../BD2npb/panel_veterinario/panel_opciones_veterinario.php"); ?>

    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Detalles del Gato: <?php echo $nombre; ?> (<?php echo $id; ?>)</h2>
            
            <?php if (!empty($mensaje_error)) : ?>
                <p class="mensaje-alerta mensaje-error" style="text-align: center; margin-bottom: 20px;">
                    <?php echo $mensaje_error; ?>
                </p>
            <?php endif; ?>

            <?php if ($gato_encontrado) : ?>
            
                <div class="formulario-colonia">
                    
                    <label for="id_gato">Código identificativo del gato:</label>
                    <input type="text" id="id_gato" name="id_gato" value="<?php echo $id; ?>" 
                           maxlength="10" required readonly>
                    
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>" 
                           maxlength="100" required readonly>
                    
                    <label for="num_chip">Número de Chip:</label>
                    <input type="text" id="num_chip" name="num_chip" value="<?php echo $chip; ?>"
                           maxlength="20" readonly>
                    
                    <label for="url_foto">URL de la Foto:</label>
                    <input type="text" id="url_foto" name="url_foto" value="<?php echo $foto; ?>"
                           maxlength="255" readonly>
                           
                    <label for="descripcion_aspecto">Descripción de Aspecto:</label>
                    <input type="text" id="descripcion_aspecto" name="descripcion_aspecto" value="<?php echo $descripcion; ?>"
                           maxlength="255" readonly>

                    <label for="estado">Estado de salud:</label>
                    <input type="text" id="estado" name="estado" value="<?php echo $estado; ?>"
                           maxlength="50" readonly 
                           style="font-weight: bold; <?php echo ($estado == 'Difunto' || $estado == 'Enfermo' || $estado == 'Herido') ? 'color: #D32F2F;' : 'color: #388E3C;'; ?>">
                    
                    <!-- Botón para volver a la lista de gatos descansando en el cementerio que estabas consultando -->
                    <button type="button" 
                            class="boton-gestion boton-confirmar" 
                            onclick="location.href='lista_gatos_descansando.php?id=<?php echo htmlspecialchars($id_cementerio); ?>'">
                        Volver a la lista de gatos descansando
                    </button>

                </div>
            
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>