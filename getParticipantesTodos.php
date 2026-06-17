<?php
header('Content-Type: application/json'); // Asegúrate de que la respuesta sea en formato JSON

include 'conexion.php'; // Incluir archivo de conexión

// Consulta para obtener los participantes
$query = "SELECT * FROM participantes";

$result = $conexion->query($query);

// Verificar si hay un error en la consulta
if (!$result) {
    echo json_encode(['error' => 'Error en la consulta SQL: ' . $conexion->error]);
    exit();
}

// Si no hay errores, procesar los resultados
$participantes = [];
while ($row = $result->fetch_assoc()) {
    $participantes[] = $row;
}

// Enviar los resultados como JSON
echo json_encode($participantes);
?>

