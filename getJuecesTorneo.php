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
    echo json_encode([]);
    exit;
}

$query = "SELECT j.id, j.nombre, j.apellido
          FROM jueces j
          INNER JOIN torneojueces tj ON j.id = tj.idJuez
          WHERE tj.idTorneo = ?
          ORDER BY j.id";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_torneo);
$stmt->execute();
$result = $stmt->get_result();

$jueces = [];
while ($row = $result->fetch_assoc()) {
    $jueces[] = $row;
}

echo json_encode($jueces);
$stmt->close();
$conn->close();
?>