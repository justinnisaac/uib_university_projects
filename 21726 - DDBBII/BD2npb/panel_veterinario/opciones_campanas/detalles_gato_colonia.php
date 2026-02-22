
<?php
// Iniciar sesión
session_start();

$id_gato_actual = "";
$id_colonia_actual = "";
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

$usuario = $_SESSION["usuario"] ?? "";

// Lógica para obtener los detalles del gato
// 1. Verificar si se ha pasado un ID de gato
if (isset($_GET["id"])) {
    $id_gato_actual = $_GET["id"];
    
    // 2. Conexión a la BBDD
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 
    
    // 3. Crear la sentencia SELECT para obtener los datos del gato
    $consulta_select = "
        SELECT 
            g.id_gato,
            g.nombre,
            g.num_chip,
            g.url_foto,
            g.descripcion_aspecto,
            eg.estado AS estado
        FROM gato g
        JOIN estado_gato eg ON g.id_estado = eg.id_estado
        AND g.id_gato = '$id_gato_actual'
    ";

    // 4. Ejecutar la consulta del gato
    $resultado = mysqli_query($conexion, $consulta_select);

    // 5. Verificar y obtener los resultados
    if ($registro = mysqli_fetch_assoc($resultado)) {
        $datos_gato = $registro;
        $gato_encontrado = true;
        
        // 6. Consultar la ID de la colonia actual del gato (para el botón Volver)
        // Buscamos la colonia donde la fecha_salida es NULL
        $consulta_colonia = "
            SELECT id_colonia 
            FROM historial_colonia 
            WHERE id_gato = '$id_gato_actual' AND fecha_salida IS NULL";
                             
        $res_colonia = mysqli_query($conexion, $consulta_colonia);
        if ($reg_colonia = mysqli_fetch_array($res_colonia)) {
            $id_colonia_actual = $reg_colonia['id_colonia'];
        } else {
            // El gato no tiene colonia activa (puede ser difunto o no asignado)
            $id_colonia_actual = ''; 
        }
    } else {
        $mensaje_error = "Error: Gato con ID '$id_gato_actual' no encontrado.";
    }

    // 7. Cerrar la conexión
    mysqli_close($conexion);
    
} else {
    $mensaje_error = "Error: No se ha especificado el ID del gato a visualizar.";
}

// Escapado de datos para HTML
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

    
    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_contenido.css">
    
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../panel_opciones_veterinario.php"); ?>

    <!-- Contenido principal -->
    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Detalles del gato: <?php echo $nombre; ?> #<?php echo $id; ?></h2>
            
            <!-- Mensajes de Error/Advertencia -->
            <?php if (!empty($mensaje_error)) : ?>
                <p class="mensaje-alerta mensaje-error" style="text-align: center; margin-bottom: 20px;">
                    <?php echo $mensaje_error; ?>
                </p>
            <?php endif; ?>

            <!-- Formulario (sólo permite visualizar) -->
            <?php if ($gato_encontrado) : ?>
            
                <div class="formulario-colonia">
                    
                    <!-- ID DEL GATO -->
                    <label for="id_gato">Código identificativo del gato:</label>
                    <input type="text" id="id_gato" name="id_gato" value="<?php echo $id; ?>" 
                           maxlength="10" required readonly>
                    
                    <!-- NOMBRE -->
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>" 
                           maxlength="100" required readonly>
                    
                    <!-- NÚMERO DE CHIP -->
                    <label for="num_chip">Número de Chip:</label>
                    <input type="text" id="num_chip" name="num_chip" value="<?php echo $chip; ?>"
                           maxlength="20" readonly>
                    
                    <!-- URL FOTO -->
                    <label for="url_foto">URL de la Foto:</label>
                    <input type="text" id="url_foto" name="url_foto" value="<?php echo $foto; ?>"
                           maxlength="255" readonly>
                           
                    <!-- DESCRIPCIÓN ASPECTO -->
                    <label for="descripcion_aspecto">Descripción de Aspecto:</label>
                    <input type="text" id="descripcion_aspecto" name="descripcion_aspecto" value="<?php echo $descripcion; ?>"
                           maxlength="255" readonly>

                    <!-- ESTADO -->
                    <label for="estado">Estado de salud:</label>
                    <input type="text" id="estado" name="estado" value="<?php echo $estado; ?>"
                           maxlength="50" readonly 
                           style="font-weight: bold; <?php echo ($estado == 'Difunto' || $estado == 'Enfermo' || $estado == 'Herido') ? 'color: #D32F2F;' : 'color: #388E3C;'; ?>">
                    
                    <!-- Botón Volver al listado de gatos -->
                    <?php if ($id_colonia_actual): ?>
                        <button type="button" 
                                class="boton-gestion boton-confirmar" 
                                onclick="location.href='ver_gatos_colonia.php?id=<?php echo $id_colonia_actual; ?>'">
                            Volver al listado de gatos
                        </button>
                    <?php else: ?>
                        <!-- Si el gato no tiene colonia volvemos a la gestión de campañas -->
                        <button type="button" 
                                class="boton-gestion boton-confirmar" 
                                onclick="location.href='../vet_campanas.php'">
                            Volver al listado de campañas
                        </button>
                    <?php endif; ?>

                </div>
            
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>