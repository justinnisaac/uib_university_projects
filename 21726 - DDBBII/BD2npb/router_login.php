<?php
// INICIAMOS LA SESIÓN: El router solo guarda el ID del usuario.
session_start();

// 1. Recoger datos del formulario
$usuario = $_POST['usuario'] ?? "";
$password = $_POST['contrasena'] ?? $_POST['password'] ?? ""; // Acepta 'contrasena'

// 2. Conectar a MySQL
$conexio = mysqli_connect("localhost", "root", "");
$db = mysqli_select_db($conexio, "BD2XAMPPions");

// 3. Buscar la cuenta del usuario
$consulta = "
    SELECT id_usuario, contrasena_hash
    FROM usuario
    WHERE nombre_usuario = '$usuario'
        AND contrasena_hash = '$password'
";

$resultat = mysqli_query($conexio, $consulta);

// Si no existe la cuenta -> volver al login
if (mysqli_num_rows($resultat) == 0) {
    mysqli_close($conexio);
    header("Location: ../BD2imo/login_registro/login.php?error=1");
    exit();
}

// 4. Obtener datos básicos y guardarlos en SESIÓN
$reg_usuario = mysqli_fetch_array($resultat);
$id_usuario = $reg_usuario['id_usuario'];

$_SESSION['usuario'] = $usuario;       // Guardamos el nombre de usuario
$_SESSION['id_usuario'] = $id_usuario; // Guardamos el ID de usuario

// 5. Buscar privilegios del usuario (Manteniendo la lógica de roles)
$consultaPriv = "
    SELECT p.privilegiosVoluntario,
           p.privilegiosResponsable,
           p.privilegiosVeterinario,
           p.privilegiosAyuntamiento
    FROM puede_hacer ph
         JOIN privilegios p ON ph.id_privilegios = p.id_privilegios
    WHERE ph.id_usuario = '$id_usuario'
";

$resultPriv = mysqli_query($conexio, $consultaPriv);
$priv = mysqli_fetch_array($resultPriv);

mysqli_close($conexio);

// 6. Redirigir usando header()

if ($priv['privilegiosAyuntamiento']) {
    // Redirigimos al panel principal. La lógica de negocio irá en principal_ayuntamiento.php
    header("Location: ../BD2imo/panel_ayuntamiento/principal_ayuntamiento.php");
    exit();
}

if ($priv['privilegiosVoluntario']) {
    header("Location: ../BD2npb/panel_voluntario/principal_voluntario.php");
    exit();
}

if ($priv['privilegiosVeterinario']) {
    header("Location: ../BD2npb/panel_veterinario/principal_veterinario.php");
    exit();
}

if ($priv['privilegiosResponsable']) {
    header("Location: ../BD2jdsg/panel_responsable/principal_responsable.php");
    exit();
}

// Si no tiene ningún privilegio
header("Location: ../BD2imo/login.php?error=sin_permiso");
exit();
?>
