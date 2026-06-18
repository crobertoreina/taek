<?php
session_start();
include('conexion.php');

$mensaje = '';
$tipo = '';

if (!isset($_GET['token']) || empty($_GET['token'])) {
    $mensaje = 'Token de confirmación no válido.';
    $tipo = 'error';
} else {
    $token = $_GET['token'];
    $stmt = $conexion->prepare("SELECT id, nombre, token_expiracion FROM escuelas WHERE token = ? AND estado = 0");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $mensaje = 'El enlace de confirmación no es válido o la cuenta ya fue activada.';
        $tipo = 'error';
    } else {
        $row = $result->fetch_assoc();
        if (strtotime($row['token_expiracion']) < time()) {
            $mensaje = 'El enlace de confirmación ha expirado. Contacta al administrador.';
            $tipo = 'error';
        } else {
            $update = $conexion->prepare("UPDATE escuelas SET estado = 1, token = NULL, token_expiracion = NULL WHERE id = ?");
            $update->bind_param('i', $row['id']);
            if ($update->execute()) {
                $mensaje = 'Cuenta confirmada correctamente. Ya puedes iniciar sesión.';
                $tipo = 'success';
            } else {
                $mensaje = 'Error al confirmar la cuenta. Intenta de nuevo.';
                $tipo = 'error';
            }
            $update->close();
        }
    }
    $stmt->close();
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmar Dojang</title>
    <link rel="stylesheet" href="themes/takwondoTheme.min.css" />
    <link rel="stylesheet" href="themes/jquery.mobile.icons.min.css" />
    <link rel="stylesheet" href="lib/jquery.mobile.structure-1.4.5.min.css" />
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background: linear-gradient(135deg, #f5f5dc 0%, #e8f5e9 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; }
        .card { background:#fff; border-radius:18px; padding:36px 28px; max-width:420px; width:90%; text-align:center; box-shadow:0 8px 30px rgba(0,0,0,0.08); }
        .card .icon { font-size:56px; margin-bottom:12px; }
        .card h2 { color:#333; font-size:20px; margin-bottom:8px; }
        .card p { color:#666; font-size:14px; line-height:1.6; margin-bottom:20px; }
        .card .btn { display:inline-block; padding:12px 28px; background:linear-gradient(135deg,#2e7d32,#4caf50); color:#fff; border-radius:12px; text-decoration:none; font-weight:600; font-size:14px; box-shadow:0 4px 12px rgba(76,175,80,0.2); }
        .card .btn:hover { transform:translateY(-1px); }
        .msg-success { background:#e8f5e9; color:#2e7d32; padding:14px; border-radius:12px; font-size:14px; margin-bottom:20px; }
        .msg-error { background:#ffebee; color:#c62828; padding:14px; border-radius:12px; font-size:14px; margin-bottom:20px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon"><?= $tipo === 'success' ? '✅' : '❌' ?></div>
        <h2><?= $tipo === 'success' ? 'Cuenta Confirmada' : 'Error' ?></h2>
        <div class="msg-<?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
        <a href="login.php" class="btn">Iniciar Sesi&oacute;n</a>
    </div>
</body>
</html>
