
<?php
session_start();

if (!isset($_SESSION["id_usuario"]) || !isset($_POST["id_tarea"])) {
    header("Location: vol_tareas.php");
    exit();
}

$id_voluntario = $_SESSION["id_usuario"];
$id_tarea = $_POST["id_tarea"];

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

/* Verificar que la tarea pertenece al voluntario */
$check = "
    SELECT 1
    FROM tarea
    WHERE id_tarea = '$id_tarea'
      AND id_voluntario = '$id_voluntario'
";

$res = mysqli_query($conexion, $check);

if ($res && mysqli_num_rows($res) > 0) {

    $update = "
        UPDATE tarea
        SET completada = TRUE
        WHERE id_tarea = '$id_tarea'
    ";

    mysqli_query($conexion, $update);
}

mysqli_close($conexion);
header("Location: vol_tareas.php");
exit();
