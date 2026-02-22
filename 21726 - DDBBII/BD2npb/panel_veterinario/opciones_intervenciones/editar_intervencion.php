
<?php
session_start();

if (!isset($_SESSION["id_usuario"]) || !isset($_GET["id"])) {
    header("Location: ../../../BD2imo/login_registro/login.php");
    exit();
}

$id_intervencion = $_GET["id"];
$id_veterinario = $_SESSION["id_usuario"];

$mensaje_error = "";

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

/* 
   Obtenemos la intervención
*/
$consulta = "
    SELECT 
        iv.fecha, 
        iv.comentario, 
        iv.id_gato,
        iv.id_campana,
        eg.estado AS estado_gato
    FROM intervencion_veterinaria iv
    JOIN veterinario_accion va ON iv.id_intervencion = va.id_intervencion
    JOIN gato g ON iv.id_gato = g.id_gato
    JOIN estado_gato eg ON g.id_estado = eg.id_estado
    AND iv.id_intervencion = '$id_intervencion'
    AND va.id_veterinario = '$id_veterinario'

";

$resultado = mysqli_query($conexion, $consulta);

if (!$resultado || mysqli_num_rows($resultado) == 0) {
    mysqli_close($conexion);
    header("Location: ../vet_intervenciones.php");
    exit();
}

$intervencion = mysqli_fetch_array($resultado);

/* 
   Procesar edición de la intervención
*/
if (isset($_POST["fecha"])) {

    $fecha = $_POST["fecha"];
    $comentario = $_POST["comentario"];
    $estado_gato = $_POST["estado_gato"]; // Nuevo campo
    $id_gato = $intervencion["id_gato"];
    $id_campana = $intervencion["id_campana"];

    /* ---- VALIDAR ESTADO DEL GATO ---- */
    $estados_validos = array("Saludable", "Enfermo", "Herido", "Difunto");
    if (!in_array($estado_gato, $estados_validos)) {

        $mensaje_error = "Estado del gato no válido";

    } else {

        /* ---- VALIDAR COLONIA + CAMPAÑA PARA LA FECHA ---- */
        $validacion = "
            SELECT 1
            FROM campana c
            JOIN historial_colonia hc ON hc.id_colonia = c.id_colonia
            AND c.id_campana = '$id_campana'
            AND hc.id_gato = '$id_gato'
            AND hc.fecha_ingreso <= '$fecha'
            AND (hc.fecha_salida IS NULL OR hc.fecha_salida >= '$fecha')
            AND c.fechaInicio <= '$fecha'
            AND (c.fechaFin IS NULL OR c.fechaFin >= '$fecha');
        ";

        $res_validacion = mysqli_query($conexion, $validacion);

        if (!$res_validacion || mysqli_num_rows($res_validacion) == 0) {

            $mensaje_error = "Fecha incorrecta";

        } else {

            /* ---- UPDATE FINAL ---- */
            $update = "
                UPDATE intervencion_veterinaria iv
                JOIN veterinario_accion va ON iv.id_intervencion = va.id_intervencion
                SET iv.fecha = '$fecha',
                    iv.comentario = '$comentario'
                WHERE iv.id_intervencion = '$id_intervencion'
                  AND va.id_veterinario = '$id_veterinario'
            ";

            mysqli_query($conexion, $update);

            /* ---- UPDATE ESTADO DEL GATO ---- */
            $res_estado = mysqli_query($conexion, "
                SELECT id_estado FROM estado_gato WHERE estado = '$estado_gato'
            ");

            $estado = mysqli_fetch_assoc($res_estado);
            $id_estado = $estado["id_estado"];

            mysqli_query($conexion, "
                UPDATE gato
                SET id_estado = '$id_estado'
                WHERE id_gato = '$id_gato'
            ");

            mysqli_close($conexion);

            header("Location: ../vet_intervenciones.php");
            exit();
        }
    }

    /* Mantener valores introducidos */
    $intervencion["fecha"] = $fecha;
    $intervencion["comentario"] = $comentario;
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Editar intervención</title>

    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>
<div style="display:flex;">

    <?php include("../panel_opciones_veterinario.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Editar intervención</h2>

            <?php if (!empty($mensaje_error)) : ?>
                <p class="mensaje-alerta mensaje-error">
                    ❌ <?php echo $mensaje_error; ?>
                </p>
            <?php endif; ?>

            <form method="POST" class="formulario-colonia">

                <label>Fecha:</label>
                <input type="date" name="fecha"
                       value="<?php echo htmlspecialchars($intervencion["fecha"]); ?>" required>

                <label>Comentario:</label>
                <input type="text" name="comentario"
                       value="<?php echo htmlspecialchars($intervencion["comentario"]); ?>" required>

                <label>Estado del gato:</label>
                <select name="estado_gato" required>
                    <option value="Saludable" <?php echo ($intervencion["estado_gato"] == "Saludable") ? "selected" : ""; ?>>Saludable</option>
                    <option value="Enfermo" <?php echo ($intervencion["estado_gato"] == "Enfermo") ? "selected" : ""; ?>>Enfermo</option>
                    <option value="Herido" <?php echo ($intervencion["estado_gato"] == "Herido") ? "selected" : ""; ?>>Herido</option>
                    <option value="Difunto" <?php echo ($intervencion["estado_gato"] == "Difunto") ? "selected" : ""; ?>>Difunto</option>
                </select>

                <button type="submit" class="boton-gestion boton-confirmar">
                    Guardar cambios
                </button>

            </form>

        </div>
    </div>
</div>
</body>
</html>
