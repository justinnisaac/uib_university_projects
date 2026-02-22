
<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../../BD2imo/login_registro/login.php");
    exit();
}

$id_voluntario = $_SESSION["id_usuario"];

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

$consulta = "
    SELECT u.nombre_usuario, u.nombre, u.apellidos, u.telefono, u.email
    FROM usuario u
    WHERE u.id_usuario = '$id_voluntario'
";

$resultado = mysqli_query($conexion, $consulta);
$datos = mysqli_fetch_assoc($resultado);

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mi perfil</title>

    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>
<div style="display:flex;">

    <?php include("panel_opciones_voluntario.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

        <h2 class="titulo-dashboard">Datos del voluntario</h2>

            <div class="formulario-colonia">

            <label>Usuario:</label>
            <input type="text" value="<?php echo htmlspecialchars($datos["nombre_usuario"]); ?>" readonly>

            <label>Nombre:</label>
            <input type="text" value="<?php echo htmlspecialchars($datos["nombre"]); ?>" readonly>

            <label>Apellidos:</label>
            <input type="text" value="<?php echo htmlspecialchars($datos["apellidos"]); ?>" readonly>

            <label>Teléfono:</label>
            <input type="text" value="<?php echo htmlspecialchars($datos["telefono"]); ?>" readonly>

            <label>Email:</label>
            <input type="text" value="<?php echo htmlspecialchars($datos["email"]); ?>" readonly>

            <button type="button"
                    class="boton-gestion boton-confirmar"
                    onclick="location.href='principal_voluntario.php'"
                    style="margin-top:30px;">
                Volver
            </button>

            </div>
        </div>
    </div>
</div>
</body>
</html>
