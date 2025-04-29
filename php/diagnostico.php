<?php
// Información de conexión - opciones diferentes para probar
$options = [
    [
        'server' => 'localhost',
        'user' => 'bjacquec_byron',
        'pass' => '#Q7qrnggn2002',
        'db' => 'bjacquec_bjacque'
    ],
    [
        'server' => 'localhost:3306',
        'user' => 'bjacquec_byron',
        'pass' => '#Q7qrnggn2002',
        'db' => 'bjacquec_bjacque'
    ],
    [
        'server' => '127.0.0.1',
        'user' => 'bjacquec_byron',
        'pass' => '#Q7qrnggn2002',
        'db' => 'bjacquec_bjacque'
    ]
];

echo "<h1>Diagnóstico de Conexión a Base de Datos</h1>";
echo "<p>Este archivo intenta conectarse a tu base de datos usando diferentes configuraciones para identificar la correcta.</p>";

foreach ($options as $i => $option) {
    echo "<h2>Opción " . ($i+1) . "</h2>";
    echo "<p>Servidor: " . $option['server'] . "<br>";
    echo "Usuario: " . $option['user'] . "<br>";
    echo "Base de datos: " . $option['db'] . "</p>";
    
    echo "<p>Intentando conectar... ";
    
    // Intentar conexión
    $conn = @new mysqli($option['server'], $option['user'], $option['pass'], $option['db']);
    
    // Verificar conexión
    if ($conn->connect_error) {
        echo "<span style='color:red'>ERROR: " . $conn->connect_error . "</span></p>";
    } else {
        echo "<span style='color:green'>CONEXIÓN EXITOSA</span></p>";
        
        // Verificar si existe la tabla usuarios
        $result = $conn->query("SHOW TABLES LIKE 'usuarios'");
        if ($result->num_rows > 0) {
            echo "<p>La tabla 'usuarios' existe.</p>";
            
            // Contar usuarios
            $result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
            $row = $result->fetch_assoc();
            echo "<p>La tabla contiene " . $row['total'] . " usuarios.</p>";
        } else {
            echo "<p style='color:orange'>La tabla 'usuarios' no existe. Creándola...</p>";
            
            // Intentar crear la tabla
            $sql = "CREATE TABLE usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                nombre_completo VARCHAR(100),
                fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            if ($conn->query($sql) === TRUE) {
                echo "<p style='color:green'>Tabla 'usuarios' creada correctamente.</p>";
                
                // Insertar usuario admin
                $hash = '$2y$10$Dq.N5VEypJxFsQNQ9KmQz.Y/BknH9q9yYHCJkK9r6xG3dR3DL0Nfa'; // admin123
                $sql = "INSERT INTO usuarios (username, password, email, nombre_completo) 
                VALUES ('admin', '$hash', 'admin@bjacque.cl', 'Administrador')";
                
                if ($conn->query($sql) === TRUE) {
                    echo "<p style='color:green'>Usuario admin creado correctamente.</p>";
                    echo "<p>Username: admin<br>Contraseña: admin123</p>";
                } else {
                    echo "<p style='color:red'>Error al crear usuario admin: " . $conn->error . "</p>";
                }
            } else {
                echo "<p style='color:red'>Error al crear tabla: " . $conn->error . "</p>";
            }
        }
        
        // Esta es la configuración correcta, actualizar config.php
        echo "<h3>Configuración correcta detectada</h3>";
        echo "<p>Copie este código a su archivo config.php:</p>";
        echo "<pre style='background:#f0f0f0;padding:10px;border:1px solid #ccc'>";
        echo htmlspecialchars('<?php
// Configuración de la base de datos
$servername = "' . $option['server'] . '";
$username = "' . $option['user'] . '";
$password = "' . $option['pass'] . '";
$dbname = "' . $option['db'] . '";

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
?>');
        echo "</pre>";
        
        // No necesitamos probar más opciones
        break;
    }
}

// Información del sistema
echo "<h2>Información del Sistema</h2>";
echo "<p>PHP versión: " . phpversion() . "</p>";
echo "<p>Extensiones cargadas:</p>";
echo "<ul>";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    if ($ext == 'mysqli' || $ext == 'mysql' || $ext == 'pdo' || $ext == 'pdo_mysql') {
        echo "<li><strong style='color:green'>" . $ext . "</strong></li>";
    }
}
echo "</ul>";
?>