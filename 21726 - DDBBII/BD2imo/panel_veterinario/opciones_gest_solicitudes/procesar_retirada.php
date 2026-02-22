<?php
session_start();
// Programa que es procedimiento backend, no tiene interfaz propia.

// Seguridad: Verificar sesión
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../login_registro/login.php");
    exit();
}

$id_veterinario = $_SESSION["id_usuario"];

// Recoger datos del POST
$id_solicitud = $_POST['id_solicitud'] ?? '';
$id_gato = $_POST['id_gato'] ?? '';
$id_responsable = $_POST['id_responsable'] ?? '';
$comentarios = $_POST['comentarios_autopsia'] ?? '';
$id_cementerio = $_POST['id_cementerio'] ?? '';

// Validar datos mínimos
if (empty($id_solicitud) || empty($id_gato) || empty($id_cementerio)) {
    // Si falta algo crítico, volvemos atrás
    header("Location: realizar_retirada.php?id=$id_solicitud&error=datos_faltantes");
    exit();
}

// Conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// 1. Obtner datos necesarios
$fecha_actual = date('Y-m-d'); // Fecha actual

// A) Obtener fecha de la solicitud original
$q_sol = "SELECT fecha_solicitud FROM solicitud_retirada WHERE id_solicitud = '$id_solicitud'";
$res_sol = mysqli_query($conexion, $q_sol);
$row_sol = mysqli_fetch_array($res_sol);
$fecha_solicitud = $row_sol['fecha_solicitud'];

// B) Obtener el ID del centro veterinario asociado al veterinario actual
$q_vet = "SELECT id_centro FROM veterinario WHERE id_veterinario = '$id_veterinario'";
$res_vet = mysqli_query($conexion, $q_vet);
$row_vet = mysqli_fetch_array($res_vet);
$id_centro = $row_vet['id_centro'];


// 2. Insertar registro en la tabla 'retirada'
$insert_retirada = "
    INSERT INTO retirada (
        fecha_retirada, 
        comentarios_autopsia,  
        id_veterinario,
        id_cementerio,
        id_solicitud
    ) VALUES (
        '$fecha_actual',
        '$comentarios',
        '$id_veterinario',
        '$id_cementerio',
        '$id_solicitud'
    )
";
mysqli_query($conexion, $insert_retirada);


// 3. Actualizar estado de la solicitud a 'Aprobada' (True)
$update_solicitud = "UPDATE solicitud_retirada SET aprobada = TRUE WHERE id_solicitud = '$id_solicitud'";
mysqli_query($conexion, $update_solicitud);

// 4. Actualizar historial colonia (Cerrar estancia)
// Ponemos la fecha de salida igual a la fecha de retirada (hoy).
// De forma automática, cuando se quiera revisar los gatos de la colonia en la que
// estaba este gato, ya no aparecerá porque su fecha de salida ya no es NULL.
$update_historial = "
    UPDATE historial_colonia 
    SET fecha_salida = '$fecha_actual' 
    WHERE id_gato = '$id_gato' AND fecha_salida IS NULL
";
mysqli_query($conexion, $update_historial);


// Cerrar conexión
mysqli_close($conexion);

// Volver al listado de solicitudes
header("Location: ../gestionar_solicitudes_retirada.php");
exit();
?>