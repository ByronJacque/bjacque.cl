<?php
// Configuración de la base de datos
$servername = "localhost"; // La configuración estándar para hosting compartido
$username = "bjacquec_byron"; // Tu usuario de base de datos
$password = "#Q7qrnggn2002"; // Tu contraseña
$dbname = "bjacquec_bjacque.cl"; // Tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>