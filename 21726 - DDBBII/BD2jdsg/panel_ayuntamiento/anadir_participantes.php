<?php

session_start();

// Seguridad ayuntamiento
if (!isset($_SESSION["id_ayuntamiento"])) {
    header("Location: ../login_registro/login.php"); 
    exit();
}

$id_campana = $_GET["id"] ?? null;
$id_centro  = $_GET["cve"] ?? null;

if (!$id_campana || !$id_centro) {
    echo "Faltan parámetros.";
    exit();
}

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

$consulta = "
    SELECT 
        u.id_usuario,
        u.nombre,
        u.apellidos,
        v.especialidad
    FROM veterinario v
    JOIN usuario u ON v.id_veterinario = u.id_usuario
    WHERE v.id_centro = '$id_centro'
    AND NOT EXISTS (
        SELECT 1
        FROM participacion p
        WHERE p.id_veterinario = v.id_veterinario
          AND p.id_campana = '$id_campana'
    )
";

$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Añadir participantes</title>

    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../BD2imo/estilo/estilo_contenido.css">
</head>

<body>

<div style="display:flex; flex-direction:row;">

    <?php include("../../BD2imo/panel_ayuntamiento/panel_opciones.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">
                Añadir participantes del centro veterinario asociado
            </h2>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Especialidad</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
<?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
<?php while ($vet = mysqli_fetch_assoc($resultado)): 
    
        $id_veterinario = $vet["id_usuario"];    
?>
                    <tr>
                        <td><?php echo htmlspecialchars($vet["id_usuario"]); ?></td>
                        <td><?php echo htmlspecialchars($vet["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($vet["apellidos"]); ?></td>
                        <td><?php echo htmlspecialchars($vet["especialidad"]); ?></td>
                        <td>
                            <button class="boton-mini"
                                onclick="location.href='confirmacion_anadir_participantes.php?id=<?php echo urlencode($id_campana); ?>&vet=<?php echo urlencode($id_veterinario); ?>'">
                                Añadir
                            </button>
                        </td>
                    </tr>
<?php endwhile; ?>
<?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:20px;">
                            No hay veterinarios disponibles para añadir a la campaña o todos los que hay ya están ocupados en otra campaña.
                        </td>
                    </tr>
<?php endif; ?>
                </tbody>
            </table>

            <div style="margin-top:20px; text-align:center;">
                <button class="boton-gestion"
                        onclick="location.href='../../BD2imo/panel_ayuntamiento/gestionar_campanas.php'">
                    Volver atrás
                </button>
            </div>

        </div>
    </div>
</div>

</body>
</html>

<?php
mysqli_close($conexion);
?>