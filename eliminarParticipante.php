<?php
// Incluir archivo de conexión a la base de datos
include('conexion.php');

// Verificar si el ID está presente
if (isset($_POST['id'])) {
    
    $id = $_POST['id'];

    // Preparar la consulta SQL para eliminar el participante
    $query = "DELETE FROM participantes WHERE id = ?";
    
    if ($stmt = $conexion->prepare($query)) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            echo "Participante eliminado correctamente";
        } else {
            echo "Error al eliminar el participante: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error en la consulta: " . $conn->error;
    }
} else {
    echo "No se especificó el ID del participante.";
}

// Cerrar la conexión
$conexion->close();
?>
