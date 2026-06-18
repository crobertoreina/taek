<?php
session_start();
include('conexion.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['pass'] ?? '';
    $nombre = trim($_POST['nombre'] ?? '');
    $siglas = trim($_POST['siglas'] ?? '');
    $fecha_fundacion = $_POST['fecha_fundacion'] ?? '';
    $pais = trim($_POST['pais'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $instructor_nombre = trim($_POST['instructor_nombre'] ?? '');
    $instructor_grado = trim($_POST['instructor_grado'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    if (!$pass || !$nombre) {
        $error = 'Contraseña y nombre son obligatorios.';
    } elseif (!$correo) {
        $error = 'El correo electrónico es obligatorio (será tu usuario para iniciar sesión).';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa un correo electrónico válido.';
    } else {
        $check = $conexion->prepare("SELECT id FROM escuelas WHERE correo = ?");
        $check->bind_param('s', $correo);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'El correo electrónico ya está registrado.';
        } else {
            $token = bin2hex(random_bytes(32));
            $token_expiracion = date('Y-m-d H:i:s', strtotime('+24 hours'));
            $stmt = $conexion->prepare("INSERT INTO escuelas (nombre, siglas, fecha_fundacion, pais, departamento, ciudad, direccion, instructor_nombre, instructor_grado, telefono, correo, user, pass, estado, token, token_expiracion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)");
            $stmt->bind_param('sssssssssssssss', $nombre, $siglas, $fecha_fundacion, $pais, $departamento, $ciudad, $direccion, $instructor_nombre, $instructor_grado, $telefono, $correo, $correo, $pass, $token, $token_expiracion);
            if ($stmt->execute()) {
                require_once __DIR__ . '/mail_config.php';
                if (enviarCorreoConfirmacion($correo, $nombre, $token)) {
                    $success = 'Dojang registrado correctamente. Revisa tu correo para confirmar tu cuenta.';
                } else {
                    $success = 'Dojang registrado. No se pudo enviar el correo de confirmación. Contacta al administrador.';
                }
            } else {
                $error = 'Error al registrar: ' . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}
$conexion->close();
            } else {
                $error = 'Error al registrar: ' . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Dojang</title>
    <link rel="stylesheet" href="themes/takwondoTheme.min.css" />
    <link rel="stylesheet" href="themes/jquery.mobile.icons.min.css" />
    <link rel="stylesheet" href="lib/jquery.mobile.structure-1.4.5.min.css" />
    <script src="lib/jquery-1.11.1.min.js"></script>
    <script src="lib/jquery.mobile-1.4.5.min.js"></script>
    <style>
        body { background: linear-gradient(135deg, #f5f5dc 0%, #e8f5e9 100%); }
        .reg-container { max-width: 520px; margin: 20px auto; background:#fff; border-radius:18px; padding:28px 24px; box-shadow:0 4px 24px rgba(0,0,0,0.08); border-top:4px solid #4caf50; }
        .reg-container h2 { text-align:center; color:#333; font-size:20px; margin-bottom:4px; }
        .reg-container .sub { text-align:center; color:#999; font-size:13px; margin-bottom:20px; }
        .reg-container .field { margin-bottom:14px; }
        .reg-container .field label { display:block; font-size:12px; font-weight:600; color:#555; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px; }
        .reg-container .field input, .reg-container .field select { width:100%; padding:11px 13px; border:1.5px solid #e0e0e0; border-radius:10px; font-size:14px; background:#fafafa; box-sizing:border-box; transition:border-color 0.15s; }
        .reg-container .field input:focus { border-color:#4caf50; outline:none; background:#fff; }
        .reg-container .field.half { display:inline-block; width:48%; }
        .reg-container .field.half + .field.half { margin-left:4%; }
        .reg-container input[type="submit"] { width:100%; padding:14px; background:linear-gradient(135deg,#2e7d32,#4caf50); color:#fff; border:none; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer; letter-spacing:1px; margin-top:8px; }
        .reg-container input[type="submit"]:hover { opacity:0.9; }
        .error { background:#ffebee; color:#c62828; padding:10px 14px; border-radius:10px; font-size:13px; margin-bottom:14px; }
        .success { background:#e8f5e9; color:#2e7d32; padding:10px 14px; border-radius:10px; font-size:13px; margin-bottom:14px; }
        .login-link { text-align:center; margin-top:16px; font-size:13px; color:#999; }
        .login-link a { color:#4caf50; font-weight:600; text-decoration:none; }
        .form-icon { text-align:center; font-size:40px; margin-bottom:6px; }
        .form-icon img { width:70px; height:70px; border-radius:50%; border:3px solid #ffeb3b; }
        .section-title { font-size:13px; font-weight:700; color:#2e7d32; text-transform:uppercase; letter-spacing:1px; margin:20px 0 12px; padding-bottom:4px; border-bottom:2px solid #e8f5e9; }
    </style>
</head>
<body>
    <div data-role="page">
        <div data-role="content" style="padding:10px;">
            <div class="reg-container">
                <div class="form-icon"><img src="images/sonbae.jpg" alt="Logo"></div>
                <h2>Registro de Dojang</h2>
                <p class="sub">Completa los datos para registrar tu Dojang</p>

                <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>

                <form method="POST">
                    <div class="section-title">Acceso al Sistema</div>
                    <div class="field">
                        <label>Correo Electrónico *</label>
                        <input type="email" name="correo" required placeholder="correo@ejemplo.com (será tu usuario)">
                    </div>
                    <div class="field">
                        <label>Contraseña *</label>
                        <input type="password" name="pass" required placeholder="Contraseña">
                    </div>

                    <div class="section-title">Datos del Dojang</div>
                    <div class="field">
                        <label>Nombre del Dojang *</label>
                        <input type="text" name="nombre" required placeholder="Nombre completo">
                    </div>
                    <div class="field half">
                        <label>Siglas</label>
                        <input type="text" name="siglas" placeholder="Ej. TKD-CR">
                    </div>
                    <div class="field half">
                        <label>Fecha de Fundación</label>
                        <input type="date" name="fecha_fundacion">
                    </div>

                    <div class="section-title">Ubicación</div>
                    <div class="field half">
                        <label>País</label>
                        <input type="text" name="pais" placeholder="País">
                    </div>
                    <div class="field half">
                        <label>Departamento</label>
                        <input type="text" name="departamento" placeholder="Departamento">
                    </div>
                    <div class="field half">
                        <label>Ciudad</label>
                        <input type="text" name="ciudad" placeholder="Ciudad">
                    </div>
                    <div class="field half">
                        <label>Dirección</label>
                        <input type="text" name="direccion" placeholder="Dirección">
                    </div>

                    <div class="section-title">Instructor</div>
                    <div class="field">
                        <label>Nombre del Instructor</label>
                        <input type="text" name="instructor_nombre" placeholder="Nombre completo">
                    </div>
                    <div class="field half">
                        <label>Grado (Dan)</label>
                        <select name="instructor_grado">
                            <option value="">-- Seleccionar --</option>
                            <option>1er Dan</option>
                            <option>2do Dan</option>
                            <option>3er Dan</option>
                            <option>4to Dan</option>
                            <option>5to Dan</option>
                            <option>6to Dan</option>
                            <option>7mo Dan</option>
                            <option>8vo Dan</option>
                            <option>9no Dan</option>
                        </select>
                    </div>
                    <div class="field half">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" placeholder="Teléfono de contacto">
                    </div>

                    <input type="submit" value="Registrar Dojang">
                </form>
                <div class="login-link">¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a></div>
            </div>
        </div>
    </div>
</body>
</html>
