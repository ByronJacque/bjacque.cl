<?php
require_once 'config.php';

// Esta función se utiliza para crear un usuario específico si no existe
function createUserIfNotExists($conn, $username, $password, $email) {
    // Comprobar si el usuario ya existe
    $sql = "SELECT id FROM usuarios WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "El usuario $username ya existe.<br>";
        $stmt->close();
        return false;
    }
    
    // El usuario no existe, crear uno nuevo
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO usuarios (username, password, email, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $hashed_password, $email);
    
    if ($stmt->execute()) {
        echo "Usuario $username creado exitosamente.<br>";
        $stmt->close();
        return true;
    } else {
        echo "Error al crear el usuario: " . $stmt->error . "<br>";
        $stmt->close();
        return false;
    }
}

// Crear la tabla de usuarios si no existe
$sql_create_usuarios = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_create_usuarios)) {
    echo "Tabla de usuarios verificada/creada correctamente.<br>";
} else {
    echo "Error al crear la tabla de usuarios: " . $conn->error . "<br>";
    exit();
}

// Crear el usuario smashbyron
createUserIfNotExists($conn, "smashbyron", "q7qrnggn", "byron.jacque16@gmail.com");

echo "<br>Proceso completado. <a href='login.php'>Ir a la página de inicio de sesión</a>";

?>