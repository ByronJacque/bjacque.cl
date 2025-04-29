<?php
// Este es un script de un solo uso para crear el usuario mvalladares
require_once 'config.php';

// Datos del usuario
$username = 'mvalladares';
$password = 'noviapexoxaxsiempre';
$email = 'mvalladares@ejemplo.com'; // Cambia esto al email real si lo deseas
$nombre_completo = 'Macarena Valladares';

// Hash de la contraseña
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verificar si el usuario ya existe
$check_sql = "SELECT id FROM usuarios WHERE username = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $username);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo "<p>El usuario mvalladares ya existe en la base de datos.</p>";
    
    // Actualizar la contraseña si se desea
    $update_sql = "UPDATE usuarios SET password = ? WHERE username = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ss", $hashed_password, $username);
    
    if ($update_stmt->execute()) {
        echo "<p>La contraseña del usuario mvalladares ha sido actualizada.</p>";
    } else {
        echo "<p>Error al actualizar la contraseña: " . $conn->error . "</p>";
    }
    
    $update_stmt->close();
} else {
    // Insertar nuevo usuario
    $insert_sql = "INSERT INTO usuarios (username, password, email, nombre_completo) VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ssss", $username, $hashed_password, $email, $nombre_completo);
    
    if ($insert_stmt->execute()) {
        echo "<p>Usuario mvalladares creado exitosamente.</p>";
        echo "<p>Nombre de usuario: mvalladares</p>";
        echo "<p>Contraseña: noviapexoxaxsiempre</p>";
    } else {
        echo "<p>Error al crear usuario: " . $conn->error . "</p>";
    }
    
    $insert_stmt->close();
}

$check_stmt->close();
$conn->close();

// Instrucciones para el usuario
echo "<h2>Instrucciones:</h2>";
echo "<p>1. Una vez que hayas ejecutado este script y veas el mensaje de éxito, <strong>borra este archivo del servidor</strong> por motivos de seguridad.</p>";
echo "<p>2. Ahora puedes usar las credenciales indicadas para acceder al área exclusiva:</p>";
echo "<ul>";
echo "<li>Usuario: mvalladares</li>";
echo "<li>Contraseña: noviapexoxaxsiempre</li>";
echo "</ul>";
echo "<p>3. <a href='login.php'>Ir a la página de inicio de sesión</a></p>";
?>