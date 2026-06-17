<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$conn = require 'conexion.php';

$id_juez = isset($_SESSION['juez_id']) ? intval($_SESSION['juez_id']) : 0;

if ($id_juez <= 0) {
    echo json_encode([]);
    exit;
}

$query = "SELECT t.idTorneo, t.nombre, t.fecha, t.ciudad
          FROM torneos t
          INNER JOIN torneojueces tj ON t.idTorneo = tj.idTorneo
          WHERE tj.idJuez = ?
          ORDER BY t.fecha DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_juez);
$stmt->execute();
$result = $stmt->get_result();

$torneos = [];
while ($row = $result->fetch_assoc()) {
    $torneos[] = $row;
}

echo json_encode($torneos);
$stmt->close();
$conn->close();
?>