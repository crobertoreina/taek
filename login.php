<?php
// Iniciar sesión
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Verificar si el usuario está autenticado
if(isset($_SESSION['user_id'])) {
	
	if( $_SESSION['user_level']===1 )
	{
		echo $_SESSION['user_level'];
		// Si ya está autenticado, redirigir a la página principal
		header("Location: index.php");
		exit();
	}
	else
	{
		// Si ya está autenticado, redirigir a la página principal
		header("Location: indexJuez.php");
		exit();
	}
}

if(isset($_SESSION['escuela_id'])) {
    header("Location: escuela_dashboard.php");
    exit();
}

// Incluir el archivo de conexión
include('conexion.php'); // Asegúrate de que 'conexion.php' esté correctamente incluído

// Verificar si se ha recibido el nombre de usuario y la contraseña desde el formulario
if (isset($_POST['username']) && isset($_POST['password'])) {
    // Obtener los datos de la solicitud
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Consultar la base de datos con MySQLi (Usar consultas preparadas para evitar SQL Injection)
    $query = "SELECT * FROM jueces WHERE user = ?"; 
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $username);  // "s" es para indicar que es un string
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si el usuario existe como juez
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verificar si la contraseña coincide (suponiendo que las contraseñas están en texto plano)
        if ($password === $row['pass']) {
            $_SESSION['user_id']    = $row['user'];
            $_SESSION['juez_id']    = $row['id'];
			$_SESSION['user_level'] = $row['level'];
			if( $_SESSION['user_level'] === 1 )
			{
				header("Location: index.php");  // Redirigir a la página principal
				exit();
			}
			else			
			{
				header("Location: indexJuez.php");  // Redirigir a la página principal
				exit();
			}
        } else {
            $error_message = "Usuario o contraseña incorrectos.";
        }
    } else {
        // Verificar si es una escuela (login con correo)
        $q2 = $conexion->prepare("SELECT * FROM escuelas WHERE correo = ?");
        $q2->bind_param("s", $username);
        $q2->execute();
        $r2 = $q2->get_result();
        if ($r2->num_rows > 0) {
            $row = $r2->fetch_assoc();
            if (intval($row['estado']) !== 1) {
                $error_message = "Tu cuenta no ha sido confirmada. Revisa tu correo y haz clic en el enlace de confirmación.";
            } elseif ($password === $row['pass']) {
                $_SESSION['escuela_id'] = $row['id'];
                $_SESSION['escuela_nombre'] = $row['nombre'];
                header("Location: escuela_dashboard.php");
                exit();
            } else {
                $error_message = "Usuario o contraseña incorrectos.";
            }
        } else {
            $error_message = "Usuario no encontrado.";
        }
        $q2->close();
    }

    $stmt->close();
}

// Cerrar la conexión
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="manifest" href="manifest.json">
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js');
    }
    </script>

    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background: linear-gradient(135deg, #f5f5dc 0%, #e8f5e9 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; }
        .login-wrapper { width:100%; max-width:400px; padding:20px; }
        .login-header { background:linear-gradient(135deg,#2e7d32,#4caf50); border-radius:18px 18px 0 0; padding:28px 24px 20px; text-align:center; }
        .login-header img { width:72px; height:72px; border-radius:50%; border:3px solid rgba(255,255,255,0.3); margin-bottom:10px; }
        .login-header h1 { color:#fff; font-size:20px; font-weight:700; letter-spacing:0.5px; text-shadow:none; }
        .login-header h1 span { color:#ffeb3b; }
        .login-header p { color:rgba(255,255,255,0.8); font-size:12px; letter-spacing:2px; text-transform:uppercase; margin-top:4px; font-weight:500; }
        .login-card { background:#fff; border-radius:0 0 18px 18px; padding:28px 26px 32px; box-shadow:0 8px 30px rgba(0,0,0,0.08); }
        .login-card h2 { color:#333; font-size:17px; font-weight:600; text-align:center; margin-bottom:22px; letter-spacing:0.3px; }
        .login-card .field { margin-bottom:18px; }
        .login-card label { display:block; font-size:11px; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:1px; margin-bottom:5px; }
        .login-card input { width:100%; padding:13px 14px; border:1.5px solid #e0e0e0; border-radius:10px; font-size:15px; background:#fafafa; transition:all 0.15s; }
        .login-card input:focus { border-color:#4caf50; background:#fff; outline:none; box-shadow:0 0 0 3px rgba(76,175,80,0.1); }
        .login-card input::placeholder { color:#bbb; }
        .login-card input[type="submit"] { width:100%; padding:14px; background:linear-gradient(135deg,#2e7d32,#4caf50); color:#fff; border:none; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer; letter-spacing:1px; margin-top:4px; box-shadow:0 4px 12px rgba(76,175,80,0.25); transition:all 0.15s; }
        .login-card input[type="submit"]:hover { transform:translateY(-1px); box-shadow:0 6px 18px rgba(76,175,80,0.3); }
        .login-card input[type="submit"]:active { transform:translateY(0); }
        .error-msg { text-align:center; margin-top:14px; padding:10px 14px; background:#ffebee; color:#c62828; border-radius:10px; font-size:13px; font-weight:500; }
        .login-footer { text-align:center; margin-top:18px; }
        .login-footer a { color:#4caf50; font-size:13px; font-weight:600; text-decoration:none; transition:opacity 0.15s; }
        .login-footer a:hover { opacity:0.8; }
        .login-copy { text-align:center; margin-top:16px; color:#bbb; font-size:11px; letter-spacing:0.5px; }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-header">
            <img src="images/sonbae.jpg" alt="Logo" />
            <h1>태권도 <span>Poomsae</span></h1>
            <p>Sistema de Evaluaci&oacute;n</p>
        </div>
        <div class="login-card">
            <h2>Iniciar Sesi&oacute;n</h2>
            <form method="POST" action="login.php" data-ajax="false">
                <div class="field">
                    <label for="username">Usuario / Correo</label>
                    <input type="text" name="username" id="username" placeholder="Usuario o correo electrónico" required />
                </div>
                <div class="field">
                    <label for="password">Contrase&ntilde;a</label>
                    <input name="password" type="password" id="password" placeholder="Tu contrase&ntilde;a" maxlength="10" required />
                </div>
                <input name="Submit" type="submit" value="Ingresar" />
            </form>
            <?php
            if (isset($error_message)) {
                echo "<div class='error-msg'>$error_message</div>";
            }
            ?>
        </div>
        <div class="login-footer" style="display:flex;flex-direction:column;gap:8px;align-items:center;">
            <a href="registro_escuela.php" style="display:inline-block;padding:10px 24px;background:linear-gradient(135deg,#2e7d32,#4caf50);color:#fff;border-radius:12px;text-decoration:none;font-weight:600;font-size:13px;box-shadow:0 2px 8px rgba(76,175,80,0.2);">🏫 Registrar Dojang</a>
        </div>
        <div class="login-copy">&copy; CRR 2025</div>
    </div>
</body>
</html>
