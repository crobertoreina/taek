<?php
header('Content-Type: application/json');
try {
    if (!isset($_POST['id'])) throw new Exception('ID requerido');
    $id = intval($_POST['id']);
    $conn = new mysqli('localhost', 'root', '', 'taekdb');
    if ($conn->connect_error) throw new Exception('Error BD: ' . $conn->connect_error);
    $conn->set_charset('utf8');
    $r = $conn->query("SELECT estado FROM escuelas WHERE id = $id");
    if (!$r || $r->num_rows === 0) throw new Exception('Dojang no encontrado');
    $row = $r->fetch_assoc();
    $nuevo = $row['estado'] ? 0 : 1;
    $conn->query("UPDATE escuelas SET estado = $nuevo WHERE id = $id");
    echo json_encode(['success' => true, 'estado' => $nuevo, 'message' => $nuevo ? 'Dojang activado' : 'Dojang desactivado']);
    $conn->close();
} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
