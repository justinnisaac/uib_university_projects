<?php
// Iniciar la sesión
session_start();

// Seguridad: Si no hay ID de ayuntamiento en la sesión, el acceso es inválido.
if (!isset($_SESSION["id_ayuntamiento"])) {
    header("Location: ../login_registro/login.php"); 
    exit();
}

// Recuperamos los datos de la sesión
$usuario = $_SESSION["usuario"] ?? "";
$id_ayuntamiento = $_SESSION["id_ayuntamiento"];
$nombre_ciudad = $_SESSION["nombre_municipio"] ?? "su municipio";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestión de Grupos</title>

    <link rel="stylesheet" href="../estilo/estilo_panel.css">
    <link rel="stylesheet" href="../estilo/estilo_contenido.css">
    
    <script>
        function mostrarAlerta() {
            var alerta = document.getElementById("alerta-voluntarios");
            if (alerta.style.display === "none") {
                alerta.style.display = "block";
            } else {
                alerta.style.display = "none";
            }
        }
    </script>
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("panel_opciones.php"); ?>

    <!-- Contenido -->
    <div class="zona-contenido">

        <div class="contenedor-difuminado">
            <h2 class="titulo-dashboard">Gestión de grupos de trabajo de <?php echo htmlspecialchars($nombre_ciudad); ?></h2>
            
<?php
// 1. Establecer la conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// Lógica previa: Contar voluntarios sin grupo asignado
$consulta_libres = "
    SELECT COUNT(v.id_voluntario) as total
    FROM voluntario v
    JOIN borsin_voluntarios b ON v.id_borsin = b.id_borsin
    AND b.id_ayuntamiento = '$id_ayuntamiento' 
    AND v.id_grupo IS NULL
";
$res_libres = mysqli_query($conexion, $consulta_libres);
$num_libres = 0;
if ($fila = mysqli_fetch_array($res_libres)) {
    $num_libres = $fila['total'];
}

// Botón "Crear nuevo grupo"
// Si hay voluntarios libres, el botón lleva al formulario.
// Si no, muestra un mensaje de alerta.
?>

            <!-- Espacio para el mensaje de alerta (oculto por defecto) -->
            <div id="alerta-voluntarios" style="display: none; margin-bottom: 20px;">
                <p class="mensaje-alerta mensaje-error">
                    Para crear un nuevo grupo necesitas un voluntario sin grupo asignado. Actualmente hay <?php echo $num_libres; ?>.
                </p>
            </div>

            <?php if ($num_libres > 0): ?>
                <!-- Caso: Hay voluntarios -> Botón funcional -->
                <button class="boton-gestion boton-crear" onclick="location.href='opciones_gest_grupos/crear_grupo.php'">Crear nuevo grupo</button>
            <?php else: ?>
                <!-- Caso: No hay voluntarios -> Botón que activa la alerta -->
                <button class="boton-gestion boton-crear" onclick="mostrarAlerta()">Crear nuevo grupo</button>
            <?php endif; ?>


<?php
// 2. Definir la consulta SELECT con COUNT para la tabla
$consulta = "
    SELECT g.id_grupo, 
           g.nombre, 
           COUNT(v.id_voluntario) as cantidad_miembros
    FROM grupo_control_felino g
    LEFT JOIN voluntario v ON g.id_grupo = v.id_grupo
    WHERE g.id_ayuntamiento = '$id_ayuntamiento'
    GROUP BY g.id_grupo, g.nombre
"; 

// 3. Ejecutar la consulta
$resultado = mysqli_query($conexion, $consulta);
?>
            <!-- Reutilizamos la clase .tabla-colonias -->
            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID Grupo</th>
                        <th>Nombre del Grupo</th>
                        <th>Cantidad Miembros</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
<?php
// 4. Recorrer los resultados
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while($registro = mysqli_fetch_array($resultado)) {
        $id_grupo = $registro["id_grupo"];
        $cantidad = $registro["cantidad_miembros"];
?>
                    <tr>
                        <td><?php echo htmlspecialchars($id_grupo); ?></td>
                        <td><?php echo htmlspecialchars($registro["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($cantidad); ?></td>
                        <td>
                            <div class="acciones-container">
                                
                                <?php 
                                // Botón 1: Ver miembros (Sólo si hay al menos 1 miembro)
                                if ($cantidad >= 1) { 
                                ?>
                                    <button class="boton-mini" onclick="location.href='opciones_gest_grupos/ver_todos_miembros_grupo.php?id=<?php echo urlencode($id_grupo); ?>'">Ver miembros</button>
                                <?php 
                                } 
                                ?>

                                <?php 
                                // Botón 2: Cambiar responsable (Sólo si hay al menos 2 miembros)
                                if ($cantidad >= 2) { 
                                ?>
                                    <button class="boton-mini" onclick="location.href='opciones_gest_grupos/cambiar_responsable.php?id=<?php echo urlencode($id_grupo); ?>'">Cambiar responsable</button>
                                <?php 
                                } 
                                ?>
                            </div>
                        </td>
                    </tr>
<?php
    } 
} else {
    // Mensaje si no hay grupos creados
    echo "<tr><td colspan='4' style='text-align:center; padding:20px;'>No hay grupos de trabajo registrados para este ayuntamiento.</td></tr>";
}
?>
                </tbody>
            </table>

<?php
// 5. Cerrar la conexión
mysqli_close($conexion); 
?>

        </div>
    </div>
</div>
</body>
</html>