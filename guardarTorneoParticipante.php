<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $torneoId = $_POST['torneoSelect'];
    $participantes = $_POST['participantes']; // Array de los participantes seleccionados

    // Verificar que se haya seleccionado un torneo y participantes
    if ($torneoId && !empty($participantes)) {
        foreach ($participantes as $participanteId) {
            $query = "INSERT INTO torneoParticipante (torneo_id, participante_id) VALUES (?, ?)";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param('ii', $torneoId, $participanteId);
            $stmt->execute();
        }
        echo "Los participantes han sido guardados correctamente.";
    } else {
        echo "Debe seleccionar un torneo y al menos un participante.";
    }
}
?>
