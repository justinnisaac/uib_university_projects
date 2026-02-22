<?php
// Este programa es completamente backend, no tiene interfaz.
// De forma interna, se guarda el usuario creado a raíz del registro.

// 1. Recoger datos
$cuenta = $_POST["cuenta"];
$password = $_POST["contrasena"];
$nombre = $_POST["nombre"];
$apellidos = $_POST["apellidos"];
$telefono = $_POST["telefono"];
$email = $_POST["email"];
$id_ayuntamiento = $_POST["ayuntamiento"];

// 2. Conectar
$conexion = mysqli_connect("localhost", "root", "");
$db = mysqli_select_db($conexion, "BD2XAMPPions");

// 3. Comprobar que no existe el usuario ya en la BBDD
$consulta_check = "SELECT id_usuario FROM usuario WHERE nombre_usuario = '$cuenta'";
$resultado_check = mysqli_query($conexion, $consulta_check);

if (mysqli_num_rows($resultado_check) > 0) {
    mysqli_close($conexion);
    // Redirigir a login.php con mensaje de error
    header("Location: login.php?msg=error_existe");
    exit();
}

// 4. Si no existe el usuario, insertar en la tabla USUARIO
$sql_usuario = "INSERT INTO usuario (nombre_usuario, contrasena_hash, nombre, apellidos, telefono, email) 
                VALUES ('$cuenta', '$password', '$nombre', '$apellidos', '$telefono', '$email')";

if (mysqli_query($conexion, $sql_usuario)) {
    
    $id_nuevo = mysqli_insert_id($conexion);

    // 5. Buscar borsin asociado al ayuntamiento seleccionado por el nuevo voluntario
    $sql_borsin = "SELECT id_borsin FROM borsin_voluntarios WHERE id_ayuntamiento = '$id_ayuntamiento' LIMIT 1";
    $res_borsin = mysqli_query($conexion, $sql_borsin);
    
    $id_borsin = 'NULL'; 
    if ($fila = mysqli_fetch_array($res_borsin)) {
        $id_borsin = "'" . $fila['id_borsin'] . "'";
    }

    // 6. Insertar registro de voluntario (en sí, el nuevo usuario también es voluntario)
    $sql_voluntario = "INSERT INTO voluntario (id_voluntario, id_grupo, id_borsin) 
                       VALUES ('$id_nuevo', NULL, $id_borsin)";
    mysqli_query($conexion, $sql_voluntario);

    // 7. Insertar privilegios para el nuevo usuario (siempre tipo VOLUNTARIO, id_privilegios = 1)
    $sql_permiso = "INSERT INTO puede_hacer (id_usuario, id_privilegios) VALUES ('$id_nuevo', 1)";
    mysqli_query($conexion, $sql_permiso);

    // 8. ÉXITO -> Redirigir a login.php con mensaje de éxito
    mysqli_close($conexion);
    header("Location: login.php?msg=exito");
    exit();

} else {
    echo "Error al insertar: " . mysqli_error($conexion);
}
?>