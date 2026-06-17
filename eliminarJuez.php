<?php
// Incluir archivo de conexión a la base de datos
include('conexion.php');

// Verificar si el ID está presente
if (isset($_POST['id'])) {
    
    $id = $_POST['id'];

    $query = "DELETE FROM jueces WHERE id = ?";
    
    if ($stmt = $conexion->prepare($query)) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            echo "Juez eliminado correctamente";
        } else {
            echo "Error al eliminar el juez: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error en la consulta: " . $conn->error;
    }
} else {
    echo "No se especificó el ID del juez.";
}

// Cerrar la conexión
$conexion->close();
?>
