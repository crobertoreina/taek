<?php
// Iniciar sesión
session_start();

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

    // Verificar si el usuario existe
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
        $error_message = "Usuario no encontrado.";
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
        body { background: linear-gradient(135deg, #f5f5dc 0%, #e8f5e9 100%); }
        #login-container {
            width: 100%;
            max-width: 350px;
            padding: 30px 24px;
            margin: 40px auto;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-top: 4px solid #4caf50;
        }
        #login-container h2 { color: #333; text-align: center; margin-bottom: 20px; font-size: 22px; }
        #login-container input[type="text"],
        #login-container input[type="password"] {
            width: 100%;
            padding: 14px;
            margin: 8px 0 16px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #fafafa;
            color: #333;
            font-size: 16px;
            box-sizing: border-box;
        }
        #login-container input::placeholder { color: #aaa; }
        #login-container input[type="submit"] {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4caf50, #388e3c);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        #login-container input[type="submit"]:active { transform: scale(0.97); }
        #error-message { text-align: center; margin-top: 12px; color: #e53935; font-size: 14px; }
        .login-logo { text-align: center; margin-bottom: 10px; }
        .login-logo img { width: 80px; height: 80px; border-radius: 50%; margin-bottom: 8px; border: 3px solid #ffeb3b; }
        .login-logo h1 { color: #333; font-size: 24px; margin: 0; }
        .login-logo h1 span { color: #4caf50; }
        .login-logo p { color: #999; font-size: 13px; letter-spacing: 2px; text-transform: uppercase; margin-top: 4px; }
    </style>
</head>
<body>
    <div data-role="page" id="login">
        <div data-role="content" style="padding:0;">
            <div class="login-logo" style="padding-top:30px;">
                <img src="images/sonbae.jpg" alt="Logo" />
                <h1>태권도 <span>Poomsae</span></h1>
                <p>Sistema de Evaluación</p>
            </div>
            <div id="login-container">
                <h2>Iniciar Sesión</h2>
                <form method="POST" action="login.php">
                    <input type="text" name="username" id="username" placeholder="Usuario" required />
                    <input name="password" type="password" id="password" placeholder="Contraseña" maxlength="10" required />
                    <input name="Submit" type="submit" value="Ingresar" />
                </form>

                <?php
                if (isset($error_message)) {
                    echo "<div id='error-message'>$error_message</div>";
                }
                ?>
            </div>
            <div style="text-align:center; padding:20px; color:#555; font-size:12px;">
                &copy; CRR 2025
            </div>
        </div>
    </div>
</body>
</html>
