<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$conn = require 'conexion.php';

$id_torneo = isset($_POST['id_torneo']) ? intval($_POST['id_torneo']) : 0;

if ($id_torneo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Torneo no válido']);
    exit;
}

// Mark current evaluation as completed
$qUpdate = "UPDATE control_evaluacion SET estado = 'completado' WHERE id_torneo = ? AND estado = 'evaluando'";
$sUpdate = $conn->prepare($qUpdate);
$sUpdate->bind_param("i", $id_torneo);
$sUpdate->execute();
$sUpdate->close();

// Get current order
$query = "SELECT orden FROM control_evaluacion WHERE id_torneo = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_torneo);
$stmt->execute();
$result = $stmt->get_result();
$currentOrder = 0;
if ($row = $result->fetch_assoc()) {
    $currentOrder = intval($row['orden']);
}
$stmt->close();

// Get all participants ordered
$qParts = "SELECT p.id FROM participantes p
           INNER JOIN torneoparticipante tp ON p.id = tp.idParticipante
           WHERE tp.idTorneo = ?
           ORDER BY p.id";
$sParts = $conn->prepare($qParts);
$sParts->bind_param("i", $id_torneo);
$sParts->execute();
$rParts = $sParts->get_result();

$participants = [];
while ($row = $rParts->fetch_assoc()) {
    $participants[] = $row['id'];
}
$sParts->close();

// Find next participant (after current order index)
$nextIndex = $currentOrder + 1;
$nextParticipantId = null;

if (isset($participants[$nextIndex])) {
    $nextParticipantId = $participants[$nextIndex];
}

if ($nextParticipantId) {
    $query3 = "INSERT INTO control_evaluacion (id_torneo, id_participante_actual, orden, estado) VALUES (?, ?, ?, 'evaluando')";
    $stmt3 = $conn->prepare($query3);
    $newOrder = $nextIndex;
    $stmt3->bind_param("iii", $id_torneo, $nextParticipantId, $newOrder);

    if ($stmt3->execute()) {
        echo json_encode(['success' => true, 'message' => 'Siguiente participante', 'id_participante' => $nextParticipantId]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt3->error]);
    }
    $stmt3->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No hay más participantes']);
}

$conn->close();
?>