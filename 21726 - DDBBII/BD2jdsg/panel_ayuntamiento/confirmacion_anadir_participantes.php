<?php
session_start();

//Variables que paso por URL
$id_campana     = $_GET['id']  ?? null;
$id_veterinario = $_GET['vet'] ?? null;

$mensaje = "";
$exito = false;
$accion_ejecutada = false;
$nombre_veterinario = "";

//Confirmación de datos
if (isset($_POST['confirmar_add']) && $id_campana && $id_veterinario) {

    $conexion = mysqli_connect("localhost", "root", "");
    mysqli_select_db($conexion, "BD2XAMPPions");

    $sql_insert = "
        INSERT INTO participacion (id_campana, id_veterinario)
        VALUES ('$id_campana', '$id_veterinario')
    ";

    if (mysqli_query($conexion, $sql_insert)) {
        $mensaje = "Veterinario añadido correctamente a la campaña.";
        $exito = true;
    } else {
        $mensaje = "Error al añadir el veterinario: " . mysqli_error($conexion);
        $exito = false;
    }

    $accion_ejecutada = true;
    mysqli_close($conexion);
}

//Carga de datos iniciales
if (!$accion_ejecutada && $id_veterinario) {

    $conexion = mysqli_connect("localhost", "root", "");
    mysqli_select_db($conexion, "BD2XAMPPions");

    $sql_vet = "
        SELECT nombre, apellidos
        FROM usuario
        WHERE id_usuario = '$id_veterinario'
    ";

    $res = mysqli_query($conexion, $sql_vet);
    if ($row = mysqli_fetch_array($res)) {
        $nombre_veterinario = $row['nombre'] . " " . $row['apellidos'];
    } else {
        $mensaje = "Veterinario no encontrado.";
        $accion_ejecutada = true;
    }

    mysqli_close($conexion);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Confirmación añadir participante</title>

    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../../BD2imo/panel_ayuntamiento/panel_opciones.php"); ?>

    <!-- Contenido -->
    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">
                Añadir veterinario a la campaña #<?php echo htmlspecialchars($id_campana); ?>
            </h2>

            <?php if ($mensaje): ?>

                <!-- MENSAJE FINAL -->
                <p class="mensaje-alerta <?php echo $exito ? 'mensaje-exito' : 'mensaje-error'; ?>"
                   style="text-align: center; margin-bottom: 25px;">
                    <?php echo htmlspecialchars($mensaje); ?>
                </p>

                <div style="text-align:center;">
                    <button class="boton-gestion boton-confirmar"
                            onclick="location.href='../../BD2imo/panel_ayuntamiento/gestionar_campanas.php'">
                        Volver al listado de campañas
                    </button>
                </div>

            <?php elseif ($id_campana && $id_veterinario): ?>

                <!-- CONFIRMACIÓN -->
                <div style="max-width: 520px; margin: 0 auto;">

                    <p style="font-size: 18px; text-align: center; margin-bottom: 30px; font-weight: bold;">
                        Se va a añadir al veterinario<br>
                        <span style="color: #F44336;">
                            <?php echo htmlspecialchars($nombre_veterinario); ?>
                        </span><br>
                        a la campaña <strong>#<?php echo htmlspecialchars($id_campana); ?></strong>.
                        <br><br>
                        ¿Estás seguro?
                    </p>

                    <form method="POST" style="display: flex; gap: 20px;">
                        <button type="submit"
                                name="confirmar_add"
                                class="boton-gestion"
                                style="background-color: #2c8a01ff; flex: 1;">
                            Confirmar
                        </button>
                    </form>

                    <button type="button"
                            class="boton-gestion boton-confirmar"
                            onclick="location.href='../../BD2imo/panel_ayuntamiento/gestionar_campanas.php'"
                            style="margin-top: 15px; background-color: #555;">
                        Volver atrás
                    </button>

                </div>

            <?php else: ?>

                <!-- ERROR -->
                <p class="mensaje-alerta mensaje-error" style="text-align: center;">
                    No se han recibido los datos necesarios.
                </p>

                <div style="text-align:center;">
                    <button class="boton-gestion boton-confirmar"
                            onclick="location.href='../gestionar_campanas.php'">
                        Volver
                    </button>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
