<?php
// Iniciar sesiión
session_start();

// Seguridad: Verificar sesión
if (!isset($_SESSION["id_ayuntamiento"])) {
    header("Location: ../../login_registro/login.php"); 
    exit();
}

// Recuperar datos
$id_ayuntamiento = $_SESSION["id_ayuntamiento"];
$id_grupo = $_GET['id'] ?? '';
$nombre_grupo = "Grupo desconocido";

// Variables para los datos
$responsable_actual = null;
$candidatos = []; // Lista de voluntarios que NO son responsables

// 1. Conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// 2. Validar Grupo y Obtener Nombre
if (!empty($id_grupo)) {
    $sql_grupo = "SELECT nombre FROM grupo_control_felino WHERE id_grupo = '$id_grupo' AND id_ayuntamiento = '$id_ayuntamiento'";
    $res_grupo = mysqli_query($conexion, $sql_grupo);
    if ($fila = mysqli_fetch_array($res_grupo)) {
        $nombre_grupo = $fila['nombre'];
    } else {
        header("Location: ../gestionar_grupos.php"); // Grupo no válido
        exit();
    }
} else {
    header("Location: ../gestionar_grupos.php");
    exit();
}

// 3. Obtener el responsable actual del grupo
// Buscamos en voluntario -> usuario -> puede_hacer -> privilegios (donde privilegiosResponsable = 1)
$sql_resp = "
    SELECT u.id_usuario, u.nombre, u.apellidos
    FROM voluntario v
    JOIN usuario u ON v.id_voluntario = u.id_usuario
    JOIN puede_hacer ph ON u.id_usuario = ph.id_usuario
    JOIN privilegios p ON ph.id_privilegios = p.id_privilegios
    WHERE v.id_grupo = '$id_grupo' 
      AND p.privilegiosResponsable = 1
    LIMIT 1
";
$res_resp = mysqli_query($conexion, $sql_resp);
if ($fila = mysqli_fetch_array($res_resp)) {
    $responsable_actual = $fila;
}

// 4. Obtener candidatos (Voluntarios del grupo que NO son responsables)
$sql_candidatos = "
    SELECT u.id_usuario, u.nombre, u.apellidos
    FROM voluntario v
    JOIN usuario u ON v.id_voluntario = u.id_usuario
    WHERE v.id_grupo = '$id_grupo'
      AND u.id_usuario NOT IN (
          SELECT ph.id_usuario 
          FROM puede_hacer ph
          JOIN privilegios p ON ph.id_privilegios = p.id_privilegios
          WHERE p.privilegiosResponsable = 1
      )
";
$res_candidatos = mysqli_query($conexion, $sql_candidatos);
while ($fila = mysqli_fetch_array($res_candidatos)) {
    $candidatos[] = $fila;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cambiar Responsable</title>

    <link rel="stylesheet" href="../../estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../estilo/estilo_contenido.css">
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../panel_opciones.php"); ?>

    <!-- Contenido -->
    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Cambiar responsable del grupo <?php echo htmlspecialchars($id_grupo); ?></h2>
            <!-- Sección 1: Responsable actual -->
            <div class="subtitulo-gestion">Responsable actual</div>
            
            <table class="tabla-colonias" style="margin-bottom: 40px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($responsable_actual): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($responsable_actual['id_usuario']); ?></td>
                            <td><?php echo htmlspecialchars($responsable_actual['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($responsable_actual['apellidos']); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align:center;">Este grupo no tiene responsable asignado actualmente.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>


            <!-- Sección 2: Seleccionar nuevo responsable -->
            <div class="subtitulo-gestion">Seleccionar nuevo responsable</div>

            <form action="cambio.php" method="POST" style="max-width: 500px; margin: 0 auto;">
                
                <!-- ID Grupo Oculto -->
                <input type="hidden" name="id_grupo" value="<?php echo htmlspecialchars($id_grupo); ?>">
                <!-- ID Responsable Actual Oculto (para quitarle permisos luego) -->
                <input type="hidden" name="id_resp_actual" value="<?php echo $responsable_actual ? $responsable_actual['id_usuario'] : ''; ?>">

                <!-- Select desplegable -->
                <select name="nuevo_responsable" id="nuevo_responsable" class="select-campo" required>
                    <option value="">Selecciona un voluntario</option>
                    <?php foreach ($candidatos as $candidato): ?>
                        <option value="<?php echo htmlspecialchars($candidato['id_usuario']); ?>">
                            <?php echo htmlspecialchars($candidato['nombre'] . " " . $candidato['apellidos']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Botón Confirmar -->
                <button type="submit" class="boton-gestion boton-confirmar">Confirmar cambio</button>

            </form>

            <!-- Botón Cancelar -->
            <div style="max-width: 500px; margin: 20px auto 0;">
                <button type="button" 
                        class="boton-gestion" 
                        style="background-color: #555;" 
                        onclick="location.href='../gestionar_grupos.php'">
                    Cancelar cambio
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