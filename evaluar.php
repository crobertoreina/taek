<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['juez_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$conn = require 'conexion.php';

$id_participante = isset($_POST['id_participante']) ? intval($_POST['id_participante']) : 0;
$id_torneo = isset($_POST['id_torneo']) ? intval($_POST['id_torneo']) : 0;
$puntos = isset($_POST['puntos']) ? floatval($_POST['puntos']) : 0;
$categoria = isset($_POST['categoria']) ? $_POST['categoria'] : 'poomsae';
$id_juez = intval($_SESSION['juez_id']);

if ($id_participante <= 0 || $id_torneo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$query = "INSERT INTO evaluaciones (id_torneo, id_participante, id_juez, categoria, puntos) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiisd", $id_torneo, $id_participante, $id_juez, $categoria, $puntos);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Evaluación guardada']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>