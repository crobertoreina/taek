<?php
// Incluir archivo de conexión a la base de datos
include('conexion.php');

// Verificar si los datos están presentes
if (isset($_POST['id']) && isset($_POST['nombre']) && isset($_POST['apellido']) && isset($_POST['telefono']) && isset($_POST['ciudad']) && isset($_POST['user']) && isset($_POST['pass']) && isset($_POST['level']))
{
    
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];
    $ciudad = $_POST['ciudad'];
	$user = $_POST['user'];
    $pass = $_POST['pass'];
	$level = $_POST['level'];

    // Preparar la consulta SQL para actualizar los datos del participante
    $query = "UPDATE jueces SET nombre = ?, apellido = ?, telefono = ?, ciudad = ?, user = ?, pass = ?, level = ? WHERE id = ?";
    
    if ($stmt = $conexion->prepare($query)) {
        $stmt->bind_param('ssssssii', $nombre, $apellido, $telefono, $ciudad, $user, $pass, $level, $id);
        if ($stmt->execute()) {
            echo "Juez modificado correctamente";
        } else {
            echo "Error al modificar el juez: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error en la consulta: " . $conn->error;
    }
} else {
    echo "Faltan datos para modificar el juez.";
}

// Cerrar la conexión
$conexion->close();
?>
