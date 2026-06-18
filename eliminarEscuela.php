<?php
session_start();
include('conexion.php');

// Verificar que es administrador
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

if (!isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID requerido']);
    exit();
}

$id = intval($_POST['id']);

// Verificar que la escuela existe
$check = $conexion->prepare("SELECT id FROM escuelas WHERE id = ?");
$check->bind_param('i', $id);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    $check->close();
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Escuela no encontrada']);
    exit();
}
$check->close();

// Eliminar escuela
$stmt = $conexion->prepare("DELETE FROM escuelas WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Escuela eliminada correctamente'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al eliminar: ' . $stmt->error]);
}

$stmt->close();
$conexion->close();
?>
