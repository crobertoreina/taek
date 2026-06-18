<?php
header('Content-Type: application/json');
try {
    $conn = new mysqli('localhost', 'root', '', 'taekdb');
    if ($conn->connect_error) throw new Exception('Error BD: ' . $conn->connect_error);
    $conn->set_charset('utf8');
    $result = $conn->query("SELECT e.*, (SELECT COUNT(*) FROM participantes WHERE id_escuela = e.id) as total_participantes FROM escuelas e ORDER BY e.estado DESC, e.nombre");
    $escuelas = [];
    while ($row = $result->fetch_assoc()) {
        $escuelas[] = $row;
    }
    echo json_encode($escuelas);
    $conn->close();
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
