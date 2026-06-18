<?php
// Incluir archivo de conexión a la base de datos
include('conexion.php');

// Verificar si los datos están presentes
if (isset($_POST['id']) && isset($_POST['nombre']) && isset($_POST['apellido']) && isset($_POST['telefono']) && isset($_POST['ciudad'])) {
    
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];
    $ciudad = $_POST['ciudad'];
    $categoria = $_POST['categoria'] ?? null;
    $cinturon = $_POST['cinturon'] ?? null;
    $edad = isset($_POST['edad']) && is_numeric($_POST['edad']) ? intval($_POST['edad']) : null;
    $id_escuela = isset($_POST['id_escuela']) && is_numeric($_POST['id_escuela']) ? intval($_POST['id_escuela']) : null;

    // Preparar la consulta SQL para actualizar los datos del participante
    $query = "UPDATE participantes SET nombre = ?, apellido = ?, telefono = ?, ciudad = ?, edad = ?, categoria = ?, cinturon = ?, id_escuela = ? WHERE id = ?";
    
    if ($stmt = $conexion->prepare($query)) {
        $stmt->bind_param('ssssissii', $nombre, $apellido, $telefono, $ciudad, $edad, $categoria, $cinturon, $id_escuela, $id);
        if ($stmt->execute()) {
            echo "Participante modificado correctamente";
        } else {
            echo "Error al modificar el participante: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error en la consulta: " . $conn->error;
    }
} else {
    echo "Faltan datos para modificar el participante.";
}

// Cerrar la conexión
$conexion->close();
?>
