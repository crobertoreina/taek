<?php
// Incluir archivo de conexión a la base de datos
include('conexion.php');

// Verificar si el ID está presente
if (isset($_POST['idTorneo']))
{
    
    $idTorneo = $_POST['idTorneo'];

    // Verificar si existen participantes en la tabla torneoParticipante para este torneo
    $queryVerificarParticipantes = "SELECT COUNT(*) AS totalParticipantes FROM torneoparticipante WHERE idTorneo = ?";
    
    if ($stmtVerificar = $conexion->prepare($queryVerificarParticipantes)) {
        $stmtVerificar->bind_param('i', $idTorneo);
        $stmtVerificar->execute();
        $resultado = $stmtVerificar->get_result();
        $row = $resultado->fetch_assoc();

        // Si existen participantes asociados, no permitir la eliminación
        if ($row['totalParticipantes'] > 0) {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar el torneo porque tiene participantes asociados.']);
        } else {
            // Si no hay participantes, proceder con la eliminación del torneo
            $queryEliminarTorneo = "DELETE FROM torneos WHERE idTorneo = ?";
            if ($stmtEliminar = $conexion->prepare($queryEliminarTorneo)) {
                $stmtEliminar->bind_param('i', $idTorneo);
                if ($stmtEliminar->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Torneo eliminado correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el torneo: ' . $stmtEliminar->error]);
                }
                $stmtEliminar->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Error en la consulta de eliminacion: ' . $conexion->error]);
            }
        }
        $stmtVerificar->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la consulta de verificación de participantes: ' . $conexion->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No se especifico el ID del torneo.']);
}

// Cerrar la conexión
$conexion->close();
?>
