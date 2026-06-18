<?php
header('Content-Type: application/json');

try {
    $conn = new mysqli('localhost', 'root', '', 'taekdb');
    if ($conn->connect_error) {
        throw new Exception('Error BD: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8');

    $check = $conn->query("SHOW COLUMNS FROM torneos LIKE 'activo'");
    if (!$check || $check->num_rows === 0) {
        $conn->query("ALTER TABLE torneos ADD COLUMN activo tinyint(1) NOT NULL DEFAULT 1 AFTER ciudad");
    }

    $query = "SELECT *, CASE WHEN fecha < CURDATE() THEN 0 ELSE COALESCE(activo, 1) END as estado_efectivo, (SELECT COUNT(*) FROM torneoparticipante WHERE idTorneo = t.idTorneo) as total_participantes, (SELECT COUNT(*) FROM torneojueces WHERE idTorneo = t.idTorneo) as total_jueces FROM torneos t ORDER BY fecha DESC";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception('Error en consulta: ' . $conn->error);
    }

    $torneos = [];
    while ($row = $result->fetch_assoc()) {
        $torneos[] = $row;
    }

    echo json_encode($torneos);
    $conn->close();
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
