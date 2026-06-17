<?php
header('Content-Type: application/json');

$conn = require 'conexion.php';

$torneoId = isset($_GET['torneo_id']) ? intval($_GET['torneo_id']) : 0;

if ($torneoId > 0) {
    $query = "SELECT p.id, p.nombre, p.apellido, p.telefono, p.ciudad
              FROM participantes p
              INNER JOIN torneoparticipante tp ON p.id = tp.idParticipante
              WHERE tp.idTorneo = ?";

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
} else {
    echo json_encode(["error" => "ID de torneo no válido"]);
}
$conn->close();
?>
