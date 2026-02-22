<?php

session_start();

$id_campana = $_GET['id'] ?? null;
$fechaFin = date("Y-m-d");
$nombre="";
$mensaje = "";
$exito = false;
$accion_ejecutada = false;

// Proceso de finalización
if (isset($_POST['confirmar_eliminar'])) {

    // Conectar a MySQL
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions");

    $id_usuario = $_SESSION["id_usuario"]; 

    // Obtener nombre de la campana
    $consulta = "
        SELECT c.nombre
        FROM campana c
        WHERE c.id_campana = '$id_campana'";

    $resultado = mysqli_query($conexion, $consulta);

    $resultado = mysqli_fetch_array($resultado);

    $nombre = $resultado['nombre'];

    //Ejecutamos el cambio en la bbdd
    $accion_ejecutada = true;
    
    // 2. Crear la sentencia UPDATE
    $consulta_update = "
    UPDATE campana
    SET fechaFin = '$fechaFin'
    WHERE id_campana = '$id_campana'";

    // 3. Ejecución
    if (mysqli_query($conexion, $consulta_update)) {
        $mensaje = "Campaña '$nombre' finalizada.";
        $exito = true;
    } else {
        $mensaje = "Error al finalizar la campaña: " . mysqli_error($conexion);
        $exito = false;
    }
    
    // 4. Cerrar la conexión
    mysqli_close($conexion);
}


// Proceso de carga inicial de datos (SELECT) o post-eliminación
if (!$accion_ejecutada && isset($_GET['id'])) {
    
    $id_voluntario = (int) $_GET['id'];
    
    // 1. Conexión
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 

    // 2. Crear la sentencia SELECT para obtener el nombre
    // Obtener nombre de la campana
    $consulta_select = "
        SELECT c.nombre
        FROM campana c
        WHERE c.id_campana = '$id_campana'";

    // 3. Ejecución y obtención del nombre
    $resultado = mysqli_query($conexion, $consulta_select);
    
    if ($registro = mysqli_fetch_array($resultado)) {
        $nombre = $registro['nombre'];
    } else {
        $mensaje = "Error: Campaña con ID '$id_campana' no encontrada o ya finalizada";
        $exito = false;
        $accion_ejecutada = true; // No mostrar el formulario si no se encuentra
    }

    // 4. Cerrar la conexión
    mysqli_close($conexion);
}

$usuario = "";
if (isset($_POST["usuario"])) {
    $usuario = $_POST["usuario"];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Finalizar campaña - Confirmacion</title>

    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
    
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral-->
    <?php include("../../BD2imo/panel_ayuntamiento/panel_opciones.php"); ?>

    <!-- Contenido -->
    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Finalizar campaña: <?php echo htmlspecialchars($id_campana); ?></h2>

            <?php if ($mensaje): 
                // Mensaje de éxito o error
                $clase_alerta = $exito ? 'mensaje-exito' : 'mensaje-error';
            ?>
                <p class="mensaje-alerta <?php echo $clase_alerta; ?>" style="text-align: center; margin-bottom: 20px;">
                    <?php echo $mensaje; ?>
                </p>
                <div style="max-width: 500px; margin: 0 auto;">
                    <button type="button" 
                            class="boton-gestion boton-confirmar" 
                            onclick="location.href='../../BD2imo/panel_ayuntamiento/gestionar_campanas.php'">
                        Volver al listado de campañas
                    </button>
                </div>

            <?php elseif ($id_voluntario): ?>

                <!-- Interfaz de confirmación -->
                
                <div style="max-width: 500px; margin: 0 auto;">
                    
                    <p style="font-size: 18px; text-align: center; margin-bottom: 30px; font-weight: bold;">
                        Se va a finalizar la campaña <span style="color: #F44336;"><?php echo htmlspecialchars($id_campana)?>: <?php echo htmlspecialchars($nombre); ?></span>. Esta
                        decisión es permanente. ¿Estás seguro?
                    </p>

                    <!-- Formulario para confirmar eliminación -->
                    <form action="confirmacion_finalizar_campana.php?id=<?php echo $id_campana ?>" method="POST" style="display: flex; justify-content: space-between; gap: 20px;">
                        
                        <!-- ID: Necesario para saber qué eliminar -->
                        <input type="hidden" name="id_voluntario" value="<?php echo htmlspecialchars($id_campana); ?>">
                        <input type="hidden" name="nombre_voluntario" value="<?php echo htmlspecialchars($nombre); ?>">
                        
                        <!-- Botón finalizar -->
                        <button type="submit" 
                                name="confirmar_eliminar" 
                                class="boton-gestion" 
                                style="background-color: #2c8a01ff; flex-grow: 1;">
                            Finalizar
                        </button>

                    </form>

                    <!-- Botón retroceder -->
                    <button type="button" 
                            class="boton-gestion boton-confirmar" 
                            onclick="location.href='../../BD2imo/panel_ayuntamiento/gestionar_campanas.php'"
                            style="margin-top: 15px; background-color: #555;">
                        Retroceder
                    </button>

                </div>

            <?php else: ?>
                <p class="mensaje-alerta mensaje-error" style="text-align: center; margin-bottom: 20px;">
                    No se pudo cargar la información para eliminar la campaña.
                </p>
                <div style="max-width: 500px; margin: 0 auto;">
                    <button type="button" 
                            class="boton-gestion boton-confirmar" 
                            onclick="location.href='../../BD2imo/panel_ayuntamiento/gestionar_campanas.php'">
                        Volver al listado de campañas
                    </button>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>