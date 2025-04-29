<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email'];
    $nombre_completo = $_POST['nombre_completo'];
    
    // Validar que las contraseñas coincidan
    if ($password != $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } else {
        // Comprobar si el usuario ya existe
        $sql = "SELECT id FROM usuarios WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "El nombre de usuario o email ya está en uso";
        } else {
            // Hashear la contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar nuevo usuario
            $sql = "INSERT INTO usuarios (username, password, email, nombre_completo) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $username, $hashed_password, $email, $nombre_completo);
            
            if ($stmt->execute()) {
                $success = "Registro exitoso. Ahora puedes iniciar sesión.";
            } else {
                $error = "Error al registrar: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | Byron Jacque</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/index.css">
    <style>
        .register-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 20px;
            background-color: rgba(0,0,0,0.7);
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.2);
        }
        .register-form {
            display: flex;
            flex-direction: column;
        }
        .register-form input {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #2a2a2a;
            background-color: #121212;
            color: #e0e0e0;
            border-radius: 4px;
        }
        .register-form button {
            padding: 12px;
            background-color: #00c853;
            color: #000;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .register-form button:hover {
            background-color: #00e676;
        }
        .error-message {
            color: #ff3d00;
            margin-bottom: 15px;
        }
        .success-message {
            color: #00e676;
            margin-bottom: 15px;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
            color: #e0e0e0;
        }
        .login-link a {
            color: #00e676;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- Digital Grid -->
    <div class="digital-grid"></div>
    
    <!-- Matrix Rain Canvas -->
    <canvas id="matrix-rain"></canvas>
    
    <!-- Video de fondo -->
    <div class="video-background">
        <video autoplay muted loop id="background-video">
            <source src="../assets/videos/fondomatrix.mp4" type="video/mp4">
        </video>
    </div>
    
    <!-- Header Fijo -->
    <header>
        <div class="logo">
            <a href="../index.html" class="logo-text">Byron Jacque</a>
        </div>
        <nav>
            <ul>
                <li><a href="../index.html">Inicio</a></li>
                <li><a href="../Paginas/proyectos.html">Proyectos</a></li>
                <li><a href="../Paginas/blogs.html">Blogs</a></li>
                <li><a href="../Paginas/hobbys.html">Hobbys</a></li>
                <li><a href="../Paginas/datos.html">Mis Datos</a></li>
                <li><a href="../Paginas/certificados.html">Certificados</a></li>
                <li><a href="../Paginas/musica.html">Música</a></li>
            </ul>
        </nav>
    </header>

    <div class="register-container">
        <h2 style="text-align: center; color: #00e676; margin-bottom: 20px;">Registro de Usuario</h2>
        
        <?php if(!empty($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if(!empty($success)): ?>
            <div class="success-message">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form class="register-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="text" name="username" placeholder="Nombre de usuario" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="text" name="nombre_completo" placeholder="Nombre completo" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="password" name="confirm_password" placeholder="Confirmar contraseña" required>
            <button type="submit">Registrarse</button>
        </form>
        
        <div class="login-link">
            ¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h3>Byron Jacque</h3>
                    <p>Desarrollador Web & Ingeniero Informático</p>
                </div>
                <div class="social-links">
                    <a href="https://github.com/ByronJacque"><i class="fab fa-github"></i></a>
                    <a href="https://www.linkedin.com/in/byron-jacque-rivero-a1b670248/"><i class="fab fa-linkedin"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 Byron Jacque. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="../Js/index.js"></script>
</body>
</html>