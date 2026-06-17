<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$conn = require 'conexion.php';

$id_torneo = isset($_GET['id_torneo']) ? intval($_GET['id_torneo']) : 0;
$id_participante = isset($_GET['id_participante']) ? intval($_GET['id_participante']) : 0;

if ($id_torneo <= 0 || $id_participante <= 0) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

// Count judges assigned to this tournament
$queryJ = "SELECT COUNT(*) as total FROM torneojueces WHERE idTorneo = ?";
$stmtJ = $conn->prepare($queryJ);
$stmtJ->bind_param("i", $id_torneo);
$stmtJ->execute();
$totalJueces = $stmtJ->get_result()->fetch_assoc()['total'];

// Count evaluations for this participant in this tournament
$queryE = "SELECT COUNT(*) as total FROM evaluaciones WHERE id_torneo = ? AND id_participante = ?";
$stmtE = $conn->prepare($queryE);
$stmtE->bind_param("ii", $id_torneo, $id_participante);
$stmtE->execute();
$totalEvaluaciones = $stmtE->get_result()->fetch_assoc()['total'];

// Check if current judge has already scored
$id_juez = isset($_SESSION['juez_id']) ? intval($_SESSION['juez_id']) : 0;
$yaVoto = false;
if ($id_juez > 0) {
    $queryV = "SELECT COUNT(*) as total FROM evaluaciones WHERE id_torneo = ? AND id_participante = ? AND id_juez = ?";
    $stmtV = $conn->prepare($queryV);
    $stmtV->bind_param("iii", $id_torneo, $id_participante, $id_juez);
    $stmtV->execute();
    $yaVoto = $stmtV->get_result()->fetch_assoc()['total'] > 0;
    $stmtV->close();
}

echo json_encode([
    'total_jueces' => intval($totalJueces),
    'total_evaluaciones' => intval($totalEvaluaciones),
    'todos_votaron' => intval($totalJueces) > 0 && intval($totalEvaluaciones) >= intval($totalJueces),
    'ya_voto' => $yaVoto
]);

$stmtJ->close();
$stmtE->close();
$conn->close();
?>