<?php
// Incluir el archivo de conexión
include('conexion.php');

// Verificar si se recibieron los datos de la solicitud
if (isset($_POST['torneoId']) && isset($_POST['participanteId']))
{
    $torneoId = $_POST['torneoId'];
    $participanteId = $_POST['participanteId'];

    // Consulta para insertar el participante en la tabla torneoParticipante
    $query = "INSERT INTO torneoparticipante (idTorneo, idParticipante) VALUES (?, ?)";

    // Preparar la consulta
    $stmt = $conexion->prepare($query);

    // Verificar si la preparación de la consulta fue exitosa
    if ($stmt === false) {
        die("Error al preparar la consulta: " . $conexion->error);
    }

    // Vincular los parámetros
    $stmt->bind_param("ii", $torneoId, $participanteId); // "ii" indica que ambos parámetros son enteros (INT)

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo "Participante agregado al torneo con éxito";
    } else {
        echo "Error al agregar el participante: " . $stmt->error;
    }

    // Cerrar la declaración
    $stmt->close();
} else {
    echo "Datos incompletos";
}

// Cerrar la conexión
$conexion->close();
?>
