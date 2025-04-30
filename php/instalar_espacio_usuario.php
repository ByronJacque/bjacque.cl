<?php
// Script para crear las tablas necesarias para el espacio personal de usuario
require_once 'config.php';

// Leer el archivo SQL
$sql_file = file_get_contents('crear_tablas_usuario.sql');

// Dividir el archivo en consultas individuales
$queries = explode(';', $sql_file);

// Ejecutar cada consulta
$success = true;
foreach ($queries as $query) {
    $query = trim($query);
    if (empty($query)) continue;
    
    if (!$conn->query($query)) {
        echo "Error ejecutando la consulta: " . $conn->error . "<br>";
        $success = false;
    }
}

// Crear directorio para los archivos de usuario si no existe
$user_directory = "../usuarios";
if (!file_exists($user_directory)) {
    if (!mkdir($user_directory, 0777, true)) {
        echo "Error al crear el directorio para archivos de usuario.<br>";
        $success = false;
    }
}

if ($success) {
    echo "Configuración completada con éxito. Las tablas para el espacio personal de usuario han sido creadas.<br>";
    echo "El directorio para guardar los archivos de usuario ha sido creado.<br>";
    echo "Ahora los usuarios podrán tener su propio espacio personal al registrarse.";
} else {
    echo "Ocurrieron errores durante la configuración. Por favor revise los mensajes anteriores.";
}
?>