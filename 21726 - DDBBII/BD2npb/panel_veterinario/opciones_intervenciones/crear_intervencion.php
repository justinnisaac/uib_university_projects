
<?php
session_start();

$mensaje = "";
$exito = false;
$vets_campana = null;
$id_campana = "";


if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../../../BD2imo/login_registro/login.php");
    exit();
}

$id_veterinario_log = $_SESSION["id_usuario"];

$conexion = mysqli_connect("localhost", "root", "", "BD2XAMPPions");



if (!empty($_POST["id_campana"])) {

    /* CARGAR VETERINARIOS CUANDO SE INTRODUCE CAMPAÑA */
    $id_campana = $_POST["id_campana"];

    $vets_campana = mysqli_query($conexion, "
        SELECT 
            u.id_usuario AS id_veterinario,
            u.nombre,
            u.apellidos,
            v.especialidad
        FROM participacion p
        JOIN veterinario v ON p.id_veterinario = v.id_veterinario
        JOIN usuario u ON u.id_usuario = v.id_veterinario
        AND p.id_campana = '$id_campana'
    ");

    /* GATOS DISPONIBLES EN LA CAMPAÑA */
    $gatos_campana = mysqli_query($conexion, "
        SELECT DISTINCT
            g.id_gato,
            g.nombre
        FROM campana c
        JOIN historial_colonia hc ON hc.id_colonia = c.id_colonia
        JOIN gato g ON g.id_gato = hc.id_gato
        JOIN estado_gato eg ON g.id_estado = eg.id_estado
        WHERE c.id_campana = '$id_campana'
          AND eg.estado <> 'Difunto'
          AND hc.fecha_ingreso <= COALESCE(c.fechaFin, CURDATE())
          AND (hc.fecha_salida IS NULL OR hc.fecha_salida >= c.fechaInicio)
    ");
}


/*
   CREAR INTERVENCIÓN (solo si viene todo COMPLETO)
*/
if (
    !empty($_POST["id_gato"]) && 
    !empty($_POST["fecha"]) && 
    !empty($_POST["comentario"]) && 
    !empty($_POST["veterinarios"]) &&
    !empty($_POST["id_campana"])  // Asegurar que también tiene campaña
) {

    // Asegurarnos de que id_campana está definida
    $id_campana = $_POST["id_campana"] ?? "";
    $id_gato      = $_POST["id_gato"];
    $fecha        = $_POST["fecha"];
    $comentario   = $_POST["comentario"];
    $veterinarios = $_POST["veterinarios"];

    /* 1. Gato existe y no es difunto */
    $res_gato = mysqli_query($conexion, "
        SELECT eg.estado
        FROM gato g
        JOIN estado_gato eg ON g.id_estado = eg.id_estado
        AND g.id_gato = '$id_gato'
    ");

    if (!$res_gato || mysqli_num_rows($res_gato) == 0) {
        $mensaje = "El gato no existe";

    } else {
        $gato = mysqli_fetch_assoc($res_gato);

        if ($gato["estado"] === "Difunto") {
            $mensaje = "No se puede intervenir un gato difunto";

        } else {

            /* 2. Veterinario creador participa en campaña */
            $res_part = mysqli_query($conexion, "
                SELECT 1 FROM participacion
                WHERE id_campana = '$id_campana'
                AND id_veterinario = '$id_veterinario_log'
            ");

            if (mysqli_num_rows($res_part) == 0) {
                $mensaje = "No participas en esta campaña";

            } else {

                /* 2.5 Verificar que el veterinario logueado esté en la lista seleccionada */
                $logueado_seleccionado = false;
                foreach ($veterinarios as $vet) {
                    if ($vet == $id_veterinario_log) {
                        $logueado_seleccionado = true;
                        break;
                    }
                }
                
                if (!$logueado_seleccionado) {
                    $mensaje = "Solo puedes crear intervenciones en las que participes tú";
                } else {

                    /* 3. Fecha válida */
                    $res_fecha = mysqli_query($conexion, "
                        SELECT 1
                        FROM campana c
                        JOIN historial_colonia hc ON hc.id_colonia = c.id_colonia
                        AND c.id_campana = '$id_campana'
                        AND hc.id_gato = '$id_gato'
                        AND hc.fecha_ingreso <= '$fecha'
                        AND (hc.fecha_salida IS NULL OR hc.fecha_salida >= '$fecha')
                        AND c.fechaInicio <= '$fecha'
                        AND (c.fechaFin IS NULL OR c.fechaFin >= '$fecha')
                    ");

                    if (mysqli_num_rows($res_fecha) == 0) {
                        $mensaje = "El gato no esta en la campaña o la fecha es incorrecta";

                    } else {

                        /* 4. Insertar intervención */
                        mysqli_query($conexion, "
                            INSERT INTO intervencion_veterinaria
                            (fecha, comentario, id_gato, id_campana)
                            VALUES ('$fecha', '$comentario', '$id_gato', '$id_campana')
                        ");

                        $id_intervencion = mysqli_insert_id($conexion);

                        /* 5. Insertar veterinarios */
                        foreach ($veterinarios as $vet) {
                            mysqli_query($conexion, "
                                INSERT INTO veterinario_accion
                                (id_veterinario, id_intervencion)
                                VALUES ('$vet', '$id_intervencion')
                            ");
                        }

                        
                        /* 6. Actualizar estado del gato si estaba Enfermo o Herido */
                        if ($gato["estado"] === "Enfermo" || $gato["estado"] === "Herido") {
                            
                            $res_estado = mysqli_query($conexion, "
                                SELECT id_estado FROM estado_gato WHERE estado = 'Saludable'
                            ");
                            $estado = mysqli_fetch_assoc($res_estado);
                            $id_estado_saludable = $estado["id_estado"];

                            mysqli_query($conexion, "
                                UPDATE gato 
                                SET id_estado = '$id_estado_saludable'
                                WHERE id_gato = '$id_gato'
                            ");
                        }

                        $mensaje = "Intervención creada correctamente";
                        $exito = true;
                    }
                }
            }
        }
    }
}

mysqli_close($conexion);
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Crear intervención</title>
    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../../BD2imo/estilo/estilo_contenido.css">
    <style>
        .contenedor-checkboxes {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            background: #f9f9f9;
            margin-top: 5px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            padding: 8px 5px;
            border-bottom: 1px solid #eee;
        }
        
        .checkbox-item:last-child {
            border-bottom: none;
        }
        
        .checkbox-item input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.2);
        }
        
        .checkbox-item label {
            cursor: pointer;
            flex-grow: 1;
        }
        
        .checkbox-item:hover {
            background: #f0f0f0;
        }
    </style>
</head>

<body>
<div style="display:flex;">

<?php include("../panel_opciones_veterinario.php"); ?>

<div class="zona-contenido">
<div class="contenedor-difuminado">

<h2 class="titulo-dashboard">Crear intervención</h2>

<?php if ($mensaje): ?>
<p class="mensaje-alerta <?php echo $exito ? 'mensaje-exito' : 'mensaje-error'; ?>">
    <?php echo $mensaje; ?>
</p>
<?php endif; ?>

<form method="POST" class="formulario-colonia">

<label>ID campaña:</label>
<input type="number" name="id_campana" required 
       value="<?= htmlspecialchars($id_campana) ?>"
       onchange="this.form.submit()">

<label>ID gato:</label>
<select name="id_gato" required>
    <?php
        if (empty($id_campana)) {
            echo "<option value=''>Introduce una campaña primero</option>";
        }
        elseif (!$gatos_campana) {
            echo "<option value=''>Error al cargar gatos</option>";
        }
        elseif (mysqli_num_rows($gatos_campana) === 0) {
            echo "<option value=''>No hay gatos disponibles</option>";
        }
        else {
            echo "<option value=''>-- Selecciona un gato --</option>";
            while ($g = mysqli_fetch_assoc($gatos_campana)) {
                echo "<option value='{$g['id_gato']}'>
                        {$g['nombre']} (ID {$g['id_gato']})
                    </option>";
            }
        }
    ?>
</select>

<label>Fecha:</label>
<input type="date" name="fecha" required>

<label>Comentario:</label>
<input type="text" name="comentario" required>

<label>Veterinarios participantes:</label>

<div class="contenedor-checkboxes" id="contenedor-veterinarios">
<?php
if (empty($id_campana)) {
    echo "<p style='color: #666; padding: 10px; text-align: center;'>
          Introduce una campaña para cargar veterinarios</p>";
}
elseif (!$vets_campana) {
    echo "<p style='color: #d00; padding: 10px; text-align: center;'>
          Error al cargar veterinarios</p>";
}
elseif (mysqli_num_rows($vets_campana) === 0) {
    echo "<p style='color: #666; padding: 10px; text-align: center;'>
          No hay veterinarios en esta campaña</p>";
}
else {
    // Reiniciar el puntero del resultset si ya se había iterado
    mysqli_data_seek($vets_campana, 0);
    
    while ($v = mysqli_fetch_assoc($vets_campana)) {
        echo "<div class='checkbox-item'>";
        echo "<input type='checkbox' 
                     name='veterinarios[]' 
                     value='{$v['id_veterinario']}' 
                     id='vet_{$v['id_veterinario']}'>";
        echo "<label for='vet_{$v['id_veterinario']}'>
              <strong>{$v['nombre']} {$v['apellidos']}</strong> 
              <span style='color: #666; font-size: 0.9em;'>- {$v['especialidad']}</span>
              </label>";
        echo "</div>";
    }
}
?>
</div>

<button class="boton-gestion boton-confirmar">
    Confirmar intervención
</button>

</form>

</div>
</div>
</div>

<script>
// Validación para asegurar que al menos un veterinario esté seleccionado
document.querySelector('form').addEventListener('submit', function(e) {
    const checkboxes = document.querySelectorAll('input[name="veterinarios[]"]:checked');
    
    if (checkboxes.length === 0) {
        e.preventDefault();
        alert('Debes seleccionar al menos un veterinario participante');
        return false;
    }
});
</script>
</body>
</html>