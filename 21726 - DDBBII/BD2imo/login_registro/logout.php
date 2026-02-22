<?php
// 1. Iniciar la sesión para poder acceder a las variables de sesión
session_start();
// 2. Eliminar todas las variables de sesión
session_unset();
// 3. Destruir la sesión por completo
session_destroy();
// 4. Redirigir al usuario a la página de login
header("Location: login.php");
exit();
?>