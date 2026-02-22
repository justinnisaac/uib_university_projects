<?php
session_start();

// 1. Verificamos si el usuario ha iniciado sesión
if (!isset($_SESSION["usuario"])) {
    header("Location: ../login_registro/login.html"); 
    exit();
}

// Recuperamos los datos esenciales de la sesión
$usuario = $_SESSION["usuario"];
$id_usuario = $_SESSION["id_usuario"]; 

// Variables de contexto
$municipio = "su municipio";
$id_ayuntamiento = ""; 
$num_colonias = 0; 
$num_grupos = 0;  
$num_campanas_activas = 0; // no contamos las que terminaron 

// Conectar a MySQL
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions");

// Solo ejecutamos la consulta si NO tenemos el id_ayuntamiento en la sesión. Llamaré a esto
// tener "contexto" o no.
if (!isset($_SESSION["id_ayuntamiento"])) {
    
    // Consulta para obtener el id_ayuntamiento y el nombre del municipio del personal_administrativo
    $consultaAyto = "
        SELECT t.id_ayuntamiento, m.nombre AS nombre_municipio
        FROM personal_administrativo t
        JOIN ayuntamiento a ON t.id_ayuntamiento = a.id_ayuntamiento
        JOIN municipio m ON a.id_municipio = m.id_municipio
        AND t.id_personal_administrativo = '$id_usuario'
    ";
    
    $resAyto = mysqli_query($conexion, $consultaAyto);

    if ($filaAyto = mysqli_fetch_array($resAyto)) {
        // Guardamos los datos obtenidos en VARIABLES DE SESIÓN para poder reutilizar desde otras páginas
        // sin recurrir a más consultas.
        $_SESSION['id_ayuntamiento'] = $filaAyto['id_ayuntamiento'];
        $_SESSION['nombre_municipio'] = $filaAyto['nombre_municipio'];
        
        $id_ayuntamiento = $filaAyto['id_ayuntamiento'];
        $municipio = $filaAyto['nombre_municipio'];
    } else {

        // Gestión de error: El usuario no está asociado a ningún ayuntamiento
        session_unset();
        session_destroy();
        header("Location: ../login_registro/login.html?error=no_ayuntamiento");
        exit();
    }
} else {
    // Si la sesión YA contiene contexto, lo recuperamos directamente.
    $id_ayuntamiento = $_SESSION['id_ayuntamiento'];
    $municipio = $_SESSION['nombre_municipio'];
}

// Parte de mostrar estadísiticas
if (!empty($id_ayuntamiento)) {
    
    // 1. Contar Colonias controladas por el ayuntamiento
    $consulta_conteo_col = "
        SELECT COUNT(id_colonia) AS total_colonias
        FROM colonia
        WHERE id_ayuntamiento = '$id_ayuntamiento'
    ";
    $res_conteo_col = mysqli_query($conexion, $consulta_conteo_col);
    if ($fila_conteo = mysqli_fetch_array($res_conteo_col)) {
        $num_colonias = $fila_conteo['total_colonias'];
    }

    // 2. Contar Grupos de Trabajo asociados al ayuntamiento
    $consulta_conteo_grup = "
        SELECT COUNT(id_grupo) AS total_grupos
        FROM grupo_control_felino
        WHERE id_ayuntamiento = '$id_ayuntamiento'
    ";
    $res_conteo_grup = mysqli_query($conexion, $consulta_conteo_grup);
    if ($fila_conteo_grup = mysqli_fetch_array($res_conteo_grup)) {
        $num_grupos = $fila_conteo_grup['total_grupos'];
    }

    // 3. Contar el número de campañas activas
    $consulta_conteo_camp = "
        SELECT COUNT(c.id_campana) AS total_campanas
        FROM campana c
        JOIN colonia col ON c.id_colonia = col.id_colonia
        AND col.id_ayuntamiento = '$id_ayuntamiento'
        AND c.fechaFin IS NULL
    ";
    $res_conteo_camp = mysqli_query($conexion, $consulta_conteo_camp);
    if ($fila_conteo_camp = mysqli_fetch_array($res_conteo_camp)) {
        $num_campanas_activas = $fila_conteo_camp['total_campanas'];
    }

    // 4. Mostrar mi nombre en el panel de bienvenida, necesitaré dado 
    // mi id_usuario, obtener mi nombre completo.
    $consulta_obtener_mi_nombre = "
        SELECT nombre, apellidos
        FROM usuario
        WHERE id_usuario = '$id_usuario'
    ";
    $res_mi_nombre = mysqli_query($conexion, $consulta_obtener_mi_nombre);
    if ($fila_mi_nombre = mysqli_fetch_array($res_mi_nombre)) {
        $nombre_completo = $fila_mi_nombre['nombre'] . ' ' . $fila_mi_nombre['apellidos'];
    }
}

mysqli_close($conexion);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Panel Ayuntamiento</title>

    <link rel="stylesheet" href="../estilo/estilo_panel.css">
    <link rel="stylesheet" href="../estilo/estilo_contenido.css">
</head>

<body>
    <div style="display: flex; flex-direction: row;">
        <?php include("panel_opciones.php"); ?>
        <div class="zona-contenido">

            <div class="contenedor-difuminado">

                <!-- ICONO CONFIGURACIÓN -->
                <div style="display:flex; justify-content:flex-end;">
                    <img src="../../BD2imo/estilo/ajustes.jpg"
                        alt="Perfil"
                        title="Ver perfil"
                        style="width:30px; cursor:pointer;"
                        onclick="location.href='ver_perfil_trabajador.php'">
                </div>

                <h2 class="titulo-dashboard">Bienvenido, <?php echo htmlspecialchars($nombre_completo); ?></h2>
                <h2 class="titulo-dashboard">Resumen de control de población felina de <?php echo htmlspecialchars($municipio); ?></h2>
                <div class="grid-estadisticas">

                    <!-- Caja colonias -->
                    <div class="caja-estado">
                        <div class="numero-grande"><?php echo $num_colonias; ?></div>
                        <div class="texto-caja">Colonias felinas</div>
                        <button class="boton-gestion" onclick="location.href='gestionar_colonias.php'">Gestionar colonias</button>
                    </div>

                    <!-- Caja grupos -->
                    <div class="caja-estado">
                        <div class="numero-grande"><?php echo $num_grupos; ?></div>
                        <div class="texto-caja">Grupos de trabajo</div>
                        <button class="boton-gestion" onclick="location.href='gestionar_grupos.php'">Gestionar grupos de trabajo</button>
                    </div>

                    <!-- Caja campañas -->
                    <div class="caja-estado">
                        <div class="numero-grande"><?php echo $num_campanas_activas; ?></div>
                        <div class="texto-caja">Campañas activas</div>
                        <button class="boton-gestion" onclick="location.href='gestionar_campanas.php'">Gestionar campañas</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>