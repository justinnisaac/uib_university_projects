<?php
// 1. Conexión a la base de datos para obtener el listado de los ayuntamientos disponibles 
// en la BBDD. Actualmente se recogen todos los ayuntamientos sin filtro alguno,
// pero la BBDD está diseñada para poder extenderse a diferentes Provincias,
// CCAAs o incluso países en el futuro.
$conexion = mysqli_connect("localhost", "root", "");
$db = mysqli_select_db($conexion, "BD2XAMPPions");

// 2. Consulta para obtener todos los ayuntamientos
$consulta_aytos = "SELECT id_ayuntamiento, nombre, direccion FROM ayuntamiento";
$resultado_aytos = mysqli_query($conexion, $consulta_aytos);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Regístrate</title>
    <link rel="stylesheet" href="../estilo/estilo_login_reg.css">
</head>

<body>
    <div class="fondo-difuminado">

        <div class="titulo-registro">Regístrate</div>

        <div class="caja-registro">
            <form action="insertar_usuario.php" method="POST">

                <!-- Campos a rellenar por el usuario -->
                <input type="text" name="cuenta" placeholder="Nombre de usuario" required>
                <input type="password" name="contrasena" placeholder="Contraseña" required>
                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="text" name="apellidos" placeholder="Apellidos" required>
                <input type="text" name="telefono" placeholder="Teléfono" required>
                <input type="text" name="email" placeholder="Correo electrónico" required>

                <!-- Menú desplegable para seleccionar ayuntamiento -->
                <select name="ayuntamiento" class="select-campo" required>
                    <option value="">Selecciona un ayuntamiento</option>
                    <?php
                    // 3. Generar las opciones del menú desplegable con los ayuntamientos obtenidos
                    if ($resultado_aytos) {
                        while ($fila = mysqli_fetch_array($resultado_aytos)) {
                            // Usamos id_ayuntamiento como valor y mostramos el nombre
                            echo '<option value="' . htmlspecialchars($fila['id_ayuntamiento']) . '">' . 
                                 htmlspecialchars($fila['nombre']) . 
                                 '</option>';
                        }
                    }
                    ?>
                </select>
                <input type="submit" class="boton" value="Registrarse">
            </form>

            <!-- Si se quiere volver a login, se proporciona un enlace -->
            <div class="texto-abajo">
                ¿Ya tienes una cuenta?<br>
                <a href="login.php">Inicia sesión</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// 4. Cerrar la conexión al final de la página
mysqli_close($conexion);
?>