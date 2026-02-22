<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login_registro/login.php");
    exit();
}

$id_trabajador_ayuntamiento = $_SESSION["id_usuario"];

$conexion = mysqli_connect("localhost", "root", "");
mysqli_select_db($conexion, "BD2XAMPPions");

$consulta = "
    SELECT 
        u.nombre_usuario,
        u.nombre,
        u.apellidos,
        u.telefono,
        u.email,
        t.especialidad,
        a.nombre AS nombre_ayuntamiento
    FROM usuario u
    JOIN personal_administrativo t ON u.id_usuario = t.id_personal_administrativo
    JOIN ayuntamiento a ON t.id_ayuntamiento = a.id_ayuntamiento
    WHERE u.id_usuario = '$id_trabajador_ayuntamiento'
";

$resultado = mysqli_query($conexion, $consulta);

if (!$resultado || mysqli_num_rows($resultado) == 0) {
    mysqli_close($conexion);
    header("Location: principal_ayuntamiento.php");
    exit();
}

$datos = mysqli_fetch_assoc($resultado);
mysqli_close($conexion);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Visualizar mi perfil</title>

    <link rel="stylesheet" href="../estilo/estilo_panel.css">
    <link rel="stylesheet" href="../estilo/estilo_contenido.css">
    <style>

        
        .contenedor-compacto {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }
        
        .grupo-datos {
            margin-bottom: 10px;
        }
        
        .grupo-datos label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
            font-size: 14px;
        }
        
        .grupo-datos .valor-dato {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            min-height: 40px;
            display: flex;
            align-items: center;
        }
        
        @media (max-width: 768px) {
            .contenedor-compacto {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div style="display:flex; flex-direction:row;">

    <?php include("panel_opciones.php"); ?>

    <div class="zona-contenido">
        <div class="contenedor-difuminado">

            <h2 class="titulo-dashboard">Mis datos de personal administrativo</h2>

            <div class="contenedor-compacto">
                <div class="grupo-datos">
                    <label>Usuario</label>
                    <div class="valor-dato"><?php echo htmlspecialchars($datos["nombre_usuario"]); ?></div>
                </div>
                
                <div class="grupo-datos">
                    <label>Especialidad</label>
                    <div class="valor-dato"><?php echo htmlspecialchars($datos["especialidad"]); ?></div>
                </div>
                
                <div class="grupo-datos">
                    <label>Nombre</label>
                    <div class="valor-dato"><?php echo htmlspecialchars($datos["nombre"]); ?></div>
                </div>
                
                <div class="grupo-datos">
                    <label>Apellidos</label>
                    <div class="valor-dato"><?php echo htmlspecialchars($datos["apellidos"]); ?></div>
                </div>
                
                <div class="grupo-datos">
                    <label>Teléfono</label>
                    <div class="valor-dato"><?php echo htmlspecialchars($datos["telefono"]); ?></div>
                </div>
                
                <div class="grupo-datos" style="grid-column: span 2;">
                    <label>Email</label>
                    <div class="valor-dato"><?php echo htmlspecialchars($datos["email"]); ?></div>
                </div>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <button type="button"
                        class="boton-gestion boton-confirmar"
                        onclick="location.href='principal_ayuntamiento.php'">
                    Volver al panel principal
                </button>
            </div>

        </div>
    </div>
</div>

</body>
</html>