<?php
session_start();

// Seguridad: Verificar sesión del ayuntamiento
if (!isset($_SESSION["id_ayuntamiento"])) {
    header("Location: ../login_registro/login.php"); 
    exit();
}

// Recuperar datos de sesión
$id_ayuntamiento = $_SESSION["id_ayuntamiento"];
$nombre_municipio = $_SESSION["nombre_municipio"] ?? "su municipio";
$id_campana = $_GET['id'] ?? '';

// Variables por defecto
$datos_campana = [];
$nombre_centro = "Desconocido";
$id_centro = "";
$datos_colonia = ['id_colonia' => '-', 'nombre_colonia' => '-', 'coordenadas_GPS' => '-', 'num_gatos' => 0];
$intervenciones = [];
$veterinarios = [];
$nombre_responsable_grupo = ""; 
$id_responsable = "";

// 1. Conexión (vamos a hacer múltiples consultas en este apartado)
$conexion = mysqli_connect("localhost", "root", ""); 
$db = mysqli_select_db($conexion, "BD2XAMPPions"); 

if (!empty($id_campana)) {
    
    // A. Obtener datos de la campaña
    $q_campana = "SELECT c.*, tc.tipo
                   FROM campana AS c
                   JOIN tipo_campana AS tc ON c.id_tipo_campana = tc.id_tipo_campana
                   AND c.id_campana = '$id_campana'";
    $res_campana = mysqli_query($conexion, $q_campana);
    
    if ($row = mysqli_fetch_array($res_campana)) {
        $datos_campana = $row;
        
        if (empty($datos_campana['tipoVacunacion'])) {
            $datos_campana['tipoVacunacion'] = "-";
        }        
        $id_centro_fk = $row['id_centro_veterinario'];
        $id_colonia_fk = $row['id_colonia'];
        $id_responsable_fk = $row['id_responsable'];
    } else {
        header("Location: ../gestionar_campanas.php");
        exit();
    }
    
    // B. Datos del responsable que inició la campaña
    if (!empty($id_responsable_fk)) {
        $q_resp = "
            SELECT u.nombre, u.apellidos, g.nombre AS nombre_grupo
            FROM voluntario v
            JOIN usuario u ON v.id_voluntario = u.id_usuario
            LEFT JOIN grupo_control_felino g ON v.id_grupo = g.id_grupo
            WHERE v.id_voluntario = '$id_responsable_fk'
        ";
        $res_resp = mysqli_query($conexion, $q_resp);

        if ($row_resp = mysqli_fetch_array($res_resp)) {
            $nombre_resp = htmlspecialchars($row_resp['nombre'] . ' ' . $row_resp['apellidos']);
            $nombre_grupo = htmlspecialchars($row_resp['nombre_grupo'] ?? 'Sin Grupo');
            $nombre_responsable_grupo = "$nombre_resp ($nombre_grupo)"; 
        }
    }


    // C. Obtener el nombre del centro veterinario
    if (!empty($id_centro_fk)) {
        $q_centro = "SELECT nombre FROM centro_veterinario WHERE id_centro = '$id_centro_fk'";
        $res_centro = mysqli_query($conexion, $q_centro);
        if ($row_c = mysqli_fetch_array($res_centro)) {
            $nombre_centro = $row_c['nombre'];
            $id_centro = $id_centro_fk;
        }
    }

    // D. Obtener datos de la colonia
    if (!empty($id_colonia_fk)) {
        $q_col = "SELECT id_colonia, nombre_colonia, coordenadas_GPS FROM colonia WHERE id_colonia = '$id_colonia_fk'";
        $res_col = mysqli_query($conexion, $q_col);
        if ($row_col = mysqli_fetch_array($res_col)) {
            $datos_colonia['id_colonia'] = $id_colonia_fk;
            $datos_colonia['nombre_colonia'] = $row_col['nombre_colonia'];
            $datos_colonia['coordenadas_GPS'] = $row_col['coordenadas_GPS'];
        }
    }

    // E. Contar el número de gatos intervenidos en esa campaña de la colonia
    // Nota: se usa DISTINCT para que múltiples intervenciones sobre el mismo gato cuenten como sólo 1
    $q_count = "SELECT COUNT(DISTINCT id_gato) as total FROM intervencion_veterinaria WHERE id_campana = '$id_campana'";
    $res_count = mysqli_query($conexion, $q_count);
    if ($row_count = mysqli_fetch_array($res_count)) {
        $datos_colonia['num_gatos'] = $row_count['total'];
    }

    // F. Listado de intervenciones realizadas en esta campaña
    $q_int = "
        SELECT id_intervencion, fecha, comentario, id_gato 
        FROM intervencion_veterinaria 
        WHERE id_campana = '$id_campana'
    ";
    $res_int = mysqli_query($conexion, $q_int);
    while ($row_i = mysqli_fetch_array($res_int)) {
        $intervenciones[] = $row_i;
    }

    // G. Listado de veterinarios involucrados en la campaña
    $q_vet = "
    SELECT 
        u.id_usuario,
        u.nombre,
        u.apellidos,
        v.especialidad
    FROM usuario u
    JOIN veterinario v ON v.id_veterinario = u.id_usuario
    JOIN participacion p ON v.id_veterinario = p.id_veterinario
    WHERE p.id_campana = '$id_campana'
    ";
    $res_vet = mysqli_query($conexion, $q_vet);
    while ($row_v = mysqli_fetch_array($res_vet)) {
        $veterinarios[] = $row_v;
    }


} else {
    header("Location: ../gestionar_campanas.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Detalles Campaña</title>
    <link rel="stylesheet" href="../../estilo/estilo_panel.css">
    <link rel="stylesheet" href="../../estilo/estilo_contenido.css">
    
    <style>
        .dato-lectura {
            width: 100%;
            padding: 12px;
            margin-bottom: 5px;
            border-radius: 12px;
            border: 1px solid #ccc;
            font-size: 17px;
            background-color: #f4f4f4; 
            color: #555;
            box-sizing: border-box;
        }
        .etiqueta-dato {
            display: block;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
            font-size: 15px;
            color: #333;
        }
        .seccion-titulo {
            font-size: 22px;
            font-weight: bold;
            margin-top: 40px;
            margin-bottom: 20px;
            color: #111;
            text-align: left;
            padding-bottom: 10px;
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

            <h2 class="titulo-dashboard">Detalles de la campaña #<?php echo htmlspecialchars($id_campana); ?></h2>

            <!-- Parte 1 de la interfaz: datos de la campaña-->
            <div class="formulario-colonia">
                
                <label class="etiqueta-dato">Nombre de la campaña</label>
                <div class="dato-lectura"><?php echo htmlspecialchars($datos_campana['nombre']); ?></div>

                <label class="etiqueta-dato">Tipo de campaña</label>
                <div class="dato-lectura"><?php echo htmlspecialchars($datos_campana['tipo']); ?></div>

                <label class="etiqueta-dato">Centro veterinario que ejecuta la campaña</label>
                <div class="dato-lectura"><?php echo htmlspecialchars($nombre_centro); ?></div>

                <div style="display: flex, gap: 20px;">
                    <div style="flex: 1;">
                        <span class="etiqueta-dato">Fecha de inicio</span>
                        <div class="dato-lectura"><?php echo htmlspecialchars($datos_campana['fechaInicio']); ?></div>
                    </div>
                    <div style="flex: 1;">
                        <span class="etiqueta-dato">Fecha de fin</span>
                        <div class="dato-lectura"><?php echo htmlspecialchars($datos_campana['fechaFin']); ?></div>
                    </div>
                </div>

                <label class="etiqueta-dato">Tipo de vacunación</label>
                <div class="dato-lectura"><?php echo htmlspecialchars($datos_campana['tipoVacunacion']); ?></div>

                <label class="etiqueta-dato">Responsable que ha iniciado la campaña</label>
                <div class="dato-lectura"><?php echo htmlspecialchars($nombre_responsable_grupo); ?></div>
            </div>

            <!-- PARTE 2: Tabla informativa de la colonia -->
            <h3 class="seccion-titulo">Colonia sobre la que se hace la campaña</h3>
            
            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID Colonia</th>
                        <th>Nombre de la colonia</th>
                        <th>Coordenadas GPS</th>
                        <th>Número de gatos intervenidos</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($datos_colonia['id_colonia']); ?></td>
                        <td><?php echo htmlspecialchars($datos_colonia['nombre_colonia']); ?></td>
                        <td><?php echo htmlspecialchars($datos_colonia['coordenadas_GPS']); ?></td>
                        <td style="text-align: center; font-weight: bold;"><?php echo htmlspecialchars($datos_colonia['num_gatos']); ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- PARTE 3: Tabla de intervenciones de la campaña -->
            <h3 class="seccion-titulo">Intervenciones realizadas</h3>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID Intervención</th>
                        <th>Fecha</th>
                        <th>Comentario</th>
                        <th>ID Gato</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($intervenciones) > 0): ?>
                        <?php foreach ($intervenciones as $int): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($int['id_intervencion']); ?></td>
                            <td><?php echo htmlspecialchars($int['fecha']); ?></td>
                            <td><?php echo htmlspecialchars($int['comentario']); ?></td>
                            <td><?php echo htmlspecialchars($int['id_gato']); ?></td>
                            <td>
                                <div class="acciones-container">                                    
                                    <!-- Ver veterinarios implicados en la intervención -->
                                    <button class="boton-mini" 
                                            onclick="location.href='lista_veterinarios.php?id_intervencion=<?php echo urlencode($int['id_intervencion']); ?>&id_campana=<?php echo urlencode($id_campana); ?>'">
                                        Veterinarios implicados
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding: 20px;">No hay intervenciones registradas en esta campaña.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- PARTE 4: Tabla informativa sobre los veterinarios involucrados -->
            <h3 class="seccion-titulo">Veterinarios que han participado</h3>

            <table class="tabla-colonias">
                <thead>
                    <tr>
                        <th>ID Veterinario</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Especialidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($veterinarios) > 0): ?>
                        <?php foreach ($veterinarios as $vet): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($vet['id_usuario']); ?></td>
                            <td><?php echo htmlspecialchars($vet['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($vet['apellidos']); ?></td>
                            <td><?php echo htmlspecialchars($vet['especialidad']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding: 10px;">No hay veterinarios que participen en esta campaña.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>


            <!-- Volver una pantalla atrás -->
            <button class="boton-gestion boton-confirmar" onclick="location.href='../gestionar_campanas.php'">
                Volver al listado de campañas
            </button>

        </div>
    </div>
</div>

<?php mysqli_close($conexion); ?>

</body>
</html>
