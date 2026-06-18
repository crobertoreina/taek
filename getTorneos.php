<?php
header('Content-Type: application/json');

$conn = require 'conexion.php';

$query = "SELECT *, CASE WHEN fecha < CURDATE() THEN 0 ELSE activo END as estado_efectivo FROM torneos ORDER BY fecha DESC";
$result = $conn->query($query);

$torneos = [];
while ($row = $result->fetch_assoc()) {
    $torneos[] = $row;
}

echo json_encode($torneos);
$conn->close();
?>
