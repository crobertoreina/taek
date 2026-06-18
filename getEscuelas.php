<?php
header('Content-Type: application/json');
include('conexion.php');
$result = $conexion->query("SELECT id, nombre, siglas FROM escuelas WHERE estado = 1 ORDER BY nombre");
$escuelas = [];
while ($row = $result->fetch_assoc()) {
    $escuelas[] = $row;
}
echo json_encode($escuelas);
