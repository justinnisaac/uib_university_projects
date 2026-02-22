<?php
// Iniciar sesión
session_start();

// Seguridad: Verificar sesión del ayuntamiento
if (!isset($_SESSION["id_ayuntamiento"])) {
    header("Location: ../../login_registro/login.php"); 
    exit();
}

// Recoger datos del formulario POST
$id_grupo = $_POST['id_grupo'] ?? '';
$id_resp_actual = $_POST['id_resp_actual'] ?? ''; // Puede venir vacío si no había responsable
$id_nuevo_resp = $_POST['nuevo_responsable'] ?? '';

// Validar que al menos se haya seleccionado un nuevo responsable
if (empty($id_nuevo_resp) || empty($id_grupo)) {
    // Si faltan datos clave, volvemos sin hacer nada
    header("Location: ../gestionar_grupos.php");
    exit();
}

// 1. Conexión a la BBDD
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// LÓGICA DE INTERCAMBIO DE PRIVILEGIOS
// ID Privilegio 1 = Voluntario
// ID Privilegio 2 = Responsable

// Paso A: "Degradar" al responsable actual (si existe) a Voluntario (ID 1)
if (!empty($id_resp_actual)) {
    // Cambiamos su privilegio de 2 a 1 en la tabla puede_hacer
    $sql_bajar = "UPDATE puede_hacer 
                  SET id_privilegios = 1 
                  WHERE id_usuario = '$id_resp_actual' AND id_privilegios = 2";
    mysqli_query($conexion, $sql_bajar);
}

// Paso B: "Ascender" al nuevo responsable a Responsable (ID 2)
if (!empty($id_nuevo_resp)) {
    // Cambiamos su privilegio de 1 a 2 en la tabla puede_hacer
    $sql_subir = "UPDATE puede_hacer 
                  SET id_privilegios = 2 
                  WHERE id_usuario = '$id_nuevo_resp' AND id_privilegios = 1";
    mysqli_query($conexion, $sql_subir);
}

// 2. Cerrar conexión
mysqli_close($conexion);

// 3. Redirección final
header("Location: ../gestionar_grupos.php");
exit();
?>