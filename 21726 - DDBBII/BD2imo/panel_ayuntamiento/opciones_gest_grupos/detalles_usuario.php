<?php
// Iniciar sesión
session_start();

$id_grupo_seleccionado = $_GET['grupo'] ?? '';
$id_usuario_actual = "";
$datos_usuario = [
    'id_usuario' => '',
    'nombre_usuario' => '',
    'nombre' => '',
    'apellidos' => '',
    'telefono' => '',
    'email' => ''
];
$mensaje_error = "";
$usuario_encontrado = false;

$usuario_logueado = $_SESSION["usuario"] ?? "";

// Lógica para obtener los detalles del usuario

// 1. Verificar si se ha pasado un ID
if (isset($_GET["id"])) {
    $id_usuario_actual = $_GET["id"];
    
    // 2. Conexión a la BBDD
    $conexion = mysqli_connect("localhost", "root", ""); 
    $db = mysqli_select_db($conexion, "BD2XAMPPions"); 
    
    // 3. Crear la sentencia SELECT
    // Seleccionamos todos los campos excepto la contraseña
    $consulta_select = "SELECT id_usuario, nombre_usuario, nombre, apellidos, telefono, email 
                        FROM usuario 
                        WHERE id_usuario = '$id_usuario_actual'";

    // 4. Ejecutar la consulta
    $resultado = mysqli_query($conexion, $consulta_select);

    // 5. Verificar y obtener los resultados
    if ($registro = mysqli_fetch_assoc($resultado)) {
        $datos_usuario = $registro;
        $usuario_encontrado = true;
    } else {
        $mensaje_error = "Error: Usuario con ID '$id_usuario_actual' no encontrado.";
    }

    // 6. Cerrar la conexión
    mysqli_close($conexion);
    
} else {
    $mensaje_error = "Error: No se ha especificado el ID del usuario.";
}

// Los datos del usuario para mostrar en el formulario
$id = htmlspecialchars($datos_usuario['id_usuario']);
$username = htmlspecialchars($datos_usuario['nombre_usuario']);
$nombre = htmlspecialchars($datos_usuario['nombre']);
$apellidos = htmlspecialchars($datos_usuario['apellidos']);
$telefono = htmlspecialchars($datos_usuario['telefono']);
$email = htmlspecialchars($datos_usuario['email']);

$titulo_pagina = $usuario_encontrado ? "Detalles de: " . $nombre . " " . $apellidos : "Detalles del Usuario";
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
    <?php include("../panel_opciones.php"); ?>

    <!-- Contenido principal -->
    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard"><?php echo $titulo_pagina; ?></h2>
            
            <!-- Mensajes de Error -->
            <?php if (!empty($mensaje_error)) : ?>
                <p class="mensaje-alerta mensaje-error" style="text-align: center; margin-bottom: 20px;">
                    <?php echo $mensaje_error; ?>
                </p>
            <?php endif; ?>

            <!-- Formulario (solo para visualización) -->
            <?php if ($usuario_encontrado) : ?>
            
                <div class="formulario-colonia">
                    
                    <!-- ID USUARIO -->
                    <label for="id_usuario">ID de Usuario:</label>
                    <input type="text" id="id_usuario" name="id_usuario" value="<?php echo $id; ?>" 
                           maxlength="10" required readonly>
                    
                    <!-- NOMBRE DE USUARIO (CUENTA) -->
                    <label for="nombre_usuario">Nombre de usuario (Login):</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" value="<?php echo $username; ?>" 
                           maxlength="50" required readonly>
                    
                    <!-- NOMBRE -->
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>" 
                           maxlength="100" required readonly>
                    
                    <!-- APELLIDOS -->
                    <label for="apellidos">Apellidos:</label>
                    <input type="text" id="apellidos" name="apellidos" value="<?php echo $apellidos; ?>"
                           maxlength="150" readonly>
                    
                    <!-- TELÉFONO -->
                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" value="<?php echo $telefono; ?>"
                           maxlength="15" readonly>
                           
                    <!-- EMAIL -->
                    <label for="email">Correo Electrónico:</label>
                    <input type="text" id="email" name="email" value="<?php echo $email; ?>"
                           maxlength="100" readonly>
                    
                    <!-- Botón Volver al borsín -->
                    <button type="button" 
                            class="boton-gestion boton-confirmar" 
                            onclick="location.href='ver_todos_miembros_grupo.php?id=<?php echo urlencode($id_grupo_seleccionado); ?>'">
                        Volver a la lista de miembros del grupo
                    </button>
                </div>
            
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>