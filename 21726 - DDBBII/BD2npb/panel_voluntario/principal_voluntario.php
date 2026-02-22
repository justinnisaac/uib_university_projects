
<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../BD2imo/login_registro/login.php");
    exit();
}

$usuario = $_SESSION["usuario"];
$id_usuario = $_SESSION["id_usuario"];

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");


$num_colonias = 0;
$num_tareas = 0;

/* 
   CONTAR COLONIAS DEL AYUNTAMIENTO
*/
$consulta_colonias = "
    SELECT COUNT(c.id_colonia) AS total
    FROM voluntario v
    JOIN borsin_voluntarios b ON v.id_borsin = b.id_borsin
    JOIN colonia c ON b.id_ayuntamiento = c.id_ayuntamiento
    AND v.id_voluntario = '$id_usuario'
";

$res_colonias = mysqli_query($conexion, $consulta_colonias);
if ($fila = mysqli_fetch_array($res_colonias)) {
    $num_colonias = $fila["total"];
}

/* 
   CONTAR TAREAS DEL VOLUNTARIO
*/
$consulta_tareas = "
    SELECT COUNT(t.id_tarea) AS total
    FROM tarea t
    WHERE t.id_voluntario = '$id_usuario'
";

$res_tareas = mysqli_query($conexion, $consulta_tareas);
if ($fila = mysqli_fetch_array($res_tareas)) {
    $num_tareas = $fila["total"];
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Panel Voluntario</title>

    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display:flex; flex-direction:row;">

    <?php include("panel_opciones_voluntario.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <!-- ICONO CONFIGURACIÓN -->
            <div style="display:flex; justify-content:flex-end;">
                <img src="../../BD2imo/estilo/ajustes.jpg"
                    alt="Perfil"
                    title="Ver perfil"
                    style="width:30px; cursor:pointer;"
                    onclick="location.href='ver_voluntario.php'">
            </div>
            <h2 class="titulo-dashboard">
                Panel del voluntario <?php echo htmlspecialchars($usuario); ?>
            </h2>

            <div class="grid-estadisticas">

                <div class="caja-estado">
                    <div class="numero-grande"><?php echo $num_colonias; ?></div>
                    <div class="texto-caja">Colonias del ayuntamiento</div>
                    <button class="boton-gestion"
                            onclick="location.href='vol_colonias.php'">
                        Ver colonias
                    </button>
                </div>

                <div class="caja-estado">
                    <div class="numero-grande"><?php echo $num_tareas; ?></div>
                    <div class="texto-caja">Tareas asignadas</div>
                    <button class="boton-gestion"
                            onclick="location.href='vol_tareas.php'">
                        Ver tareas
                    </button>
                </div>

            </div>

        </div>
    </div>
</div>

</body>
</html>
