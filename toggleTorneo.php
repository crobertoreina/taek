<?php
header('Content-Type: application/json');

try {
    $conexion = new mysqli('localhost', 'root', '', 'taekdb');
    if ($conexion->connect_error) {
        throw new Exception('Error de conexión BD: ' . $conexion->connect_error);
    }
    $conexion->set_charset('utf8');

    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception('ID de torneo no válido.');
    }

    $id = intval($_POST['id']);

    $check = $conexion->query("SHOW COLUMNS FROM torneos LIKE 'activo'");
    if (!$check || $check->num_rows === 0) {
        $conexion->query("ALTER TABLE torneos ADD COLUMN activo tinyint(1) NOT NULL DEFAULT 1 AFTER ciudad");
    }

    $stmt = $conexion->prepare("UPDATE torneos SET activo = IF(COALESCE(activo, 1) = 1, 0, 1) WHERE idTorneo = ?");
    if (!$stmt) {
        throw new Exception('Error preparando consulta: ' . $conexion->error);
    }
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Estado cambiado correctamente.']);
    } else {
        throw new Exception('Error al ejecutar: ' . $stmt->error);
    }
    $stmt->close();
    $conexion->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
