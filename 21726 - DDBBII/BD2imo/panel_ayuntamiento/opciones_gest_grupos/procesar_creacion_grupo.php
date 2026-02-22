<?php
// Iniciar sesión
session_start();

// Seguridad: Verificar sesión del ayuntamiento
if (!isset($_SESSION["id_ayuntamiento"])) {
    header("Location: ../login_registro/login.php"); 
    exit();
}

$id_ayuntamiento = $_SESSION["id_ayuntamiento"];

// Recoger datos del formulario
$nombre_grupo = $_POST['nombre_grupo'] ?? '';
$id_primer_miembro = $_POST['id_primer_miembro'] ?? '';

// Validar que los datos existan
if (empty($nombre_grupo) || empty($id_primer_miembro)) {
    // Si falta algo, volvemos atrás
    header("Location: crear_grupo.php");
    exit();
}

// 1. Conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// Paso 1: Crear el grupo
$sql_insert_grupo = "INSERT INTO grupo_control_felino (nombre, id_ayuntamiento) 
                     VALUES ('$nombre_grupo', '$id_ayuntamiento')";

if (mysqli_query($conexion, $sql_insert_grupo)) {
    
    // Obtenemos el ID del grupo recién creado (AUTO_INCREMENT)
    $id_nuevo_grupo = mysqli_insert_id($conexion);

    // Paso 2: Asignar el grupo al voluntario
    // Actualizamos la tabla voluntario para ponerle el fk del nuevo grupo
    $sql_update_voluntario = "UPDATE voluntario 
                              SET id_grupo = '$id_nuevo_grupo' 
                              WHERE id_voluntario = '$id_primer_miembro'";
    mysqli_query($conexion, $sql_update_voluntario);
    
    // Paso 3: Actualizar privilegios del voluntario a responsable
    // Actualizamos la tabla puede_hacer para cambiar su privilegio
    // ID Privilegio 1 = Voluntario -> ID Privilegio 2 = Responsable
    $sql_update_privilegios = "UPDATE puede_hacer 
                               SET id_privilegios = 2 
                               WHERE id_usuario = '$id_primer_miembro' AND id_privilegios = 1";
    mysqli_query($conexion, $sql_update_privilegios);
    
} else {
    // Error en la creación del grupo
    echo "Error al crear el grupo: " . mysqli_error($conexion);
    exit();
}

// 4. Cerrar conexión
mysqli_close($conexion);

// 5. Redirección final
header("Location: ../gestionar_grupos.php");
exit();
?>