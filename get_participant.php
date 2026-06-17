<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$conn = require 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_SESSION['evaluando_participante_id']) ? intval($_SESSION['evaluando_participante_id']) : 0);

if ($id > 0) {
    $query = "SELECT id, nombre, apellido FROM participantes WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Participante no encontrado']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'ID no válido']);
}

$conn->close();
?>