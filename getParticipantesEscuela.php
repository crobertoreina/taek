<?php
header('Content-Type: application/json');
include('conexion.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode([]);
    exit();
}

$query = "SELECT * FROM participantes WHERE id_escuela = ? ORDER BY nombre";
$stmt = $conexion->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

$participantes = [];
while ($row = $result->fetch_assoc()) {
    $participantes[] = $row;
}
echo json_encode($participantes);
