<?php
// Viniendo de registro.php, puede ser que haya un mensaje para mostrar,
// lo recogemos aquí.
$msg = $_GET['msg'] ?? '';
$texto_mensaje = '';
$clase_mensaje = '';

if ($msg == 'exito') {
    $texto_mensaje = "Cuenta creada exitósamente. Puedes iniciar sesión.";
    $clase_mensaje = "mensaje-exito";
} elseif ($msg == 'error_existe') {
    $texto_mensaje = "Cuenta ya existente. Intentar con otros datos.";
    $clase_mensaje = "mensaje-error";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="../estilo/estilo_login_reg.css">
</head>

<body>
    <div class="fondo-difuminado">
        <div class="titulo-login">Iniciar sesión</div>
        <div class="caja-login">
            
            <!-- Mostrar mensaje si existe -->
            <?php if ($texto_mensaje): ?>
                <p class="mensaje-alerta <?php echo $clase_mensaje; ?>">
                    <?php echo $texto_mensaje; ?>
                </p>
            <?php endif; ?>

            <!-- Formulario de Login -->
            <!-- A través de router_login.php, se verifica que el usuario existe -->
            <form action="../../BD2npb/router_login.php" method="POST">
                <input type="text" name="usuario" placeholder="Usuario">
                <input type="password" name="contrasena" placeholder="Contraseña">
                <input type="submit" class="boton" value="Entrar">
            </form>

            <!-- Enlace a registro en caso de no tener cuenta. LA CUENTA CREADA SERÁ TIPO VOLUNTARIO -->
            <div class="texto-abajo">
                ¿No tienes una cuenta y quieres ser voluntario?<br>
                <a href="registro.php">¡Regístrate!</a>
            </div>
        </div>
    </div>
</body>
</html>