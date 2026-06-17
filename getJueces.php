<?php
header('Content-Type: application/json');

$conn = require 'conexion.php';

$query = "SELECT id, nombre, apellido, telefono, ciudad, user, pass, level FROM jueces";
$result = $conn->query($query);

$jueces = [];
while ($row = $result->fetch_assoc()) {
    $jueces[] = $row;
}

echo json_encode($jueces);
$conn->close();
?>