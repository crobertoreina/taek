<?php
header('Content-Type: application/json');

try {
    $conexion = new mysqli('localhost', 'root', '', 'taekdb');
    if ($conexion->connect_error) {
        throw new Exception('Error BD: ' . $conexion->connect_error);
    }
    $conexion->set_charset('utf8');

    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception('ID de torneo no válido.');
    }
    $id = intval($_POST['id']);

    $check = $conexion->query("SHOW COLUMNS FROM torneos LIKE 'activo'");
    if (!$check || $check->num_rows === 0) {
        $conexion->query("ALTER TABLE torneos ADD COLUMN activo tinyint(1) NOT NULL DEFAULT 1 AFTER ciudad");
    }

    $cur = $conexion->query("SELECT activo FROM torneos WHERE idTorneo = $id")->fetch_assoc();
    $activoActual = $cur ? intval($cur['activo']) : 1;
    $esActivacion = $activoActual === 0 || $activoActual === null;

    if ($esActivacion) {
        $countP = $conexion->query("SELECT COUNT(*) as c FROM torneoparticipante WHERE idTorneo = $id")->fetch_assoc()['c'];
        $countJ = $conexion->query("SELECT COUNT(*) as c FROM torneojueces WHERE idTorneo = $id")->fetch_assoc()['c'];
        if ($countP == 0 || $countJ == 0) {
            throw new Exception('El torneo necesita al menos 1 participante y 1 juez para activarse.');
        }
    }

    $stmt = $conexion->prepare("UPDATE torneos SET activo = IF(COALESCE(activo, 1) = 1, 0, 1) WHERE idTorneo = ?");
    if (!$stmt) {
        throw new Exception('Error preparando: ' . $conexion->error);
    }
    $stmt->bind_param('i', $id);

    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar: ' . $stmt->error);
    }

    echo json_encode(['success' => true, 'message' => 'Estado cambiado correctamente.']);
    $stmt->close();
    $conexion->close();
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
