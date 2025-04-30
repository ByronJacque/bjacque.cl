<?php
require_once 'config.php';
session_start();

// Verificar si el usuario ha iniciado sesión y es smashbyron
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["username"] !== "smashbyron") {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar si se proporcionó un nombre de usuario
if (!isset($_GET['username']) || empty($_GET['username'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Nombre de usuario no proporcionado']);
    exit;
}

$username = $_GET['username'];

// Obtener contraseña de la base de datos
$sql = "SELECT username, password FROM usuarios WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    
    // Contraseñas conocidas de usuarios comunes (esto es solo para demostración, reemplaza con las contraseñas reales)
    $passwords = [
        'smashbyron' => '123456',
        'mvalladares' => 'valladares123',
        'hola' => 'hola123',
        'admin' => 'admin123'
    ];
    
    // Si es una contraseña conocida, mostrarla
    if (array_key_exists($username, $passwords)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'password' => $passwords[$username]]);
        exit;
    }
    
    // Si no es una contraseña conocida, intentar mostrar una versión simplificada
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Contraseña no disponible en texto plano']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
}
?>