<?php

$id_colonia_actual = "";
$nombre_colonia = "Colonia Desconocida";
$mensaje = "";
$gatos_encontrados = null; // Variable para almacenar el resultado de la consulta

$usuario = "";
if (isset($_POST["usuario"])) {
    $usuario = $_POST["usuario"];
}


// Lógica para obtener los gatos de una colonia específica

// 1. Verificar si se ha pasado un ID
if (isset($_GET["id"])) {
    $id_colonia_actual = $_GET["id"];
    
    // 2. Conexión a la BBDD
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 
    
    // 3. Obtener el nombre de la colonia
    $consulta_nombre = "SELECT nombre_colonia FROM colonia WHERE id_colonia = '$id_colonia_actual'";
    $res_nombre = mysqli_query($conexion, $consulta_nombre);
    if ($reg = mysqli_fetch_array($res_nombre)) {
        $nombre_colonia = $reg['nombre_colonia'];
    }

    // 4. Consulta principal: Obtener ID y Nombre de los gatos actualmente en esta colonia
    // Nota: que solo salgan aquellos gatos cuyo historial_colonia apunte a la colonia actual Y fecha_salida sea NULL.
    // Es una forma de decir "el gato sigue en la colonia".
    $consulta_gatos = "
        SELECT g.id_gato, g.nombre
        FROM gato g
        JOIN historial_colonia hc ON g.id_gato = hc.id_gato
        WHERE hc.id_colonia = '$id_colonia_actual' 
          AND hc.fecha_salida IS NULL"; 

    // 5. Ejecutar la consulta
    $gatos_encontrados = mysqli_query($conexion, $consulta_gatos);
    
    if (!$gatos_encontrados) {
        $mensaje = "Error al consultar los gatos: " . mysqli_error($conexion);
    }

    // 6. Cerrar la conexión
    mysqli_close($conexion);
    
} else {
    $mensaje = "Error: No se ha especificado el ID de la colonia.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gatos en <?php echo htmlspecialchars($nombre_colonia); ?></title>

    <link rel="stylesheet" href="../../estilo/estilo_panel.css"> 
    <link rel="stylesheet" href="../../estilo/estilo_contenido.css">
    
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../panel_opciones.php"); ?>

    <!-- Contenido principal -->
    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Gatos en <?php echo htmlspecialchars($nombre_colonia); ?> (#<?php echo htmlspecialchars($id_colonia_actual); ?>)</h2>

            <?php if (!empty($mensaje)) : ?>
                <!-- Mostrar mensaje de error -->
                <p class="mensaje-alerta mensaje-error" style="text-align: center; margin-bottom: 20px;">
                    <?php echo $mensaje; ?>
                </p>
            <?php endif; ?>
            
            <!-- Tabla de gatos -->
            <?php if ($gatos_encontrados && mysqli_num_rows($gatos_encontrados) > 0) : ?>
                
                <table class="tabla-colonias">
                    <thead>
                        <tr>
                            <th>ID Gato</th>
                            <th>Nombre del Gato</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
    <?php
    // Bucle para generar una fila por cada gato encontrado
    while($registro = mysqli_fetch_array($gatos_encontrados)) {
        $id_gato = $registro["id_gato"];
        $nombre_gato = $registro["nombre"];
    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($id_gato); ?></td>
                            <td><?php echo htmlspecialchars($nombre_gato); ?></td>
                            <td>
                                <div class="acciones-container">
                                    <button class="boton-mini" 
                                            onclick="location.href='detalles_gato.php?id=<?php echo htmlspecialchars($id_gato); ?>'">
                                        Detalles
                                    </button>
                                </div>
                            </td>
                        </tr>
    <?php
    }
    ?>
                    </tbody>
                </table>
            
            <?php elseif ($id_colonia_actual && empty($mensaje)) : ?>
                <!-- Mensaje si la consulta fue exitosa pero no hay gatos -->
                <p class="mensaje-alerta mensaje-exito" style="text-align: center; margin-top: 30px;">
                    La colonia '<?php echo htmlspecialchars($nombre_colonia); ?>' no tiene gatos actualmente.
                </p>
            <?php endif; ?>
            
            <!-- Botón Volver al listado de colonias -->
            <div style="max-width: 500px; margin: 30px auto 0;">
                <button type="button" 
                        class="boton-gestion boton-confirmar" 
                        onclick="location.href='../gestionar_colonias.php'">
                    Volver al listado de colonias
                </button>
            </div>

        </div>
    </div>
</div>
</body>
</html>