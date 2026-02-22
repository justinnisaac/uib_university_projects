
<?php
session_start();

if (!isset($_SESSION["id_usuario"]) || !isset($_GET["id"])) {
    header("Location: ../../../BD2imo/login_registro/login.php");
    exit();
}

$id_intervencion = $_GET["id"];
$id_veterinario  = $_SESSION["id_usuario"];

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

/* Verificar que el veterinario participa en la intervención */
$check = mysqli_query(
    $conexion,
    "
    SELECT 1
    FROM veterinario_accion
    WHERE id_intervencion = '$id_intervencion'
      AND id_veterinario = '$id_veterinario'
    "
);

if (!$check || mysqli_num_rows($check) == 0) {
    mysqli_close($conexion);
    header("Location: ../../../BD2imo/login_registro/login.php");
    exit();
}

/* Confirmación */
if (isset($_POST["confirmar"])) {

    /* Borrar TODAS las relaciones */
    mysqli_query(
        $conexion,
        "
        DELETE FROM veterinario_accion
        WHERE id_intervencion = '$id_intervencion'
        "
    );

    /* Borrar la intervención */
    mysqli_query(
        $conexion,
        "
        DELETE FROM intervencion_veterinaria
        WHERE id_intervencion = '$id_intervencion'
        "
    );

    mysqli_close($conexion);
    header("Location: ../vet_intervenciones.php");
    exit();
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Eliminar intervención</title>
<link rel="stylesheet" href="../../../BD2imo/estilo/estilo_panel.css">
<link rel="stylesheet" href="../../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>
<div style="display:flex;">

<?php include("../panel_opciones_veterinario.php"); ?>

<div class="zona-contenido">
<div class="contenedor-difuminado">

<h2 class="titulo-dashboard">Eliminar intervención</h2>

<p class="mensaje-alerta mensaje-error" style="text-align:center;">
¿Seguro que quieres eliminar esta intervención para TODOS los veterinarios?
</p>

<form method="POST" style="display:flex; gap:20px;">
<button name="confirmar" class="boton-gestion" style="background:#F44336; flex:1;">
Sí, eliminar
</button>

<button type="button" class="boton-gestion" style="flex:1;"
        onclick="location.href='../vet_intervenciones.php'">
Cancelar
</button>
</form>

</div>
</div>
</div>
</body>
</html>
