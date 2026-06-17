<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$conn = require 'conexion.php';

$id_torneo = isset($_GET['id_torneo']) ? intval($_GET['id_torneo']) : 0;

if ($id_torneo <= 0) {
    echo json_encode(['error' => 'Torneo no válido']);
    exit;
}

// Get current participant from control_evaluacion
$query = "SELECT ce.id_participante_actual, ce.orden, ce.estado,
                 p.nombre, p.apellido
          FROM control_evaluacion ce
          LEFT JOIN participantes p ON ce.id_participante_actual = p.id
          WHERE ce.id_torneo = ? AND ce.estado = 'evaluando'
          ORDER BY ce.id DESC LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_torneo);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'id_participante_actual' => $row['id_participante_actual'],
        'nombre' => $row['nombre'],
        'apellido' => $row['apellido'],
        'orden' => $row['orden'],
        'estado' => $row['estado']
    ]);
} else {
    // Check if there are completed evaluations (all participants done)
    $qDone = "SELECT COUNT(*) as total FROM control_evaluacion WHERE id_torneo = ? AND estado = 'completado'";
    $sDone = $conn->prepare($qDone);
    $sDone->bind_param("i", $id_torneo);
    $sDone->execute();
    $sDone->bind_result($completedCount);
    $sDone->fetch();
    $sDone->close();

    // Get total participants
    $qTotal = "SELECT COUNT(*) as total FROM torneoparticipante WHERE idTorneo = ?";
    $sTotal = $conn->prepare($qTotal);
    $sTotal->bind_param("i", $id_torneo);
    $sTotal->execute();
    $sTotal->bind_result($totalParticipants);
    $sTotal->fetch();
    $sTotal->close();

    if ($completedCount >= $totalParticipants && $totalParticipants > 0) {
        echo json_encode(['error' => 'Todos los participantes han sido evaluados']);
        exit;
    }

    // Get first participant and create evaluation record
    $query2 = "SELECT p.id, p.nombre, p.apellido
               FROM participantes p
               INNER JOIN torneoparticipante tp ON p.id = tp.idParticipante
               WHERE tp.idTorneo = ?
               ORDER BY p.id LIMIT 1";
    $stmt2 = $conn->prepare($query2);
    $stmt2->bind_param("i", $id_torneo);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    if ($row2 = $result2->fetch_assoc()) {
        // Create the evaluation record
        $insertQuery = "INSERT INTO control_evaluacion (id_torneo, id_participante_actual, orden, estado) VALUES (?, ?, 0, 'evaluando')";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ii", $id_torneo, $row2['id']);
        $insertStmt->execute();
        $insertStmt->close();

        echo json_encode([
            'id_participante_actual' => $row2['id'],
            'nombre' => $row2['nombre'],
            'apellido' => $row2['apellido'],
            'orden' => 0,
            'estado' => 'evaluando'
        ]);
    } else {
        echo json_encode(['error' => 'No hay participantes en este torneo']);
    }
    $stmt2->close();
}

$stmt->close();
$conn->close();
?>