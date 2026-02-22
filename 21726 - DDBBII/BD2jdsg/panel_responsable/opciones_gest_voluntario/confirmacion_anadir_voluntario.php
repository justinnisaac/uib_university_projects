<?php

session_start();

$id_voluntario = "";
$nombre_voluntario = "";
$mensaje = "";
$exito = false;
$accion_ejecutada = false;

// Proceso de eliminación tras confirmación
if (isset($_POST['confirmar_eliminar'])) {

    // Conectar a MySQL
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions");

    $id_usuario = $_SESSION["id_usuario"]; 

    // Obtener información del grupo de trabajo al que pertenece el responsable
    $consulta = "
        SELECT
        g.id_grupo,
        COUNT(v.id_voluntario) AS cantidad_miembros,
        g.nombre AS nombre_grupo,
        a.nombre AS nombre_ayuntamiento
    FROM grupo_control_felino g
    JOIN voluntario v ON v.id_grupo = g.id_grupo
    JOIN ayuntamiento a ON g.id_ayuntamiento = a.id_ayuntamiento
    WHERE g.id_grupo = (
        SELECT id_grupo FROM voluntario WHERE id_voluntario = '$id_usuario'
    )
    GROUP BY g.id_grupo, g.nombre, a.nombre
    ";

    $resultado = mysqli_query($conexion, $consulta);

    $resultado = mysqli_fetch_array($resultado);

    $id_grupo_seleccionado = $resultado['id_grupo'];

    //Ejecutamos el cambio en la bbdd
    $accion_ejecutada = true;
    $id_voluntario = $_POST['id_voluntario'];
    $nombre_voluntario = $_POST['nombre_voluntario']; // Se pasa el nombre para el mensaje de éxito

    // 1. Conexión a la BBDD
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 
    
    // 2. Crear la sentencia UPDATE
    $consulta_update = "
    UPDATE voluntario
    SET id_grupo = '$id_grupo_seleccionado'
    WHERE id_voluntario = '$id_voluntario'";

    // 3. Ejecución
    if (mysqli_query($conexion, $consulta_update)) {
        $mensaje = "Voluntario '$nombre_voluntario' añadido al grupo de trabajo.";
        $exito = true;
    } else {
        $mensaje = "Error al añadir al voluntario: " . mysqli_error($conexion);
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
    $consulta_select = "
        SELECT u.nombre, u.apellidos
        FROM voluntario v
        JOIN usuario u ON v.id_voluntario = u.id_usuario
        WHERE v.id_voluntario = '$id_voluntario'
    ";

    // 3. Ejecución y obtención del nombre
    $resultado = mysqli_query($conexion, $consulta_select);
    
    if ($registro = mysqli_fetch_array($resultado)) {
        $nombre_voluntario = $registro['nombre'] . " " . $registro['apellidos'];
    } else {
        $mensaje = "Error: Voluntario con ID '$id_voluntario' no encontrado o ya añadido";
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
    <title>Añadir Voluntario - Confirmacion</title>

    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_contenido.css">
    
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral-->
    <?php include("../panel_opciones_responsable.php"); ?>

    <!-- Contenido -->
    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Añadir Voluntario: <?php echo htmlspecialchars($id_voluntario); ?></h2>

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
                            onclick="location.href='anadir_voluntario.php'">
                        Volver al listado de voluntarios disponibles
                    </button>
                </div>

            <?php elseif ($id_voluntario): ?>

                <!-- Interfaz de confirmación -->
                
                <div style="max-width: 500px; margin: 0 auto;">
                    
                    <p style="font-size: 18px; text-align: center; margin-bottom: 30px; font-weight: bold;">
                        Se va a añadir al voluntario <span style="color: #F44336;">[<?php echo htmlspecialchars($nombre_voluntario); ?>]</span>. Esta
                        decisión es permanente. ¿Estás seguro?
                    </p>

                    <!-- Formulario para confirmar eliminación -->
                    <form action="confirmacion_anadir_voluntario.php" method="POST" style="display: flex; justify-content: space-between; gap: 20px;">
                        
                        <!-- ID: Necesario para saber qué eliminar -->
                        <input type="hidden" name="id_voluntario" value="<?php echo htmlspecialchars($id_voluntario); ?>">
                        <input type="hidden" name="nombre_voluntario" value="<?php echo htmlspecialchars($nombre_voluntario); ?>">
                        
                        <!-- Botón añadir -->
                        <button type="submit" 
                                name="confirmar_eliminar" 
                                class="boton-gestion" 
                                style="background-color: #2c8a01ff; flex-grow: 1;">
                            Añadir
                        </button>

                    </form>

                    <!-- Botón retroceder -->
                    <button type="button" 
                            class="boton-gestion boton-confirmar" 
                            onclick="location.href='anadir_voluntario.php'"
                            style="margin-top: 15px; background-color: #555;">
                        Retroceder
                    </button>

                </div>

            <?php else: ?>
                <p class="mensaje-alerta mensaje-error" style="text-align: center; margin-bottom: 20px;">
                    No se pudo cargar la información para añadir al voluntario.
                </p>
                <div style="max-width: 500px; margin: 0 auto;">
                    <button type="button" 
                            class="boton-gestion boton-confirmar" 
                            onclick="location.href='anadir_voluntario.php'">
                        Volver al listado de voluntarios
                    </button>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>