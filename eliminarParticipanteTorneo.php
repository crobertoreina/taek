<?php
// Incluir el archivo de conexión
include('conexion.php');

// Verificar si se recibieron los datos de la solicitud
if (isset($_POST['torneoId']) && isset($_POST['participanteId']))
{
    $torneoId = $_POST['torneoId'];
    $participanteId = $_POST['participanteId'];

    // Consulta para eliminar el participante de la tabla torneoParticipante
    $query = "DELETE FROM torneoparticipante WHERE idTorneo = ? AND idParticipante = ?";

    // Preparar la consulta
    $stmt = $conexion->prepare($query);

    // Verificar si la preparación de la consulta fue exitosa
    if ($stmt === false) {
        die("Error al preparar la consulta: " . $conexion->error);
    }

    // Vincular los parámetros (ii significa que ambos parámetros son enteros)
    $stmt->bind_param("ii", $torneoId, $participanteId);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo "Participante eliminado con éxito";
    } else {
        echo "Error al borrar el participante: " . $stmt->error;
    }

    // Cerrar la declaración
    $stmt->close();
} else {
    echo "Datos incompletos";
}

// Cerrar la conexión
$conexion->close();
?>
