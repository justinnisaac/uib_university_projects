<?php
// Iniciar sesión
session_start();

// Seguridad: Verificar sesión del ayuntamiento
if (!isset($_SESSION["id_ayuntamiento"])) {
    header("Location: ../login_registro/login.php"); 
    exit();
}

$id_ayuntamiento = $_SESSION["id_ayuntamiento"];
$usuario = $_SESSION["usuario"] ?? "";

// 1. Conexión
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

// 2. Obtener lista de voluntarios LIBRES (sin grupo) de este ayuntamiento
$consulta_libres = "
    SELECT v.id_voluntario, u.nombre, u.apellidos
    FROM voluntario v
    JOIN usuario u ON v.id_voluntario = u.id_usuario
    JOIN borsin_voluntarios b ON v.id_borsin = b.id_borsin
    WHERE b.id_ayuntamiento = '$id_ayuntamiento' 
      AND v.id_grupo IS NULL
";
$resultado_libres = mysqli_query($conexion, $consulta_libres);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Crear Grupo</title>
    <link rel="stylesheet" href="../../estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../estilo/estilo_contenido.css">
    
    <!-- Estilo para el select -->
    <style>
        .select-estilo {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 12px;
            border: 1px solid #ccc;
            font-size: 17px;
            background-color: white;
            color: #333;
        }
    </style>
</head>

<body>

<div style="display: flex; flex-direction: row;">

    <!-- Panel lateral -->
    <?php include("../panel_opciones.php"); ?>

    <!-- Contenido -->
    <div class="zona-contenido">

        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Crear nuevo grupo de trabajo</h2>

            <!-- Formulario -->
            <form action="procesar_creacion_grupo.php" method="POST" class="formulario-colonia">
                
                <!-- NOMBRE DEL GRUPO -->
                <label for="nombre_grupo">Nombre del grupo:</label>
                <input type="text" id="nombre_grupo" name="nombre_grupo" maxlength="100" 
                       placeholder="Ej: Grupo de limpieza de casetas gatunas" required>
                
                <!-- Selección del primer voluntario -->
                <label for="id_primer_miembro">Asignar primer voluntario (será el responsable):</label>
                <select name="id_primer_miembro" id="id_primer_miembro" class="select-estilo" required>
                    <option value="">Selecciona un voluntario disponible en la bolsa</option>
                    <?php
                    if ($resultado_libres && mysqli_num_rows($resultado_libres) > 0) {
                        while ($vol = mysqli_fetch_array($resultado_libres)) {
                            $id = htmlspecialchars($vol['id_voluntario']);
                            $nombre_completo = htmlspecialchars($vol['nombre'] . " " . $vol['apellidos']);
                            
                            echo "<option value='$id'>ID: $id, Nombre: $nombre_completo</option>";
                        }
                    }
                    ?>
                </select>
                
                <!-- Botón Confirmar -->
                <button type="submit" class="boton-gestion boton-confirmar">Confirmar creación</button>

            </form>
            
            <!-- Botón Cancelar -->
            <div style="max-width: 500px; margin: 15px auto 0;">
                <button type="button" 
                        class="boton-gestion" 
                        style="background-color: #555;" 
                        onclick="location.href='../gestionar_grupos.php'">
                    Cancelar
                </button>
            </div>

        </div>
    </div>
</div>

<?php
mysqli_close($conexion);
?>

</body>
</html>