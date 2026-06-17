<?php
// Incluir el archivo de conexión a la base de datos
include('conexion.php');

// Verificar que se haya enviado el torneoId a través de GET
if (isset($_GET['torneoId'])) {
    $torneoId = intval($_GET['torneoId']); // Asegurarse de que el ID sea un número entero

    // Preparar la consulta para obtener los participantes seleccionados para este torneo
    $sql = "
        SELECT p.id AS participante_id, p.nombre, p.apellido
        FROM torneoparticipante tp
        JOIN participantes p ON tp.idParticipante = p.id
        WHERE tp.idTorneo = ?
    ";

    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("i", $torneoId); // Enlazar el parámetro torneoId
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $participantes = array();

            while ($row = $result->fetch_assoc()) {
                // Almacenar los participantes en un arreglo
                $participantes[] = array(
                    'id' => $row['participante_id'],
                    'nombre' => $row['nombre'],
                    'apellido' => $row['apellido']
                );
            }

            // Retornar los datos como JSON
            echo json_encode($participantes);
        } else {
            // Si hubo un error en la ejecución de la consulta
            echo json_encode(['success' => false, 'message' => 'Error al obtener los participantes.']);
        }
        
        $stmt->close();
    } else {
        // Si no se pudo preparar la consulta
        echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta.']);
    }

} else {
    // Si no se recibe el torneoId
    echo json_encode(['success' => false, 'message' => 'Faltó el torneoId.']);
}

// Cerrar la conexión
$conexion->close();
?>
