<?php
session_start();
include('conexion.php');

// Verificar que es administrador
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] !== 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit();
}

// Validar datos requeridos
if (!isset($_POST['id']) || !isset($_POST['nombre'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID y nombre son requeridos']);
    exit();
}

$id = intval($_POST['id']);
$nombre = trim($_POST['nombre']);
$correo = trim($_POST['correo'] ?? '');
$siglas = trim($_POST['siglas'] ?? '');
$fecha_fundacion = trim($_POST['fecha_fundacion'] ?? '');
$pais = trim($_POST['pais'] ?? '');
$departamento = trim($_POST['departamento'] ?? '');
$ciudad = trim($_POST['ciudad'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$instructor_nombre = trim($_POST['instructor_nombre'] ?? '');
$instructor_grado = trim($_POST['instructor_grado'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$pass = trim($_POST['pass'] ?? '');
$estado = isset($_POST['estado']) ? intval($_POST['estado']) : 1;

// Validar correo si se proporciona
if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Correo inválido']);
    exit();
}

// Verificar que la escuela existe
$check = $conexion->prepare("SELECT id, correo FROM escuelas WHERE id = ?");
$check->bind_param('i', $id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    $check->close();
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Escuela no encontrada']);
    exit();
}

$row = $result->fetch_assoc();
$check->close();

// Si cambió el correo, verificar que el nuevo no exista
if ($correo !== $row['correo'] && !empty($correo)) {
    $check2 = $conexion->prepare("SELECT id FROM escuelas WHERE correo = ? AND id != ?");
    $check2->bind_param('si', $correo, $id);
    $check2->execute();
    if ($check2->get_result()->num_rows > 0) {
        $check2->close();
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'El correo ya está registrado']);
        exit();
    }
    $check2->close();
}

// Actualizar escuela
$query = "UPDATE escuelas SET nombre = ?, siglas = ?, fecha_fundacion = ?, pais = ?, departamento = ?, 
          ciudad = ?, direccion = ?, instructor_nombre = ?, instructor_grado = ?, telefono = ?, 
          correo = ?, user = ?";

$params = [$nombre, $siglas, $fecha_fundacion, $pais, $departamento, $ciudad, $direccion, 
          $instructor_nombre, $instructor_grado, $telefono, $correo, $correo];
$types = 'ssssssssssss';

// Agregar contraseña si se proporciona
if (!empty($pass)) {
    $query .= ", pass = ?";
    $params[] = $pass;
    $types .= 's';
}

// Agregar estado
$query .= ", estado = ? WHERE id = ?";
$params[] = $estado;
$params[] = $id;
$types .= 'ii';

$stmt = $conexion->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error en la consulta: ' . $conexion->error]);
    exit();
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Escuela modificada correctamente'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al modificar: ' . $stmt->error]);
}

$stmt->close();
$conexion->close();
?>
