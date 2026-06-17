<?php
header('Content-Type: application/json');

$conn = require 'conexion.php';

if (isset($_GET['torneoId'])) {
    $torneoId = intval($_GET['torneoId']);

    $query = "SELECT p.id, p.nombre, p.apellido
              FROM participantes p
              LEFT JOIN torneoparticipante tp ON p.id = tp.idParticipante AND tp.idTorneo = ?
              WHERE tp.idParticipante IS NULL";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $torneoId);
    $stmt->execute();
    $result = $stmt->get_result();

    $participantes = [];
    while ($row = $result->fetch_assoc()) {
        $participantes[] = $row;
    }

    echo json_encode($participantes);
    $stmt->close();
}
$conn->close();
?>